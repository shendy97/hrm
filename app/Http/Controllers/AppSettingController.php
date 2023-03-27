<?php

namespace App\Http\Controllers;

use Form;
use DateTimeZone;
use Carbon\Carbon;
use App\Helper\Reply;
use App\Models\Company;
use App\Models\Session;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\Admin\App\UpdateGoogleMapSetting;
use App\Http\Requests\Admin\App\UpdateAppSetting;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class AppSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.appSettings';
        $this->activeSettingMenu = 'app_settings';

        $this->middleware(function ($request, $next) {

            abort_403(user()->permission('manage_app_setting') !== 'all');

            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $tab = request('tab');

        switch ($tab) {

        case 'file-upload-setting':
            $this->view = 'app-settings.ajax.file-upload-setting';
            break;
        case 'client-signup-setting':
            $this->view = 'app-settings.ajax.client-signup-setting';
            break;
        case 'google-map-setting':
            $this->view = 'app-settings.ajax.map-setting';
            break;
        default:
            $this->view = 'app-settings.ajax.app-setting';
            break;
        }

        $this->dateFormat = array_keys(Company::DATE_FORMATS);
        $this->timezones = DateTimeZone::listIdentifiers();
        $this->currencies = Currency::all();
        $this->dateObject = now();
        $this->cachedFile = File::exists(base_path('bootstrap/cache/config.php'));

        // Not fetching from session
        $this->globalSetting = GlobalSetting::first();

        $this->activeTab = $tab ?: 'app-setting';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('app-settings.index', $this->data);
    }

    /**
     * @param UpdateAppSetting $request
     * @param mixed $id
     * @return array
     * @throws BindingResolutionException
     * @throws CommandNotFoundException
     */
    // phpcs:ignore
    public function update(UpdateAppSetting $request, $id)
    {
        config(['filesystems.default' => 'local']);

        $tab = request('page');

        switch ($tab) {

        case 'file-upload-setting':
            $this->updateFileUploadSetting($request);
            break;
        case 'client-signup-setting':
            $this->updateClientSignupSetting($request);
            break;
        case 'google-map-setting':
            $this->updateGoogleMapSetting($request);
            break;
        default:
            $this->updateAppSetting($request);
            break;
        }

        session()->forget('company');
        session()->forget('global_setting');

        return Reply::success(__('messages.updateSuccess'));
    }

    public function globalSettingSave($request)
    {
        $globalSetting = GlobalSetting::first();
        $globalSetting->app_debug = $request->has('app_debug') && $request->app_debug == 'on' ? 1 : 0;
        $globalSetting->system_update = $request->has('system_update') && $request->system_update == 'on' ? 1 : 0;
        $globalSetting->session_driver = $request->session_driver;
        $globalSetting->locale = $request->locale;
        $globalSetting->save();
    }

    public function updateAppSetting($request)
    {
        $setting = \company();
        $setting->currency_id = $request->currency_id;
        $setting->timezone = $request->timezone;
        $setting->locale = $request->locale;
        $setting->date_format = $request->date_format;
        $setting->time_format = $request->time_format;
        $setting->moment_format = $this->momentFormat($setting->date_format);
        $setting->dashboard_clock = $request->has('dashboard_clock') && $request->dashboard_clock == 'on' ? 1 : 0;
        $setting->save();

        $this->globalSettingSave($request);

        $this->createCache($request);
    }

    public function updateFileUploadSetting($request)
    {
        if (!empty($request->allowed_file_types)) {
            $allowed_file_types = $request->allowed_file_types;

            $fileTypeArray = [];

            foreach (json_decode($allowed_file_types) as $file) {
                $fileTypeArray[] = $file->value;
            }
        }

        $globalSetting = GlobalSetting::first();
        $globalSetting->allowed_file_types = !empty($fileTypeArray) ? implode(',', $fileTypeArray) : '';
        $globalSetting->allowed_file_size = $request->allowed_file_size;
        $globalSetting->save();
    }

    public function updateClientSignupSetting($request)
    {
        $setting = \company();
        $setting->allow_client_signup = $request->allow_client_signup == 'on' ? 1 : 0;
        $setting->admin_client_signup_approval = $request->admin_client_signup_approval == 'on' ? 1 : 0;
        $setting->save();
    }

    public function updateGoogleMapSetting(UpdateAppSetting $request)
    {
        $globalSetting = \global_setting();
        $globalSetting->google_map_key = $request->google_map_key;
        $globalSetting->save();
        \session()->forget('global_setting');
    }

    /**
     * @param string $dateFormat
     * @return string
     */
    public function momentFormat($dateFormat)
    {
        $availableDateFormats = Company::DATE_FORMATS;

        return (isset($availableDateFormats[$dateFormat])) ? $availableDateFormats[$dateFormat] : 'DD-MM-YYYY';
    }

    private function createCache($request)
    {
        if ($request->cache) {
            try {
                Artisan::call('optimize');
                Artisan::call('route:clear');

            } catch (\Exception $e) {
                return $e->getMessage();
            }

        }
        else {
            Artisan::call('optimize:clear');
            Artisan::call('cache:clear');
        }
    }

    public function deleteSessions(array $usersIds = [])
    {
        if (!empty($usersIds)) {
            Session::whereIn('user_id', $usersIds)->where('user_id', '<>', user()->id)->delete();

            return Reply::success(__('messages.deleteSuccess'));
        }

        $table = config('session.table');
        DB::table($table)->truncate();

        return Reply::success(__('messages.deleteSuccess'));
    }

}
