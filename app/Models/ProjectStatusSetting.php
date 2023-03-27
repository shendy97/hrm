<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\ProjectStatusSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $status_name
 * @property string $color
 * @property string $status
 * @property string $default_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereDefaultStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereStatusName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectStatusSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProjectStatusSetting extends BaseModel
{

    use HasFactory, HasCompany;

    const ACTIVE = '1';
    const INACTIVE = '0';

    protected $fillable = ['status_name', 'color', 'status', 'default_status'];

}
