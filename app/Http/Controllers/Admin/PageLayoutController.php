<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModuleType;
use App\Models\Page;
use App\Models\PageModule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Visual editor of a page's blocks: drag to reorder, switch on/off, title on/off, item count, jump to the block's records. */
class PageLayoutController
{
    /** Block types whose "item count" has an effect (see ModuleLoader). */
    private const LIMITED = [ModuleType::Posts, ModuleType::Companies, ModuleType::Faq, ModuleType::Gallery];

    public function edit(int $id): View
    {
        $page = Page::with('language')->findOrFail($id);
        $modules = $page->modules()->with('category')->get();

        return view('admin.pages.layout', [
            'page' => $page,
            'modules' => $modules,
            'recordUrls' => $modules->mapWithKeys(fn (PageModule $m) => [$m->id => $this->recordsUrl($m)]),
            'limited' => self::LIMITED,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $page = Page::findOrFail($id);
        $modules = $page->modules()->get()->keyBy('id');

        $data = $request->validate([
            'order' => ['required', 'array', 'max:100'],
            'order.*' => ['integer', 'distinct'],
            'active' => ['nullable', 'array'],
            'titles' => ['nullable', 'array'],
            'limit' => ['nullable', 'array'],
            'limit.*' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        DB::transaction(function () use ($data, $modules): void {
            // Only blocks that belong to this page are touched; unknown ids are ignored.
            foreach (array_values($data['order']) as $position => $moduleId) {
                $module = $modules->get((int) $moduleId);

                $module?->forceFill([
                    'sort_order' => $position + 1,
                    'is_active' => isset($data['active'][$moduleId]),
                    'show_title' => isset($data['titles'][$moduleId]),
                    'item_limit' => in_array($module->type, self::LIMITED, true) ? ($data['limit'][$moduleId] ?? $module->item_limit) : $module->item_limit,
                ])->save();
            }
        });

        return redirect()->route('admin.pages.layout', $page->id)->with('status', 'چینش ذخیره شد.');
    }

    /** Where the records shown by this block are managed (null for blocks that only hold their own content). */
    private function recordsUrl(PageModule $m): ?string
    {
        [$resource, $query] = match ($m->type) {
            ModuleType::Posts => ['posts', array_filter(['page_id' => $m->effectiveSourcePageId(), 'category_id' => $m->category_id])],
            ModuleType::Companies => ['companies', []],
            ModuleType::Links => $m->link_group_id ? ['links', ['link_group_id' => $m->link_group_id]] : ['link-groups', []],
            ModuleType::Faq => ['faqs', []],
            ModuleType::Gallery => $m->gallery_id ? ['gallery-items', ['gallery_id' => $m->gallery_id]] : ['galleries', []],
            default => [null, []],
        };

        return $resource ? route('admin.resource.index', ['resource' => $resource, ...$query]) : null;
    }
}
