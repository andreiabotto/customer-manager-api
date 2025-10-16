<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \App\Http\Middleware\PreventRedirects::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'sanctum/csrf-cookie'
        ]);

        $middleware->alias([
            'throttle.api' => \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            'swagger.bypass_csrf' => \App\Http\Middleware\BypassCsrfForSwagger::class,
        ]);
    })
    ->withProviders([
        L5Swagger\L5SwaggerServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
