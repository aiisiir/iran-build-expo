<?php

namespace App\Support;

use App\Models\Company;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/** Turns a content record into its public page: SEO data, language versions, breadcrumbs and the view. */
final class PublicRenderer
{
    public function __construct(private readonly ModuleLoader $modules) {}

    public function page(Page $page): View
    {
        $page->loadMissing('language', 'ogImage');
        $modules = $this->modules->load($page);
        $versions = $this->versions($page);
        $name = $this->siteName($page->language);
        $path = $page->path();
        $breadcrumbs = $this->breadcrumbs($page->language, $page->is_home ? [] : [['name' => $page->title, 'path' => $path]]);

        // The 3D tour is the only place an iframe is allowed; CSP frame-src is opened for that origin on this page only.
        if ($modules->contains(fn ($m) => $m->type->value === 'embed')) {
            request()->attributes->set('csp.frame_src', $this->tourOrigin());
        }

        return view('public.page', [
            'page' => $page,
            'modules' => $modules,
            'tourUrl' => config('services.tour3d.url'),
            'breadcrumbs' => $breadcrumbs,
            'translations' => $this->switcher($versions),
            'seo' => Seo::make(
                $page->language,
                $page->is_home ? ($page->meta_title ?: $name) : ($page->meta_title ?: $page->title.' | '.$name),
                $page->meta_description ?: ($page->body ?: SiteSettings::for($page->language_id)['meta_description'] ?? ''),
                $path, $versions, 'website', $page->noindex, $page->ogImage,
                $page->is_home
                    ? ['@type' => 'WebSite', 'name' => $name, 'url' => PublicUrl::absolute($path), 'inLanguage' => $page->language->htmlLang()]
                    : ['@type' => 'WebPage', 'name' => $page->title, 'url' => PublicUrl::absolute($path), 'inLanguage' => $page->language->htmlLang()],
                $breadcrumbs,
            ),
        ]);
    }

    public function post(Post $post): View
    {
        $post->loadMissing('language', 'page', 'image', 'category');
        $versions = $this->versions($post);
        $breadcrumbs = $this->breadcrumbs($post->language, [
            ['name' => $post->page->title, 'path' => $post->page->path()],
            ['name' => $post->title, 'path' => $post->path()],
        ]);

        return view('public.post', [
            'post' => $post,
            'breadcrumbs' => $breadcrumbs,
            'translations' => $this->switcher($versions),
            'seo' => Seo::make($post->language, ($post->meta_title ?: $post->title).' | '.$this->siteName($post->language),
                $post->meta_description ?: ($post->summary ?: $post->lead ?: $post->body), $post->path(), $versions, 'article', false, $post->image,
                array_filter([
                    '@type' => 'NewsArticle',
                    'headline' => $post->title,
                    'datePublished' => ($post->published_at ?? $post->created_at)?->toIso8601String(),
                    'dateModified' => $post->updated_at?->toIso8601String(),
                    'image' => $post->image?->url(),
                    'inLanguage' => $post->language->htmlLang(),
                    'mainEntityOfPage' => PublicUrl::absolute($post->path()),
                    'publisher' => ['@type' => 'Organization', 'name' => $this->siteName($post->language)],
                ]),
                $breadcrumbs),
        ]);
    }

    public function company(Company $company): View
    {
        $company->loadMissing('language', 'page', 'logo', 'products.images.media');
        $versions = $this->versions($company);
        $breadcrumbs = $this->breadcrumbs($company->language, [
            ['name' => $company->page->title, 'path' => $company->page->path()],
            ['name' => $company->name, 'path' => $company->path()],
        ]);

        return view('public.company', [
            'company' => $company,
            'breadcrumbs' => $breadcrumbs,
            'translations' => $this->switcher($versions),
            'seo' => Seo::make($company->language, ($company->meta_title ?: $company->name).' | '.$this->siteName($company->language),
                $company->meta_description ?: ($company->description ?: $company->industry), $company->path(), $versions, 'website', false, $company->logo,
                array_filter(['@type' => 'Organization', 'name' => $company->name, 'url' => PublicUrl::absolute($company->path()), 'logo' => $company->logo?->url()]),
                $breadcrumbs),
        ]);
    }

    public function product(Product $product): View
    {
        $product->loadMissing('company.language', 'company.page', 'images.media');
        $company = $product->company;
        $breadcrumbs = $this->breadcrumbs($company->language, [
            ['name' => $company->page->title, 'path' => $company->page->path()],
            ['name' => $company->name, 'path' => $company->path()],
            ['name' => $product->name, 'path' => $product->path()],
        ]);
        $versions = collect([['language' => $company->language, 'path' => $product->path()]]);
        $image = $product->images->first()?->media;

        return view('public.product', [
            'product' => $product,
            'company' => $company,
            'breadcrumbs' => $breadcrumbs,
            'translations' => [],
            'seo' => Seo::make($company->language, ($product->meta_title ?: $product->name).' | '.$company->name, $product->meta_description ?: $product->description,
                $product->path(), $versions, 'website', false, $image,
                array_filter(['@type' => 'Product', 'name' => $product->name, 'image' => $image?->url(), 'brand' => ['@type' => 'Brand', 'name' => $company->name]]),
                $breadcrumbs),
        ]);
    }

    /** @return Collection<int, array{language: Language, path: string}> this record plus its public translations */
    private function versions(Page|Post|Company $record): Collection
    {
        return collect([['language' => $record->language, 'path' => $record->path()]])
            ->merge($record->publicTranslations()->map(fn ($t) => ['language' => $t->language, 'path' => $t->path()]))
            ->filter(fn ($v) => $v['language']->is_active);
    }

    /** @return array<string, string> language code => relative URL for the language switcher */
    private function switcher(Collection $versions): array
    {
        return $versions->mapWithKeys(fn ($v) => [$v['language']->code => PublicUrl::path($v['path'])])->all();
    }

    private function breadcrumbs(Language $language, array $trail): array
    {
        return [['name' => SiteSettings::for($language->id)['site_name'] ?? config('app.name'), 'path' => "/{$language->code}"], ...$trail];
    }

    private function siteName(Language $language): string
    {
        return SiteSettings::for($language->id)['site_name'] ?? (string) config('app.name');
    }

    private function tourOrigin(): string
    {
        $parts = parse_url((string) config('services.tour3d.url'));

        return isset($parts['scheme'], $parts['host']) ? $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '') : "'none'";
    }
}
