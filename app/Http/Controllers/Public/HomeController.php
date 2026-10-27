<?php

namespace App\Http\Controllers\Public;

use Illuminate\Contracts\View\View;

class HomeController
{
    public function index(string $locale): View
    {
        $alternates = [
            'fa-IR' => route('public.home', ['locale' => 'fa']),
            'en' => route('public.home', ['locale' => 'en']),
        ];

        return view('pages.home', [
            'seo' => [
                'title' => __('seo.home_title'),
                'description' => __('seo.home_description'),
                'canonical' => route('public.home', ['locale' => $locale]),
                'alternates' => $alternates,
                'x_default' => route('public.home', ['locale' => 'fa']),
                'robots' => app()->environment('production') ? 'index,follow' : 'noindex,nofollow',
                'type' => 'website',
            ],
        ]);
    }
}
