<?php

namespace App\Http\Controllers\Admin;

use App\Models\MediaAsset;
use App\Services\MediaUploader;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaController
{
    public function index(): View
    {
        return view('admin.media.index', ['assets' => MediaAsset::with('language')->latest('id')->paginate(24)]);
    }

    public function store(Request $request, MediaUploader $uploader): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'language_id' => ['nullable', 'exists:languages,id'],
            'alt' => ['nullable', 'string', 'max:191'],
            'title' => ['nullable', 'string', 'max:191'],
        ]);

        $uploader->store($request->file('file'), $data['language_id'] ?? null, $data['alt'] ?? null, $data['title'] ?? null);

        return redirect()->route('admin.media.index')->with('status', 'فایل بارگذاری شد.');
    }

    public function edit(int $id): View
    {
        return view('admin.media.edit', ['asset' => MediaAsset::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        MediaAsset::findOrFail($id)->update($request->validate([
            'language_id' => ['nullable', 'exists:languages,id'],
            'alt' => ['nullable', 'string', 'max:191'],
            'title' => ['nullable', 'string', 'max:191'],
        ]));

        return redirect()->route('admin.media.index')->with('status', 'ذخیره شد.');
    }

    public function destroy(int $id): RedirectResponse
    {
        MediaAsset::findOrFail($id)->delete(); // soft delete; the file stays on disk

        return redirect()->route('admin.media.index')->with('status', 'حذف شد (در دیتابیس باقی می‌ماند).');
    }
}
