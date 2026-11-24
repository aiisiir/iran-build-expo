<?php

namespace App\Providers;

use App\Models\{Company, Faq, Gallery, LinkGroup, Post};
use App\View\PublicLayoutComposer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Canonical, Open Graph and asset URLs must never depend on the request's Host header.
        if ($this->app->isProduction()) {
            URL::forceRootUrl((string) config('app.url'));
            URL::forceScheme('https');
        }

        // Stable names for polymorphic rows (page_placements, categorizables) instead of class names.
        Relation::morphMap(['post' => Post::class, 'company' => Company::class, 'faq' => Faq::class, 'gallery' => Gallery::class, 'link_group' => LinkGroup::class]);

        View::composer('layouts.app', PublicLayoutComposer::class);
        RateLimiter::for('public', fn (Request $request) => Limit::perMinute(120)->by($request->ip()));
        RateLimiter::for('badge', fn (Request $request) => Limit::perHour(5)->by($request->ip()));
    }
}
