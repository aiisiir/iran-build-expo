<?php

use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/fa', 301)->name('root');

Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');

Route::prefix('{locale}')
    ->where(['locale' => 'fa|en'])
    ->middleware('set-locale')
    ->group(function (): void {
        Route::get('/', [HomeController::class, 'index'])->name('public.home');
    });
