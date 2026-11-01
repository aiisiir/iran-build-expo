<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Enums\MediaKind;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Model;

class ProductImageResource extends Resource
{
    public function model(): string
    {
        return ProductImage::class;
    }

    public function key(): string
    {
        return 'product-images';
    }

    public function title(): string
    {
        return 'تصاویر محصول';
    }

    public function with(): array
    {
        return ['media'];
    }

    public function filters(): array
    {
        return ['product_id'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('product_id', 'محصول', fn () => Options::products(), ['required', 'exists:products,id']),
            Field::select('media_id', 'تصویر', fn () => Options::media(MediaKind::Image), ['required', 'exists:media_assets,id']),
            Field::number('sort_order', 'ترتیب'),
        ];
    }

    public function columns(): array
    {
        return ['تصویر' => 'media.original_name', 'ترتیب' => 'sort_order'];
    }
}
