<?php

namespace App\Http\Controllers\Public;

use App\Models\Badge;
use App\Models\BadgeType;
use App\Models\Edition;
use App\Services\PrivatePhotos;
use App\Support\BadgeRules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Public badge request form. Nothing is downloadable: the person collects the printed card at the support desk. */
class BadgeRegistrationController
{
    public function create(string $locale): View
    {
        return view('public.badge', [
            'edition' => Edition::current(),
            'types' => BadgeType::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'),
            'seo' => ['title' => __('badge.title'), 'description' => __('badge.intro'), 'canonical' => url("/{$locale}/badge"), 'robots' => 'noindex,nofollow', 'alternates' => [], 'type' => 'website'],
        ]);
    }

    public function store(Request $request, string $locale, PrivatePhotos $photos): RedirectResponse
    {
        $edition = Edition::current();
        abort_if($edition === null, 404);

        BadgeRules::normalize($request);
        $data = $request->validate([
            ...BadgeRules::base(),
            'photo' => ['required', 'file', 'max:2048'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'], // honeypot: real people never fill this hidden field
        ]);

        Badge::create([
            ...collect($data)->except(['photo', 'consent', 'website'])->all(),
            'edition_id' => $edition->id,
            'photo_path' => $photos->store($request->file('photo')),
        ]);

        return redirect()->route('public.badge', ['locale' => $locale])->with('sent', true);
    }
}
