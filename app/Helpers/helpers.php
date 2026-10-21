<?php

use App\Models\Setting;

if (!function_exists('format_rupiah')) {
    function format_rupiah($number, $withPrefix = true)
    {
        $formatted = number_format((float) $number, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('app_setting')) {
    function app_setting(string $key, $default = null)
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('app_name')) {
    function app_name()
    {
        return app_setting('app_name', 'Warung Aboen');
    }
}

if (!function_exists('app_local_ip')) {
    function app_local_ip()
    {
        $ip = gethostbyname(gethostname());
        if (!$ip || $ip === '127.0.0.1' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '192.168.1.116';
        }
        return $ip;
    }
}

if (!function_exists('app_mobile_url')) {
    function app_mobile_url()
    {
        return 'http://' . app_local_ip() . ':8000';
    }
}
