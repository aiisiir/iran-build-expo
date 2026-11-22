<?php

namespace App\Http\Controllers\Public;

use App\Models\Company;
use App\Models\Product;
use App\Support\PublicRenderer;
use Illuminate\Contracts\View\View;

/** /{locale}/product/{id}; the product's company must be active and in the requested language. */
class ProductController
{
    public function __construct(private readonly PublicRenderer $renderer) {}

    public function show(string $locale, int $id): View
    {
        $product = Product::where('is_active', true)->findOrFail($id);
        $owner = Company::where('language_id', app('site.language')->id)->where('is_active', true)->findOrFail($product->company_id);

        return $this->renderer->product($product->setRelation('company', $owner));
    }
}
