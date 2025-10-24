<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TenantDatabaseMiddleware::class,
            \App\Http\Middleware\SetTenantLocale::class,
        ]);

        $middleware->alias([
            'tenant.database' => \App\Http\Middleware\TenantDatabaseMiddleware::class,
            'tenant.url' => \App\Http\Middleware\TenantUrlMiddleware::class,
            'auth:tenant' => \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
