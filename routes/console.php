<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('about:expo', function () {
    $this->comment('Iran Build Expo — Laravel + Blade pilot skeleton');
})->purpose('Show a short project marker for the initial scaffold');
