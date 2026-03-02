<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'track.activity' => \App\Http\Middleware\TrackUserActivity::class,
            'check.suspended' => \App\Http\Middleware\CheckUserSuspended::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\TrackUserActivity::class,
            \App\Http\Middleware\CheckUserSuspended::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Close stale activity sessions daily
        $schedule->command('activity:close-stale --hours=24')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
