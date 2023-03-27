<?php

namespace Database\Seeders;

use App\Models\DatabaseBackupSetting;
use App\Models\GdprSetting;
use App\Models\GoogleCalendarModule;
use App\Models\LanguageSetting;
use App\Models\PaymentGatewayCredentials;
use App\Models\PusherSetting;
use App\Models\PushNotificationSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use App\Models\TaskboardColumn;
use App\Models\TranslateSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class CoreDatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->dashboardBackupSetting();
        $this->fileStorageSetting();
        $this->gdprSetting();
        $this->languageSettings();
        $this->socialAuth();
        $this->appreciationIcon();
        TranslateSetting::create(['google_key' => null]);
        $this->pushNotification();
    }

    private function dashboardBackupSetting()
    {
        $backupSetting = new DatabaseBackupSetting();
        $backupSetting->status = 'inactive';
        $backupSetting->hour_of_day = '';
        $backupSetting->backup_after_days = '0';
        $backupSetting->delete_backup_after_days = '0';
        $backupSetting->save();
    }

    private function fileStorageSetting()
    {
        $storage = new StorageSetting();
        $storage->filesystem = 'local';
        $storage->status = 'enabled';
        $storage->save();
    }

    private function gdprSetting()
    {
        $gdpr = new GdprSetting();
        $gdpr->create();
    }

    private function languageSettings()
    {
        $languages = [
            [
                'language_code' => 'en',
                'flag_code' => 'en',
                'language_name' => 'English',
                'status' => 'enabled',
            ],
            [
                'language_code' => 'ar',
                'flag_code' => 'sa',
                'language_name' => 'Arabic',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'de',
                'flag_code' => 'de',
                'language_name' => 'German',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'es',
                'flag_code' => 'es',
                'language_name' => 'Spanish',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'et',
                'flag_code' => 'et',
                'language_name' => 'Estonian',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'fa',
                'flag_code' => 'ir',
                'language_name' => 'Farsi',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'fr',
                'flag_code' => 'fr',
                'language_name' => 'French',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'gr',
                'flag_code' => 'gr',
                'language_name' => 'Greek',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'it',
                'flag_code' => 'it',
                'language_name' => 'Italian',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'nl',
                'flag_code' => 'nl',
                'language_name' => 'Dutch',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'pl',
                'flag_code' => 'pl',
                'language_name' => 'Polish',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'pt',
                'flag_code' => 'pt',
                'language_name' => 'Portuguese',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'pt-br',
                'flag_code' => 'br',
                'language_name' => 'Portuguese (Brazil)',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'ro',
                'flag_code' => 'ro',
                'language_name' => 'Romanian',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'ru',
                'flag_code' => 'ru',
                'language_name' => 'Russian',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'tr',
                'flag_code' => 'tr',
                'language_name' => 'Turkish',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'zh-CN',
                'flag_code' => 'cn',
                'language_name' => 'Chinese (S)',
                'status' => 'disabled',

            ],
            [
                'language_code' => 'zh-TW',
                'flag_code' => 'cn',
                'language_name' => 'Chinese (T)',
                'status' => 'disabled',

            ],
        ];

        LanguageSetting::insert($languages);
    }

    private function socialAuth()
    {
        SocialAuthSetting::create([
            'facebook_status' => 'disable',
            'google_status' => 'disable',
            'linkedin_status' => 'disable',
            'twitter_status' => 'disable',
        ]);
    }

    private function pushNotification()
    {
        $slack = new PushNotificationSetting();
        $slack->onesignal_app_id = null;
        $slack->onesignal_rest_api_key = null;
        $slack->notification_logo = null;
        $slack->save();

        $pusherSetting = new PusherSetting();
        $pusherSetting->save();

    }

    private function appreciationIcon()
    {
        $icons = [
            ['title' => 'Trophy', 'icon' => 'trophy-fill'],
            ['title' => 'Thumbs Up', 'icon' => 'hand-thumbs-up-fill'],
            ['title' => 'Award', 'icon' => 'award-fill'],
            ['title' => 'Book', 'icon' => 'book-fill'],
            ['title' => 'Gift', 'icon' => 'gift-fill'],
            ['title' => 'Watch', 'icon' => 'watch'],
            ['title' => 'Cup', 'icon' => 'cup-hot-fill'],
            ['title' => 'Puzzle', 'icon' => 'puzzle-fill'],
            ['title' => 'Plane', 'icon' => 'airplane-fill'],
            ['title' => 'Money', 'icon' => 'piggy-bank-fill'],
        ];

        foreach ($icons as $icon) {
            \App\Models\AwardIcon::create($icon);
        }
    }

}

