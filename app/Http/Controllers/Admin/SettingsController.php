<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;
use App\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController
{
    public function edit(Request $request): View
    {
        $languages = Language::active();
        $current = $languages->get((string) $request->query('lang')) ?? Language::default();

        return view('admin.settings.edit', [
            'languages' => $languages,
            'current' => $current,
            'values' => SiteSettings::for($current->id),
            'keys' => SiteSettings::KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'settings.*.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $language = Language::active()->firstWhere('id', (int) $request->input('language_id')) ?? abort(422);
        SiteSettings::save($language->id, (array) $request->input("settings.{$language->id}", []));

        return redirect()->route('admin.settings.edit', ['lang' => $language->code])->with('status', 'ذخیره شد.');
    }
}
