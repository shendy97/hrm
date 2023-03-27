<?php

namespace App\Console\Commands;

use App\Events\BirthdayReminderEvent;
use App\Models\Company;
use App\Models\EmployeeDetails;
use Illuminate\Console\Command;

class BirthdayReminderCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send birthday notification to everyone';

    public function handle()
    {

        $currentDay = now()->format('m-d');

        $companies = Company::select('id')->get();

        foreach ($companies as $company) {

            $upcomingBirthday = EmployeeDetails::join('users', 'employee_details.user_id', '=', 'users.id')
                ->where('employee_details.company_id', $company->id)
                ->where('users.status', 'active')
                ->whereRaw('DATE_FORMAT(`date_of_birth`, "%m-%d") = "' . $currentDay . '"')
                ->orderBy('employee_details.date_of_birth')
                ->select('employee_details.company_id', 'employee_details.date_of_birth', 'users.name', 'users.image', 'users.id')
                ->get()->toArray();

            if ($upcomingBirthday != null) {
                event(new BirthdayReminderEvent($company, $upcomingBirthday));
            }
        }
    }

}
