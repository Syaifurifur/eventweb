<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (! defined('FCPATH')) {
            define('FCPATH', public_path() . DIRECTORY_SEPARATOR);
        }
        if (! defined('WRITEPATH')) {
            define('WRITEPATH', storage_path() . DIRECTORY_SEPARATOR);
        }
        if (! is_dir(storage_path('cache'))) {
            @mkdir(storage_path('cache'), 0775, true);
        }
        if (! defined('ENVIRONMENT')) {
            define('ENVIRONMENT', app()->environment());
        }
        if (str_starts_with((string) config('app.url'), 'https://') || app()->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
