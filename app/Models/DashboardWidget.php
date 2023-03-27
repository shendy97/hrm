<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\DashboardWidget
 *
 * @property int $id
 * @property string $widget_name
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $dashboard_type
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget query()
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereDashboardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereWidgetName($value)
 * @mixin \Eloquent
 * @property int|null $company_id
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|DashboardWidget whereCompanyId($value)
 */
class DashboardWidget extends BaseModel
{
    use HasCompany;
    protected $fillable = ['widget_name', 'status', 'dashboard_type', 'company_id'];
}
