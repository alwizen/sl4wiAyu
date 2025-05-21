<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppgSetting extends Model
{
    protected $fillable = [
        'sppg_name', 'address', 'logo_light', 'logo_dark', 'favicon'
    ];

    protected static function booted()
    {
        static::saved(function ($setting) {
            cache()->forget('sppg_setting');
            cache()->put('sppg_setting', $setting, 3600);
        });
    }
}
