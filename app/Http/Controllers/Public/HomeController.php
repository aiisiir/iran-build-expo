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
        $canonical = route('public.home', ['locale' => $locale]);

        return view('pages.home', [
            'seo' => [
                'title' => __('seo.home_title'),
                'description' => __('seo.home_description'),
                'canonical' => $canonical,
                'alternates' => $alternates,
                'x_default' => route('public.home', ['locale' => 'fa']),
                'robots' => app()->environment('production') ? 'index,follow' : 'noindex,nofollow',
                'type' => 'website',
                'jsonld' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => config('app.name'),
                    'url' => $canonical,
                    'inLanguage' => $locale === 'fa' ? 'fa-IR' : 'en',
                ],
            ],
        ]);
    }
}
