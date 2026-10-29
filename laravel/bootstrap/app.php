<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticateCrm;
use App\Http\Middleware\AuthenticateLegacy;
use App\Http\Middleware\RequireAccess;
use App\Http\Middleware\RequireRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'q/*',
            'kuesioner/*',
        ]);

        $middleware->alias([
            'legacy.auth' => AuthenticateLegacy::class,
            'crm.auth' => AuthenticateCrm::class,
            'access' => RequireAccess::class,
            'role' => RequireRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
