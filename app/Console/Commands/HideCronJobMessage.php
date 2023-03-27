<?php

namespace App\Console\Commands;

use App\Models\GlobalSetting;
use Illuminate\Console\Command;

class HideCronJobMessage extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hide-cron-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hide cron job message.';

    /**
     * Execute the console command.
     *`
     * @return mixed
     */

    public function handle()
    {
        $setting = GlobalSetting::first();

        $setting->last_cron_run = now();
        $setting->hide_cron_message = 1;
        $setting->save();
    }

}
