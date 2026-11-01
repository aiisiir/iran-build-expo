<?php

namespace App\View;

use App\Models\Language;
use App\Models\Link;
use App\Models\LinkGroup;
use App\Support\SiteSettings;
use Illuminate\View\View;

/** Feeds the shared header/footer of every public page. */
class PublicLayoutComposer
{
    public function compose(View $view): void
    {
        /** @var Language $language */
        $language = app('site.language');

        $groups = LinkGroup::where('language_id', $language->id)->where('is_active', true)
            ->whereIn('placement', ['menu', 'logo', 'footer'])->orderBy('sort_order')->get();

        $links = Link::whereIn('link_group_id', $groups->modelKeys())->where('is_active', true)
            ->with(['page.language', 'fileMedia', 'iconMedia'])->orderBy('sort_order')->get()->groupBy('link_group_id');

        $switch = $view->getData()['translations'] ?? [];

        $view->with([
            'language' => $language,
            'site' => SiteSettings::for($language->id),
            'menuLinks' => $this->tree($links, $groups->firstWhere('placement.value', 'menu')),
            'logoLink' => $links->get($groups->firstWhere('placement.value', 'logo')?->id, collect())->first(),
            'footerGroups' => $groups->filter(fn ($g) => $g->placement->value === 'footer')
                ->map(fn ($g) => ['group' => $g, 'links' => $this->tree($links, $g)]),
            'switcher' => Language::active()->except($language->code)
                ->map(fn (Language $l) => ['language' => $l, 'url' => $switch[$l->code] ?? url('/'.$l->code)]),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int|string, \Illuminate\Support\Collection<int, Link>> links grouped by parent id (null = roots) */
    private function tree($links, ?LinkGroup $group)
    {
        return $group ? $links->get($group->id, collect())->groupBy(fn (Link $l) => $l->parent_id ?? 0) : collect();
    }
}
