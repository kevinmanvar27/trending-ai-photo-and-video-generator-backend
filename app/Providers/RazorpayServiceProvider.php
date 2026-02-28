<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class RazorpayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('razorpay.config', function ($app) {
            return [
                'key' => Setting::get('razorpay_key', ''),
                'secret' => Setting::get('razorpay_secret', ''),
                'enabled' => Setting::get('razorpay_enabled', '0') === '1',
            ];
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
