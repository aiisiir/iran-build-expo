<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaKind;
use App\Models\MediaAsset;
use App\Services\MediaUploader;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    /** JSON for the picker popup: newest first, filtered by kind and a search word. */
    public function picker(Request $request): JsonResponse
    {
        $word = addcslashes(trim((string) $request->query('q')), '%_\\');
        $page = MediaAsset::query()
            ->when($request->query('kind') === 'image', fn ($q) => $q->where('kind', MediaKind::Image))
            ->when($word !== '', fn ($q) => $q->where(fn ($w) => $w->where('original_name', 'like', "%{$word}%")->orWhere('alt', 'like', "%{$word}%")->orWhere('title', 'like', "%{$word}%")))
            ->latest('id')->paginate(18);

        return response()->json(['data' => $page->map(fn (MediaAsset $a) => $this->payload($a))->all(), 'next' => $page->hasMorePages()]);
    }

    /** Upload from inside a record's form; the file is flagged as belonging to that record's form (see MediaAsset::deleteIfOrphan). */
    public function quick(Request $request, MediaUploader $uploader): JsonResponse
    {
        $data = $request->validate(['file' => ['required', 'file', 'max:20480'], 'alt' => ['nullable', 'string', 'max:191']]);
        $asset = $uploader->store($request->file('file'), null, $data['alt'] ?? null, null);
        $asset->forceFill(['is_inline' => true])->save();

        return response()->json($this->payload($asset), 201);
    }

    private function payload(MediaAsset $a): array
    {
        return ['id' => $a->id, 'url' => $a->url(), 'name' => $a->original_name, 'alt' => $a->alt, 'image' => $a->kind === MediaKind::Image];
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

        return redirect()->route('admin.media.index')->with('status', 'حذف شد.');
    }
}
