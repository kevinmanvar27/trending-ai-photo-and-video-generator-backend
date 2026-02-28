<?php

use App\Models\Setting;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('settings')) {
    /**
     * Get all settings as array
     * 
     * @return array
     */
    function settings()
    {
        return Setting::getAllSettings();
    }
}

if (!function_exists('razorpay_config')) {
    /**
     * Get Razorpay configuration
     * 
     * @param string|null $key
     * @return mixed
     */
    function razorpay_config($key = null)
    {
        $config = app('razorpay.config');
        
        if ($key === null) {
            return $config;
        }
        
        return $config[$key] ?? null;
    }
}
