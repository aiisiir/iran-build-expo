<?php

namespace App\Support;

use App\Enums\ModuleType;
use App\Models\Company;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Link;
use App\Models\LinkGroup;
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
            ModuleType::Posts => Post::live()->where('language_id', $page->language_id)->onPage($module->effectiveSourcePageId())
                ->when($module->category_id, fn ($q, $id) => $q->inCategory($id))
                ->with(['image', 'page', 'language', 'category'])->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')->limit($limit)->get(),
            ModuleType::Companies => Company::where('language_id', $page->language_id)->onPage($module->effectiveSourcePageId())->where('is_active', true)
                ->with(['logo', 'page', 'language'])->orderBy('sort_order')->orderBy('name')->limit($limit)->get(),
            // A module without its own group/gallery shows the ones placed on this page.
            ModuleType::Links, ModuleType::About => Link::whereIn('link_group_id', $module->link_group_id ? [$module->link_group_id] : LinkGroup::onPage($page->id)->pluck('id'))->whereNull('parent_id')->where('is_active', true)
                ->whereHas('group', fn ($q) => $q->where('language_id', $page->language_id)->where('is_active', true))
                ->with(['page.language', 'fileMedia', 'iconMedia'])->orderBy('sort_order')->get(),
            // A FAQ with no placement is shown everywhere; once placed, only on those pages.
            ModuleType::Faq => Faq::where('language_id', $page->language_id)->where('is_active', true)
                ->where(fn ($q) => $q->onPage($page->id)->orDoesntHave('placedPages'))
                ->when($module->category_id, fn ($q, $id) => $q->inCategory($id))->orderBy('sort_order')->limit($limit)->get(),
            ModuleType::Gallery => ($module->gallery_id
                ? collect($module->gallery?->is_active ? [$module->gallery] : [])
                : Gallery::where('is_active', true)->where('language_id', $page->language_id)->onPage($page->id)->orderBy('sort_order')->with('items.media')->get()
            )->flatMap->items->take($limit)->values(),
            default => collect(),
        };
    }
}
