<?php

namespace App\Observers;

use App\Events\NewCompanyCreatedEvent;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\RolePermissionController;
use App\Models\AttendanceSetting;
use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\Currency;
use App\Models\EmailNotificationSetting;
use App\Models\EmployeeDetails;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\GlobalSetting;
use App\Models\GoogleCalendarModule;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\Lead;
use App\Models\LogTimeFor;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Project;
use App\Models\ProjectSetting;
use App\Models\ProjectTimeLog;
use App\Models\Role;
use App\Models\SlackSetting;
use App\Models\Task;
use App\Models\ThemeSetting;
use App\Models\Ticket;
use App\Models\TicketEmailSetting;
use App\Models\TicketType;
use App\Models\User;
use App\Models\CurrencyFormatSetting;
use App\Models\CustomFieldGroup;
use App\Models\DashboardWidget;
use App\Models\DiscussionCategory;
use App\Models\EmployeeShift;
use App\Models\LeadCustomForm;
use App\Models\LeadSource;
use App\Models\MessageSetting;
use App\Models\TicketChannel;
use App\Models\TicketCustomForm;
use App\Models\LeadStatus;
use App\Models\LeaveType;
use App\Models\PermissionRole;
use App\Models\ProjectStatusSetting;
use App\Models\TaskboardColumn;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class CompanyObserver
{

    protected array $permissionTypes = [
        'added' => 1,
        'owned' => 2,
        'both' => 3,
        'all' => 4,
        'none' => 5
    ];

    public function creating(Company $company)
    {
        $company->hash = md5(microtime());
        $this->dateFormats($company);
    }

    public function saving(Company $company)
    {

        session()->forget(['company', 'company.*', 'global_setting', 'global_setting.*', 'companyOrGlobalSetting', 'company.currency', 'company.paymentGatewayCredentials']);

        $user = user();

        if ($user) {
            $company->last_updated_by = $user->id;
        }


        if ($company->isDirty('date_format')) {
            $this->dateFormats($company);
        }

        if (!isRunningInConsoleOrSeeding() && $company->isDirty('currency_id') && !is_null(user())) {
            $allClients = User::allClients();
            $clientsArray = $allClients->pluck('id')->toArray();

            $appSettings = new AppSettingController();
            $appSettings->deleteSessions($clientsArray);
        }

        // IsRunningInConsoleOrSeeding is added to prevent running seeder
        // for the case of running company migration before having global_settings table
        if ($company->id === 1 && isWorksuite() && !isRunningInConsoleOrSeeding()) {
            $global = GlobalSetting::first();
            $global->global_app_name = $company->app_name;
            $global->logo_background_color = $company->logo_background_color;
            $global->header_color = $company->header_color;
            $global->login_background = $company->login_background;
            $global->sidebar_logo_style = $company->sidebar_logo_style;
            $global->auth_theme = $company->auth_theme;
            $global->light_logo = $company->light_logo;
            $global->favicon = $company->favicon;
            $global->logo = $company->logo;
            $global->saveQuietly();
        }
    }

    //phpcs:ignore
    public function created(Company $company)
    {
        $this->currencies($company);
        $this->companyAddress($company);
        $this->roles($company);
        $this->employeeShift($company);
        $this->attendanceSetting($company);
        $this->customFieldGroup($company);
        $this->dashboardWidgets($company);
        $this->discussionCategory($company);
        $this->emailNotificationSettings($company);
        $this->invoiceSetting($company);
        $this->leadCustomForms($company);
        $this->leadSources($company);
        $this->leaveType($company);
        $this->logTimeFor($company);
        $this->messageSetting($company);
        $this->projectSetting($company);
        $this->slackSetting($company);
        $this->ticketChannel($company);
        $this->ticketType($company);
        $this->customForms($company);
        $this->taskBoard($company);
        $this->projectStatusSettings($company);

        $company->paymentGatewayCredentials()->create();
        $company->taskSetting()->create();
        $company->leaveSetting()->create();
        $this->dateFormats($company);
        $this->moduleSettings($company);
        $this->themeSetting($company);
        $this->ticketEmailSetting($company);
        $this->googleCalendar($company);

        // Will be used in various module
        event(new NewCompanyCreatedEvent($company));


    }

    private function currencies($company)
    {
        $currency = new Currency();
        $currency->currency_name = 'Dollars';
        $currency->currency_symbol = '$';
        $currency->currency_code = 'USD';
        $currency->exchange_rate = 1;
        $currency->currency_position = 'left';
        $currency->no_of_decimal = 2;
        $currency->thousand_separator = ',';
        $currency->decimal_separator = '.';
        $currency->company()->associate($company);
        $currency->saveQuietly();

        // Save First currency to company default currency
        $company->currency_id = $currency->id;
        $company->saveQuietly();


        $currency = new Currency();
        $currency->currency_name = 'Pounds';
        $currency->currency_symbol = '£';
        $currency->currency_code = 'GBP';
        $currency->exchange_rate = 1;
        $currency->currency_position = 'left';
        $currency->no_of_decimal = 2;
        $currency->thousand_separator = ',';
        $currency->decimal_separator = '.';
        $currency->company()->associate($company);
        $currency->saveQuietly();

        $currency = new Currency();
        $currency->currency_name = 'Euros';
        $currency->currency_symbol = '€';
        $currency->currency_code = 'EUR';
        $currency->exchange_rate = 1;
        $currency->currency_position = 'left';
        $currency->no_of_decimal = 2;
        $currency->thousand_separator = ',';
        $currency->decimal_separator = '.';
        $currency->company()->associate($company);
        $currency->saveQuietly();

        $currency = new Currency();
        $currency->currency_name = 'Rupee';
        $currency->currency_symbol = '₹';
        $currency->currency_code = 'INR';
        $currency->exchange_rate = 1;
        $currency->currency_position = 'left';
        $currency->no_of_decimal = 2;
        $currency->thousand_separator = ',';
        $currency->decimal_separator = '.';
        $currency->company()->associate($company);
        $currency->saveQuietly();
    }

    private function employeeShift($company)
    {

        $employeeShift = new EmployeeShift();
        $employeeShift->shift_name = 'General Shift';
        $employeeShift->company_id = $company->id;
        $employeeShift->shift_short_code = 'GS';
        $employeeShift->color = '#99C7F1';
        $employeeShift->office_start_time = '09:00:00';
        $employeeShift->office_end_time = '18:00:00';
        $employeeShift->late_mark_duration = 20;
        $employeeShift->clockin_in_day = 2;
        $employeeShift->office_open_days = '[1,2,3,4,5]';
        $employeeShift->saveQuietly();
    }

    private function attendanceSetting($company)
    {
        $setting = new AttendanceSetting();
        $setting->company_id = $company->id;
        $setting->office_start_time = '09:00:00';
        $setting->office_end_time = '18:00:00';
        $setting->late_mark_duration = 20;
        $setting->default_employee_shift = EmployeeShift::where('company_id', $company->id)->first()->id;
        $setting->alert_after_status = 0;
        $setting->saveQuietly();
    }

    private function customFieldGroup($company)
    {

        $fields = [
            ['name' => 'Client', 'model' => ClientDetails::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Employee', 'model' => EmployeeDetails::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Project', 'model' => Project::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Invoice', 'model' => Invoice::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Estimate', 'model' => Estimate::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Task', 'model' => Task::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Expense', 'model' => Expense::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Lead', 'model' => Lead::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Product', 'model' => Product::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Ticket', 'model' => Ticket::CUSTOM_FIELD_MODEL, 'company_id' => $company->id],
            ['name' => 'Time Log', 'model' => ProjectTimeLog::CUSTOM_FIELD_MODEL, 'company_id' => $company->id]

        ];

        CustomFieldGroup::insert($fields);

    }

    private function dashboardWidgets($company)
    {

        $widgets = [
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_clients', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_employees', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_projects', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_unpaid_invoices', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_hours_logged', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_pending_tasks', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_today_attendance', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'total_unresolved_tickets', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'recent_earnings', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'settings_leaves', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'new_tickets', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'overdue_tasks', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'pending_follow_up', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'project_activity_timeline', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'user_activity_timeline', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-dashboard', 'widget_name' => 'timelogs', 'company_id' => $company->id],

            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_clients', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_leads', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_lead_conversions', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_contracts_generated', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'total_contracts_signed', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'client_wise_earnings', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'client_wise_timelogs', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'lead_vs_status', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'lead_vs_source', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'latest_client', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-client-dashboard', 'widget_name' => 'recent_login_activities', 'company_id' => $company->id],

            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_paid_invoices', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_expenses', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_earnings', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_pending_amount', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'invoice_overview', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'estimate_overview', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'proposal_overview', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'earnings_by_client', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'earnings_by_projects', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-finance-dashboard', 'widget_name' => 'total_unpaid_invoices', 'company_id' => $company->id],

            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_leaves_approved', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_new_employee', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_employee_exits', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'average_attendance', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'department_wise_employee', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'designation_wise_employee', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'gender_wise_employee', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'role_wise_employee', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'leaves_taken', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'late_attendance_mark', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'headcount', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'joining_vs_attrition', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'birthday', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-hr-dashboard', 'widget_name' => 'total_today_attendance', 'company_id' => $company->id],

            ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'total_project', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'total_hours_logged', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'total_overdue_project', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'status_wise_project', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-project-dashboard', 'widget_name' => 'pending_milestone', 'company_id' => $company->id],

            ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'total_tickets', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'total_unassigned_ticket', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'type_wise_ticket', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'status_wise_ticket', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'channel_wise_ticket', 'company_id' => $company->id],
            ['dashboard_type' => 'admin-ticket-dashboard', 'widget_name' => 'new_tickets', 'company_id' => $company->id],

            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'profile', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'shift_schedule', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'birthday', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'notices', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'tasks', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'projects', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'my_task', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'my_calender', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'week_timelog', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'leave', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'lead', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'work_from_home', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'appreciation', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'work_anniversary', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'ticket', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'notice_period_duration', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'probation_date', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'contract_date', 'company_id' => $company->id],
            ['dashboard_type' => 'private-dashboard', 'widget_name' => 'internship_date', 'company_id' => $company->id],
        ];

        DashboardWidget::insert($widgets);

    }

    private function discussionCategory($company)
    {
        DiscussionCategory::create([
            'name' => 'General',
            'color' => '#3498DB',
            'company_id' => $company->id
        ]);
    }

    private function emailNotificationSettings($company)
    {

        $data = [
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'New Expense/Added by Admin',
                'slug' => 'new-expenseadded-by-admin',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'New Expense/Added by Member',
                'slug' => 'new-expenseadded-by-member',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Expense Status Changed',
                'slug' => 'expense-status-changed',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'New Support Ticket Request',
                'slug' => 'new-support-ticket-request',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'New Leave Application',
                'slug' => 'new-leave-application',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Task Completed',
                'slug' => 'task-completed',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Invoice Create/Update Notification',
                'slug' => 'invoice-createupdate-notification',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Discussion Reply',
                'slug' => 'discussion-reply',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'New Product Purchase Request',
                'slug' => 'new-product-purchase-request',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Lead notification',
                'slug' => 'lead-notification',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'no',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Order Create/Update Notification',
                'slug' => 'order-createupdate-notification',

            ],
            [
                'send_email' => 'no',
                'company_id' => $company->id,
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'User Join via Invitation',
                'slug' => 'user-join-via-invitation',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'no',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Follow Up Reminder',
                'slug' => 'follow-up-reminder',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'User Registration/Added by Admin',
                'slug' => 'user-registrationadded-by-admin',

            ],
            [
                'send_email' => 'yes',
                'company_id' => $company->id,
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Employee Assign to Project',
                'slug' => 'employee-assign-to-project',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'no',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'New Notice Published',
                'slug' => 'new-notice-published',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'User Assign to Task',
                'slug' => 'user-assign-to-task',

            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'yes',
                'setting_name' => 'Birthday notification',
                'slug' => 'birthday-notification',
            ],
            [
                'company_id' => $company->id,
                'send_email' => 'yes',
                'send_push' => 'no',
                'send_slack' => 'no',
                'setting_name' => 'Payment Notification',
                'slug' => 'payment-notification',
            ],
            [
                'send_email' => 'yes',
                'send_push' => 'no',
                'company_id' => $company->id,
                'send_slack' => 'no',
                'setting_name' => 'Employee Appreciation',
                'slug' => 'appreciation-notification',
            ],
        ];

        EmailNotificationSetting::insert($data);
    }

    private function invoiceSetting($company)
    {

        InvoiceSetting::create([
            'company_id' => $company->id,
            'credit_note_digit' => 3,
            'credit_note_prefix' => 'CN',
            'credit_note_number_separator' => '#',
            'due_after' => 15,
            'estimate_digit' => 3,
            'estimate_prefix' => 'EST',
            'estimate_number_separator' => '#',
            'estimate_terms' => null,
            'gst_number' => null,
            'hsn_sac_code_show' => 0,
            'invoice_digit' => 3,
            'invoice_prefix' => 'INV',
            'invoice_number_separator' => '#',
            'invoice_terms' => 'Thank you for your business.',
            'locale' => 'en',
            'logo' => null,
            'reminder' => null,
            'send_reminder' => 0,
            'send_reminder_after' => 0,
            'show_client_company_address' => 'yes',
            'show_client_company_name' => 'yes',
            'show_client_email' => 'yes',
            'show_client_name' => 'yes',
            'show_client_phone' => 'yes',
            'show_gst' => 'no',
            'show_project' => 0,
            'tax_calculation_msg' => 0,
            'template' => 'invoice-5',
        ]);
    }

    private function leadCustomForms($company)
    {
        $data = [
            [
                'company_id' => $company->id,
                'status' => 'active',
                'field_display_name' => 'Name',
                'field_name' => 'name',
                'field_order' => 1,
                'required' => 1,
            ],
            [
                'company_id' => $company->id,
                'status' => 'active',
                'field_display_name' => 'Email',
                'field_name' => 'email',
                'field_order' => 2,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Company Name',
                'status' => 'active',
                'field_name' => 'company_name',
                'field_order' => 3,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Website',
                'field_name' => 'website',
                'status' => 'active',
                'field_order' => 4,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Address',
                'field_name' => 'address',
                'status' => 'active',
                'field_order' => 5,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Mobile',
                'field_name' => 'mobile',
                'field_order' => 6,
                'status' => 'active',
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Message',
                'field_name' => 'message',
                'status' => 'active',
                'field_order' => 7,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'City',
                'status' => 'active',
                'field_name' => 'city',
                'field_order' => 1,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'State',
                'status' => 'active',
                'field_name' => 'state',
                'field_order' => 2,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Country',
                'field_name' => 'country',
                'status' => 'active',
                'field_order' => 3,
                'required' => 0,

            ],
            [
                'company_id' => $company->id,
                'field_display_name' => 'Postal Code',
                'field_name' => 'postal_code',
                'status' => 'active',
                'field_order' => 4,
                'required' => 0,

            ],
        ];

        LeadCustomForm::insert($data);

    }

    private function leadSources($company)
    {
        $sources = [
            ['type' => 'email', 'company_id' => $company->id],
            ['type' => 'google', 'company_id' => $company->id],
            ['type' => 'facebook', 'company_id' => $company->id],
            ['type' => 'friend', 'company_id' => $company->id],
            ['type' => 'direct visit', 'company_id' => $company->id],
            ['type' => 'tv ad', 'company_id' => $company->id]
        ];

        LeadSource::insert($sources);

        $status = [
            ['type' => 'pending', 'priority' => 1, 'default' => 1, 'label_color' => '#FFE700', 'company_id' => $company->id],
            ['type' => 'inprocess', 'priority' => 2, 'default' => 0, 'label_color' => '#009EFF', 'company_id' => $company->id],
            ['type' => 'converted', 'priority' => 3, 'default' => 0, 'label_color' => '#1FAE07', 'company_id' => $company->id]
        ];

        LeadStatus::insert($status);

    }

    private function leaveType($company)
    {
        $status = [
            ['type_name' => 'Casual', 'color' => '#16813D', 'company_id' => $company->id],
            ['type_name' => 'Sick', 'color' => '#DB1313', 'company_id' => $company->id],
            ['type_name' => 'Earned', 'color' => '#B078C6', 'company_id' => $company->id]
        ];

        LeaveType::insert($status);

    }

    private function logTimeFor($company)
    {
        $logTimeFor = new LogTimeFor();
        $logTimeFor->company_id = $company->id;
        $logTimeFor->log_time_for = 'project';
        $logTimeFor->saveQuietly();
    }

    private function messageSetting($company)
    {
        $setting = new MessageSetting();
        $setting->company_id = $company->id;
        $setting->allow_client_admin = 'no';
        $setting->allow_client_employee = 'no';
        $setting->saveQuietly();
    }

    private function projectSetting($company)
    {
        $project_setting = new ProjectSetting();
        $project_setting->company_id = $company->id;
        $project_setting->send_reminder = 'no';
        $project_setting->remind_time = 5;
        $project_setting->remind_type = 'days';

        $project_setting->saveQuietly();
    }

    private function slackSetting($company)
    {
        $slack = new SlackSetting();
        $slack->company_id = $company->id;
        $slack->slack_webhook = null;
        $slack->slack_logo = null;
        $slack->saveQuietly();
    }

    private function ticketChannel($company)
    {
        $channels = [
            ['channel_name' => 'Email', 'company_id' => $company->id],
            ['channel_name' => 'Phone', 'company_id' => $company->id],
            ['channel_name' => 'Twitter', 'company_id' => $company->id],
            ['channel_name' => 'Facebook', 'company_id' => $company->id]
        ];

        TicketChannel::insert($channels);
    }

    private function ticketType($company)
    {
        $types = [
            ['type' => 'Bug', 'company_id' => $company->id],
            ['type' => 'Suggestion', 'company_id' => $company->id],
            ['type' => 'Question', 'company_id' => $company->id],
            ['type' => 'Sales', 'company_id' => $company->id],
            ['type' => 'Code', 'company_id' => $company->id],
            ['type' => 'Management', 'company_id' => $company->id],
            ['type' => 'Problem', 'company_id' => $company->id],
            ['type' => 'Incident', 'company_id' => $company->id],
            ['type' => 'Feature Request', 'company_id' => $company->id],
        ];

        TicketType::insert($types);
    }

    private function customForms($company)
    {
        $fields = ['Name', 'Email', 'Ticket Subject', 'Ticket Description', 'Type', 'Priority'];
        $fieldsName = ['name', 'email', 'ticket_subject', 'ticket_description', 'type', 'priority'];
        $fieldsType = ['text', 'text', 'text', 'textarea', 'select', 'select'];

        foreach ($fields as $key => $value) {

            TicketCustomForm::create([
                'field_display_name' => $value,
                'field_name' => $fieldsName[$key],
                'field_order' => $key + 1,
                'field_type' => $fieldsType[$key],
                'company_id' => $company->id,
            ]);

        }
    }

    private function companyAddress($company)
    {
        $company->companyAddress()->create([
            'address' => $company->address,
            'location' => 'Jaipur, India',
            'is_default' => 1,
            'company_id' => $company->id,
        ]);
    }

    private function roles($company)
    {
        $admin = new Role();
        $admin->name = 'admin';
        $admin->company_id = $company->id;
        $admin->display_name = 'App Administrator'; // optional
        $admin->description = 'Admin is allowed to manage everything of the app.'; // optional
        $admin->saveQuietly();

        $employee = new Role();
        $employee->name = 'employee';
        $employee->company_id = $company->id;
        $employee->display_name = 'Employee'; // optional
        $employee->description = 'Employee can see tasks and projects assigned to him.'; // optional
        $employee->saveQuietly();

        $client = new Role();
        $client->name = 'client';
        $client->company_id = $company->id;
        $client->display_name = 'Client'; // optional
        $client->description = 'Client can see own tasks and projects.'; // optional
        $client->saveQuietly();

        $allPermissions = Permission::all();

        PermissionRole::where('role_id', $admin->id)->delete();
        PermissionRole::where('role_id', $employee->id)->delete();
        PermissionRole::where('role_id', $client->id)->delete();

        $rolePermissionController = new RolePermissionController();
        $rolePermissionController->permissionRole($allPermissions, 'employee', $company->id);
        $rolePermissionController->rolePermissionInsert($allPermissions, $admin->id, 'all');
        $rolePermissionController->permissionRole($allPermissions, 'client', $company->id);
    }

    private function taskBoard($company)
    {
        $columns = [
            ['column_name' => 'Incomplete', 'label_color' => '#d21010', 'priority' => 1, 'slug' => str_slug('Incomplete', '_'), 'company_id' => $company->id],
            ['column_name' => 'To Do', 'label_color' => '#f5c308', 'priority' => 2, 'slug' => str_slug('To Do', '_'), 'company_id' => $company->id],
            ['column_name' => 'Doing', 'label_color' => '#00b5ff', 'priority' => 3, 'slug' => str_slug('Doing', '_'), 'company_id' => $company->id],
            ['column_name' => 'Completed', 'label_color' => '#679c0d', 'priority' => 4, 'slug' => str_slug('Completed', '_'), 'company_id' => $company->id],
        ];

        TaskboardColumn::insert($columns);

        $board = TaskboardColumn::where('slug', 'incomplete')
            ->where('company_id', $company->id)
            ->first();

        $company->default_task_status = $board->id;
        $company->saveQuietly();
    }

    public function projectStatusSettings($company)
    {
        $columns = [
            [
                'company_id' => $company->id,
                'status_name' => 'in progress',
                'color' => '#00b5ff',
                'status' => 'active',
                'default_status' => 1
            ],
            [
                'company_id' => $company->id,
                'status_name' => 'not started',
                'color' => '#616e80',
                'status' => 'active',
                'default_status' => '0'
            ],
            [
                'company_id' => $company->id,
                'status_name' => 'on hold',
                'color' => '#f5c308',
                'status' => 'active',
                'default_status' => '0'
            ],
            [
                'company_id' => $company->id,
                'status_name' => 'canceled',
                'color' => '#d21010',
                'status' => 'active',
                'default_status' => '0'
            ],
            [
                'company_id' => $company->id,
                'status_name' => 'finished',
                'color' => '#679c0d',
                'status' => 'active',
                'default_status' => '0'
            ]
        ];

        ProjectStatusSetting::insert($columns);

    }

    private function dateFormats($company)
    {
        switch ($company->date_format) {

        case 'm-d-Y':
            $company->date_picker_format = 'mm-dd-yyyy';
            $company->moment_format = 'MM-DD-YYYY';
            break;
        case 'Y-m-d':
            $company->date_picker_format = 'yyyy-mm-dd';
            $company->moment_format = 'YYYY-MM-DD';
            break;
        case 'd.m.Y':
            $company->date_picker_format = 'dd.mm.yyyy';
            $company->moment_format = 'DD.MM.YYYY';
            break;
        case 'm.d.Y':
            $company->date_picker_format = 'mm.dd.yyyy';
            $company->moment_format = 'MM.DD.YYYY';
            break;
        case 'Y.m.d':
            $company->date_picker_format = 'yyyy.mm.dd';
            $company->moment_format = 'YYYY.MM.DD';
            break;
        case 'd/m/Y':
            $company->date_picker_format = 'dd/mm/yyyy';
            $company->moment_format = 'DD/MM/YYYY';
            break;
        case 'Y/m/d':
            $company->date_picker_format = 'yyyy/mm/dd';
            $company->moment_format = 'YYYY/MM/DD';
            break;
        case 'd-M-Y':
            $company->date_picker_format = 'dd-M-yyyy';
            $company->moment_format = 'DD-MMM-YYYY';
            break;
        case 'd/M/Y':
            $company->date_picker_format = 'dd/M/yyyy';
            $company->moment_format = 'DD/MMM/YYYY';
            break;
        case 'd.M.Y':
            $company->date_picker_format = 'dd.M.yyyy';
            $company->moment_format = 'DD.MMM.YYYY';
            break;
        case 'd M Y':
            $company->date_picker_format = 'dd M yyyy';
            $company->moment_format = 'DD MMM YYYY';
            break;
        case 'd F, Y':
            $company->date_picker_format = 'dd MM, yyyy';
            $company->moment_format = 'yyyy-mm-d';
            break;
        case 'd D M Y':
            $company->date_picker_format = 'dd D M yyyy';
            $company->moment_format = 'DD ddd MMM YYYY';
            break;
        case 'D d M Y':
            $company->date_picker_format = 'D dd M yyyy';
            $company->moment_format = 'ddd DD MMMM YYYY';
            break;
        default:
            $company->date_picker_format = 'dd-mm-yyyy';
            $company->moment_format = 'DD-MM-YYYY';
            break;
        }
    }

    private function moduleSettings($company)
    {

        $clientModules = [
            'clients',
            'projects',
            'tickets',
            'invoices',
            'estimates',
            'events',
            'messages',
            'tasks',
            'timelogs',
            'contracts',
            'notices',
            'payments',
            'orders',
            'knowledgebase',
        ];

        $otherModules = [
            'employees',
            'attendance',
            'expenses',
            'leaves',
            'leads',
            'holidays',
            'products',
            'reports',
            'settings',
        ];


        $data = [
            'admin' => [
                ...$clientModules, ...$otherModules
            ],
            'employee' => [
                ...$clientModules, ...$otherModules
            ]
            ,
            'client' => [
                ...$clientModules
            ]
        ];


        foreach ($data as $type => $moduleList) {

            $moduleSettings = [];

            foreach ($moduleList as $module) {
                $moduleSettings[] = [
                    'company_id' => $company->id,
                    'type' => $type,
                    'module_name' => $module,
                    'status' => 'active'
                ];
            }

            ModuleSetting::insert($moduleSettings);


        }

    }

    private function themeSetting($company)
    {
        $themeSettings = [
            ['panel' => 'admin', 'company_id' => $company->id, 'header_color' => '#1d82f5', 'sidebar_color' => '#171F29', 'sidebar_text_color' => '#99A5B5', 'link_color' => '#F7FAFF'],
            ['panel' => 'project_admin', 'company_id' => $company->id, 'header_color' => '#1d82f5', 'sidebar_color' => '#171F29', 'sidebar_text_color' => '#99A5B5', 'link_color' => '#F7FAFF'],
            ['panel' => 'employee', 'company_id' => $company->id, 'header_color' => '#1d82f5', 'sidebar_color' => '#171F29', 'sidebar_text_color' => '#99A5B5', 'link_color' => '#F7FAFF'],
            ['panel' => 'client', 'company_id' => $company->id, 'header_color' => '#1d82f5', 'sidebar_color' => '#171F29', 'sidebar_text_color' => '#99A5B5', 'link_color' => '#F7FAFF'],
        ];

        ThemeSetting::insert($themeSettings);
    }

    private function ticketEmailSetting($company)
    {
        $setting = new TicketEmailSetting();
        $setting->company_id = $company->id;
        $setting->saveQuietly();
    }

    private function googleCalendar($company)
    {
        $module = new GoogleCalendarModule();
        $module->company_id = $company->id;
        $module->lead_status = 0;
        $module->leave_status = 0;
        $module->invoice_status = 0;
        $module->contract_status = 0;
        $module->task_status = 0;
        $module->event_status = 0;
        $module->holiday_status = 0;
        $module->saveQuietly();
    }

}
