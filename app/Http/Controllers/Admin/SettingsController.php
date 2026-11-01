<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController
{
    public function edit(): View
    {
        $languages = Language::active();

        return view('admin.settings.edit', [
            'languages' => $languages,
            'values' => $languages->mapWithKeys(fn ($l) => [$l->id => SiteSettings::for($l->id)]),
            'keys' => SiteSettings::KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['settings.*.*' => ['nullable', 'string', 'max:2000']]);

        foreach (Language::active() as $language) {
            SiteSettings::save($language->id, (array) $request->input("settings.{$language->id}", []));
        }

        return back()->with('status', 'ذخیره شد.');
    }
}
