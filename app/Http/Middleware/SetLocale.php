<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/** The first URL segment must be an active language (read from the database); anything else is a 404. */
class SetLocale
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $language = Language::active()->get((string) $request->route('locale'));

        abort_if($language === null, 404);

        App::setLocale($language->code);
        URL::defaults(['locale' => $language->code]);
        app()->instance('site.language', $language);
        $request->attributes->set('active_locale', $language->code);

        return $next($request);
    }
}
