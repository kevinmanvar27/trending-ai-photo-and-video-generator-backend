<?php

use App\Models\Setting;
use App\Models\Page;

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

if (!function_exists('active_pages')) {
    /**
     * Get all active pages for navigation
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function active_pages()
    {
        return Page::getActivePages();
    }
}
