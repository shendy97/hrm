<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\ExpenseRecurring;
use Illuminate\Console\Command;

class AutoCreateRecurringExpenses extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-expenses-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create recurring expenses ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $recurringExpenses = ExpenseRecurring::with('recurrings')->where('status', 'active')->get();

        $recurringExpenses->each(function ($recurring) {
            if ($recurring->unlimited_recurring == 1 || ($recurring->unlimited_recurring == 0 && $recurring->recurrings->count() < $recurring->billing_cycle)) {

                if ($this->isDaily($recurring) ||
                    $this->isWeekly($recurring) ||
                    $this->isBiWeekly($recurring) ||
                    $this->isMonthly($recurring) ||
                    $this->isQuarterly($recurring) ||
                    $this->isHalfYearly($recurring) ||
                    $this->isAnnually($recurring)
                ) {
                    $this->makeExpense($recurring);
                }
            }
        });
    }

    private function makeExpense($recurring)
    {
        $expense = new Expense();
        $expense->company_id = $recurring->company_id;
        $expense->expenses_recurring_id = $recurring->id;
        $expense->category_id = $recurring->category_id;
        $expense->project_id = $recurring->project_id;
        $expense->currency_id = $recurring->currency_id;
        $expense->user_id = $recurring->user_id;
        $expense->created_by = $recurring->created_by;
        $expense->item_name = $recurring->item_name;
        $expense->description = $recurring->description;
        $expense->price = $recurring->price;
        $expense->purchase_from = $recurring->purchase_from;
        $expense->added_by = $recurring->added_by;
        $expense->purchase_date = now()->format('Y-m-d');
        $expense->status = 'approved';
        $expense->save();
    }

    private function isDaily($recurring)
    {
        return $recurring->rotation === 'daily';
    }

    private function isWeekly($recurring)
    {
        $today = now()->timezone($recurring->company->timezone);
        $isWeekly = ($today->dayOfWeek === $recurring->day_of_week);

        return $recurring->rotation === 'weekly' && $isWeekly;
    }

    private function isBiWeekly($recurring)
    {
        $today = now()->timezone($recurring->company->timezone);
        $isWeekly = ($today->dayOfWeek === $recurring->day_of_week);
        $isBiWeekly = ($isWeekly && $today->weekOfYear % 2 === 0);

        return ($recurring->rotation === 'bi-weekly' && $isBiWeekly);
    }

    private function isMonthly($recurring)
    {
        $today = now()->timezone($recurring->company->timezone);
        $isMonthly = ($today->day === $recurring->day_of_month);

        return ($recurring->rotation === 'monthly' && $isMonthly);
    }

    private function isQuarterly($recurring)
    {
        $today = now()->timezone($recurring->company->timezone);
        $isMonthly = ($today->day === $recurring->day_of_month);
        $isQuarterly = ($isMonthly && $today->month % 3 === 1);

        return ($recurring->rotation === 'quarterly' && $isQuarterly);
    }

    private function isHalfYearly($recurring)
    {
        $today = now()->timezone($recurring->company->timezone);
        $isMonthly = ($today->day === $recurring->day_of_month);
        $isHalfYearly = ($isMonthly && $today->month % 6 === 1);

        return ($recurring->rotation === 'half-yearly' && $isHalfYearly);
    }

    private function isAnnually($recurring)
    {
        $today = now()->timezone($recurring->company->timezone);
        $isMonthly = ($today->day === $recurring->day_of_month);
        $isAnnually = ($isMonthly && $today->month % 12 === 1);

        return ($recurring->rotation === 'annually' && $isAnnually);
    }

}
