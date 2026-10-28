<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind project services here as modules are implemented.
    }

    public function boot(): void
    {
        RateLimiter::for('public', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        // Used by the login route once authentication is built.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(strtolower((string) $request->input('username')).'|'.$request->ip()));
    }
}
