<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Registry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** One list of every deleted record (all types together) with its type, so it can be restored. */
class TrashController
{
    private const LIMIT = 200;

    public function __invoke(Request $request): View
    {
        $isAdmin = $request->user()->isAdmin();

        $rows = collect(Registry::groups())->flatten()
            ->filter(fn ($r) => $r->hasTrash() && ! $r->readOnly && ($isAdmin || ! $r->adminOnly))
            ->flatMap(fn ($r) => $r->model()::onlyTrashed()->with($r->with())->latest('deleted_at')->limit(self::LIMIT)->get()
                ->map(fn ($m) => ['resource' => $r, 'model' => $m, 'title' => is_string($path = array_values($r->columns())[0]) ? data_get($m, $path) : null]))
            ->sortByDesc(fn ($row) => $row['model']->deleted_at)->values();

        return view('admin.trash', ['rows' => $rows->take(self::LIMIT), 'more' => $rows->count() > self::LIMIT]);
    }
}
