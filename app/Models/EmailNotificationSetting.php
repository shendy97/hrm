<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\EmailNotificationSetting
 *
 * @property int $id
 * @property string $setting_name
 * @property string $send_email
 * @property string $send_slack
 * @property string $send_push
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $slug
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereSendEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereSendPush($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereSendSlack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereSettingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|EmailNotificationSetting whereCompanyId($value)
 */
class EmailNotificationSetting extends BaseModel
{

    use HasCompany;

    protected $guarded = ['id'];

    public static function userAssignTask()
    {
        return EmailNotificationSetting::where('slug', 'user-assign-to-task')->first();
    }

}
