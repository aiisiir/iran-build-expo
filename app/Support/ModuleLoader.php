<?php

namespace App\Support;

use App\Enums\ModuleType;
use App\Models\Company;
use App\Models\Faq;
use App\Models\Link;
use App\Models\Page;
use App\Models\PageModule;
use App\Models\Post;
use Illuminate\Support\Collection;

/**
 * Loads what each active module of a page shows. A record appears only when its language,
 * source page and (optional) category match the module and it is publicly visible right now.
 */
final class ModuleLoader
{
    /** @return Collection<int, PageModule> active modules with an `items` attribute */
    public function load(Page $page): Collection
    {
        return $page->modules()->where('is_active', true)->with(['media', 'gallery.items.media'])->get()
            ->each(fn (PageModule $m) => $m->setRelation('items', $this->items($m, $page)));
    }

    private function items(PageModule $module, Page $page): Collection
    {
        $limit = max(1, $module->item_limit);

        return match ($module->type) {
            ModuleType::Posts => Post::live()->where('language_id', $page->language_id)->where('page_id', $module->effectiveSourcePageId())
                ->when($module->category_id, fn ($q, $id) => $q->where('category_id', $id))
                ->with(['image', 'page', 'language'])->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->limit($limit)->get(),
            ModuleType::Companies => Company::where('language_id', $page->language_id)->where('page_id', $module->effectiveSourcePageId())->where('is_active', true)
                ->with(['logo', 'page', 'language'])->orderBy('sort_order')->orderBy('name')->limit($limit)->get(),
            ModuleType::Links => Link::where('link_group_id', $module->link_group_id)->whereNull('parent_id')->where('is_active', true)
                ->whereHas('group', fn ($q) => $q->where('language_id', $page->language_id)->where('is_active', true))
                ->with(['page.language', 'fileMedia', 'iconMedia'])->orderBy('sort_order')->get(),
            ModuleType::Faq => Faq::where('language_id', $page->language_id)->where('is_active', true)
                ->when($module->category_id, fn ($q, $id) => $q->where('category_id', $id))->orderBy('sort_order')->limit($limit)->get(),
            ModuleType::Gallery => ($module->gallery?->is_active ? $module->gallery->items : collect())->take($limit),
            default => collect(),
        };
    }
}
