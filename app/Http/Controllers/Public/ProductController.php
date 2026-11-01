<?php

namespace App\Http\Controllers\Public;

use App\Models\Company;
use App\Models\Page;
use App\Models\Product;
use App\Support\PublicRenderer;
use Illuminate\Contracts\View\View;

class ProductController
{
    public function __construct(private readonly PublicRenderer $renderer) {}

    public function show(string $locale, string $page, string $company, string $product): View
    {
        $language = app('site.language');
        $parent = Page::published()->where('language_id', $language->id)->where('slug', $page)->firstOrFail();
        $owner = Company::where('language_id', $language->id)->where('page_id', $parent->id)->where('slug', $company)->where('is_active', true)->firstOrFail();
        $item = Product::where('company_id', $owner->id)->where('slug', $product)->where('is_active', true)->firstOrFail();

        return $this->renderer->product($item->setRelation('company', $owner->setRelation('page', $parent)));
    }
}
