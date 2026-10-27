<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind project services here as modules are implemented.
    }

    public function boot(): void
    {
        // Shared view data and explicit policies will be added in subsequent slices.
    }
}
