<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale');

        abort_unless(in_array($locale, ['fa', 'en'], true), 404);

        App::setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->attributes->set('active_locale', $locale);

        return $next($request);
    }
}
