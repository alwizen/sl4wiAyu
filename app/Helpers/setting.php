<?php

if (!function_exists('setting')) {
    function setting(string $key = null): mixed
    {
        $setting = cache()->remember('sppg_setting', 3600, function () {
            return \App\Models\SppgSetting::first();
        });

        if (!$setting) return null;

        return match ($key) {
            null => $setting,
            'logo_light_url' => $setting->logo_light ? asset('storage/' . $setting->logo_light) : null,
            'logo_dark_url' => $setting->logo_dark ? asset('storage/' . $setting->logo_dark) : null,
            'favicon_url' => $setting->favicon ? asset('storage/' . $setting->favicon) : null,
            default => $setting->{$key} ?? null,
        };
    }
}
