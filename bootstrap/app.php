<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        // The login route is on the api php file. We need to exclude the sanctum CRSF checks for this route.
        $middleware->validateCsrfTokens([
            '*login*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
