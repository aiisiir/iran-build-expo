<?php

namespace App\Providers;

use App\View\PublicLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services are resolved by type; nothing to bind.
    }

    public function boot(): void
    {
        View::composer('layouts.app', PublicLayoutComposer::class);
        RateLimiter::for('public', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('badge', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
    }
}
