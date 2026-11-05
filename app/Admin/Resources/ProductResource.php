<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    public function model(): string
    {
        return Product::class;
    }

    public function key(): string
    {
        return 'products';
    }

    public function title(): string
    {
        return 'محصولات';
    }

    public function with(): array
    {
        return ['company'];
    }

    public function filters(): array
    {
        return ['company_id'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('company_id', 'شرکت', fn () => Options::companies(), ['required', 'exists:companies,id']),
            Field::text('name', 'نام محصول', ['required', 'string', 'max:191']),
            Field::text('slug', 'نشانی (slug)', ['nullable', 'string', 'max:160'], 'خالی = خودکار از نام'),
            Field::text('meta_title', 'عنوان سئو', ['nullable', 'string', 'max:191']),
            Field::text('meta_description', 'توضیح سئو', ['nullable', 'string', 'max:300']),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
            Field::rich('description', 'توضیحات'),
        ];
    }

    public function columns(): array
    {
        return ['نام' => 'name', 'شرکت' => 'company.name', 'فعال' => 'is_active'];
    }

    public function children(Model $model): array
    {
        return ['تصاویر محصول' => route('admin.resource.index', ['resource' => 'product-images', 'product_id' => $model->id])];
    }
}
