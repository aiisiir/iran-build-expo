<?php

use App\Http\Middleware\PreventIndexingNonProduction;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'set-locale' => SetLocale::class,
        ]);
        $middleware->appendToGroup('web', PreventIndexingNonProduction::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Exception reporting and rendering policies will be configured with the production threat model.
    })
    ->create();
