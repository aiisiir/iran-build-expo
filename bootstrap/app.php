<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\PreventIndexingNonProduction;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        then: fn () => Route::middleware('web')->prefix('admin')->name('admin.')->group(base_path('routes/admin.php')),
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['set-locale' => SetLocale::class, 'admin-only' => EnsureAdmin::class]);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        // Global (not the "web" group) so that 404 responses carry these headers too.
        $middleware->append([SecurityHeaders::class, PreventIndexingNonProduction::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
