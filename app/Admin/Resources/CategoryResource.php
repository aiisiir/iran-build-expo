<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class CategoryResource extends Resource
{
    public function model(): string
    {
        return Category::class;
    }

    public function key(): string
    {
        return 'categories';
    }

    public function title(): string
    {
        return 'دسته‌بندی‌ها';
    }

    public function with(): array
    {
        return ['language'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::select('type', 'نوع', Options::categoryTypes(), ['required', 'in:post,faq']),
            Field::text('name', 'نام', ['required', 'string', 'max:120']),
            Field::text('slug', 'نشانی (slug)', ['nullable', 'string', 'max:120'], 'خالی = خودکار از نام'),
            Field::text('translation_key', 'کلید ترجمه', ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'], 'اختیاری؛ در دو زبان یکسان بنویسید تا معادل هم شناخته شوند.'),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['نام' => 'name', 'زبان' => 'language.code', 'نوع' => 'type', 'slug' => 'slug', 'فعال' => 'is_active'];
    }
}
