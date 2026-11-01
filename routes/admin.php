<?php

use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

// Loaded with the "web" group and the /admin prefix (see bootstrap/app.php).
Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:30,1');
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('media')->name('media.')->controller(MediaController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
        Route::put('{id}', 'update')->whereNumber('id')->name('update');
        Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
    });

    Route::prefix('badges')->name('badges.')->controller(BadgeController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('print', 'print')->name('print');
        Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
        Route::put('{id}', 'update')->whereNumber('id')->name('update');
        Route::get('{id}/photo', 'photo')->whereNumber('id')->name('photo');
        Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
    });

    Route::middleware('admin-only')->prefix('settings')->name('settings.')->controller(SettingsController::class)->group(function (): void {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });

    Route::prefix('r/{resource}')->name('resource.')->controller(ResourceController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{id}/edit', 'edit')->whereNumber('id')->name('edit');
        Route::put('{id}', 'update')->whereNumber('id')->name('update');
        Route::delete('{id}', 'destroy')->whereNumber('id')->name('destroy');
    });
});
