<?php

namespace App\Console\Commands;

use App\Http\Controllers\DatabaseBackupSettingController;
use App\Models\DatabaseBackupSetting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AutoDatabaseBackup extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-database-backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto create database backup';

    /**
     * Execute the console command.
     *
     * @return bool
     */

    public function handle()
    {
        $backupSetting = DatabaseBackupSetting::first();

        $dbController = new DatabaseBackupSettingController();

        // Return false when there is no record in database and status is also inactive
        if (!$backupSetting || $backupSetting->status == 'inactive') {
            return false;
        }

        $backups = $dbController->getBackup();

        $backups = array_reverse($backups);

        if (count($backups) == 0) {
            Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]);

            return true;
        }

        $date = Carbon::parse(($backups)[0]['last_modified']);
        $dateDifference = $date->diffInDays(now());

        if ($dateDifference < $backupSetting->backup_after_days) {
            return false;
        }

        $nowTimeWithTimeZone = now()->setTimezone(global_setting()->timezone)->format('H:i:s');
        $settingHourOfDay = Carbon::createFromFormat('H:i:s', $backupSetting->hour_of_day)->format('H:i:s');

        if ($nowTimeWithTimeZone >= $settingHourOfDay) {
            Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]);
        }

        return true;
    }

}
