<?php

namespace App\Models;

/**
 * App\Models\LanguageSetting
 *
 * @property int $id
 * @property string $language_code
 * @property string $language_name
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereLanguageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereLanguageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $flag_code
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageSetting whereFlagCode($value)
 */
class LanguageSetting extends BaseModel
{
    const LANGUAGES_TRANS = [
        'en' => 'English',
        'ar' => 'عربي',
        'de' => 'Deutsch',
        'es' => 'Español',
        'et' => 'eesti keel',
        'fa' => 'فارسی',
        'fr' => 'français',
        'gr' => 'Ελληνικά',
        'it' => 'Italiano',
        'nl' => 'Nederlands',
        'pl' => 'Polski',
        'pt' => 'Português',
        'pt-br' => 'Português (Brasil)',
        'ro' => 'Română',
        'ru' => 'Русский',
        'tr' => 'Türk',
        'zh-CN' => '中国人',
        'zh-TW' => '中國人'
    ];
}
