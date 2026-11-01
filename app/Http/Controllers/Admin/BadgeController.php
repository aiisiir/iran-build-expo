<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Options;
use App\Models\AuditEvent;
use App\Models\Badge;
use App\Models\BadgeType;
use App\Services\PrivatePhotos;
use App\Support\BadgeRules;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BadgeController
{
    public function index(Request $request): View
    {
        $badges = Badge::with(['type', 'edition'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('full_name', 'like', '%'.$request->string('q').'%'))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.badges.index', ['badges' => $badges, 'statuses' => Options::badgeStatuses()]);
    }

    public function edit(int $id): View
    {
        $badge = Badge::findOrFail($id);
        AuditEvent::log('viewed', $badge); // personal data: reads are logged too

        return view('admin.badges.edit', [
            'badge' => $badge,
            'types' => BadgeType::where('is_active', true)->orderBy('sort_order')->pluck('name', 'id'),
            'statuses' => Options::badgeStatuses(),
        ]);
    }

    public function update(Request $request, int $id, PrivatePhotos $photos): RedirectResponse
    {
        $badge = Badge::findOrFail($id);
        BadgeRules::normalize($request);
        $data = $request->validate([...BadgeRules::base(), 'status' => ['required', 'in:pending,approved,rejected'], 'photo' => ['nullable', 'file', 'max:2048']]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $photos->store($request->file('photo'));
        }

        if ($data['status'] !== $badge->status->value) {
            $data += ['reviewed_by' => auth()->id(), 'reviewed_at' => now()];
        }

        $badge->forceFill(collect($data)->except('photo')->all())->save();

        return redirect()->route('admin.badges.index')->with('status', 'ذخیره شد.');
    }

    public function photo(int $id)
    {
        $badge = Badge::findOrFail($id);
        AuditEvent::log('photo_viewed', $badge);

        return Storage::disk('local')->response($badge->photo_path, null, ['Cache-Control' => 'private, no-store']);
    }

    public function print(Request $request): View
    {
        $ids = (array) $request->validate(['ids' => ['required', 'array', 'max:100'], 'ids.*' => ['integer']])['ids'];
        $badges = Badge::with(['type', 'edition'])->whereIn('id', $ids)->where('status', 'approved')->get();

        Badge::whereIn('id', $badges->modelKeys())->update(['printed_at' => now(), 'print_count' => DB::raw('print_count + 1')]);
        AuditEvent::log('printed', null, $badges->modelKeys());

        return view('admin.badges.print', ['badges' => $badges]);
    }

    public function destroy(int $id): RedirectResponse
    {
        Badge::findOrFail($id)->delete();

        return redirect()->route('admin.badges.index')->with('status', 'حذف شد (در دیتابیس باقی می‌ماند).');
    }
}
