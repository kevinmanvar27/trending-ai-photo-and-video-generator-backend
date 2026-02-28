<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\ViewComposers\SettingsComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share settings with frontend views
        View::composer([
            'image-submission.index',
            'image-submission.create',
            'image-submission.show',
            'welcome'
        ], SettingsComposer::class);
    }
}
