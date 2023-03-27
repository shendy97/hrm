<?php

namespace App\Console\Commands;

use App\Events\TaskReminderEvent;
use App\Models\Company;
use App\Models\Task;
use App\Models\TaskboardColumn;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendTaskReminder extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-task-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send task reminders';

    /**
     *
     */
    public function handle()
    {
        $companies = Company::select('id', 'timezone', 'before_days', 'after_days', 'on_deadline')->get();

        foreach ($companies as $company) {

            $now = Carbon::now($company->timezone);

            if ($company->before_days > 0) {
                $beforeDeadline = $now->clone()->subDays($company->before_days)->format('Y-m-d');
                $this->tasks($beforeDeadline, $company);
            }

            if ($company->after_days > 0) {
                $afterDeadline = $now->clone()->addDays($company->after_days)->format('Y-m-d');
                $this->tasks($afterDeadline, $company);
            }

            if ($company->on_deadline) {
                $onDeadline = $now->clone()->format('Y-m-d');
                $this->tasks($onDeadline, $company);
            }
        }

    }

    private function tasks($dueDate, $company)
    {
        $completedTaskColumn = TaskboardColumn::where('company_id', $company->id)->where('slug', 'completed')->first();
        $tasks = Task::select('id')
            ->where('due_date', $dueDate)
            ->where('company_id', $company->id)
            ->where('board_column_id', '<>', $completedTaskColumn->id)
            ->get();

        foreach ($tasks as $task) {
            event(new TaskReminderEvent($task));
        }
    }

}
