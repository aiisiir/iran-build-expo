<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;

class SeoController
{
    public function robots(): Response
    {
        if (! app()->environment('production')) {
            return response("User-agent: *\nDisallow: /\n", 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        }

        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /login',
            'Disallow: /register',
            'Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml',
            '',
        ]);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response|View
    {
        if (! app()->environment('production')) {
            return response('', 404)->header('X-Robots-Tag', 'noindex, nofollow');
        }

        $homeAlternates = [
            'fa-IR' => route('public.home', ['locale' => 'fa']),
            'en' => route('public.home', ['locale' => 'en']),
            'x-default' => route('public.home', ['locale' => 'fa']),
        ];

        return response()->view('seo.sitemap', [
            'entries' => [[
                'urls' => array_values(array_unique($homeAlternates)),
                'alternates' => $homeAlternates,
            ]],
        ], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
