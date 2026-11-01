<?php

use App\Http\Controllers\Public\BadgeRegistrationController;
use App\Http\Controllers\Public\ItemController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\SeoController;
use App\Models\Language;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', fn () => redirect('/'.(Language::default()?->code ?? 'fa'), 301))->name('root');

// Public pages send no cookies and start no session: faster, cache-friendly, and no session files for bots.
$stateless = [StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class, AddQueuedCookiesToResponse::class, EncryptCookies::class];

Route::middleware('throttle:public')->group(function () use ($stateless): void {
    Route::withoutMiddleware($stateless)->group(function (): void {
        Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
        Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
    });

    Route::prefix('{locale}')->where(['locale' => '[a-z]{2,3}'])->middleware('set-locale')->group(function () use ($stateless): void {
        // Badge form needs a session (CSRF); it is declared before the {page} wildcard on purpose.
        Route::get('badge', [BadgeRegistrationController::class, 'create'])->name('public.badge');
        Route::post('badge', [BadgeRegistrationController::class, 'store'])->middleware('throttle:badge')->name('public.badge.store');

        Route::withoutMiddleware($stateless)->group(function (): void {
            Route::get('/', [PageController::class, 'home'])->name('public.home');
            Route::get('{page}', [PageController::class, 'show'])->name('public.page');
            Route::get('{page}/{item}', [ItemController::class, 'show'])->name('public.item');
            Route::get('{page}/{company}/{product}', [ProductController::class, 'show'])->name('public.product');
        });
    });
});
