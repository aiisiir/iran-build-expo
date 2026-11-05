<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Model;

class FaqResource extends Resource
{
    public function model(): string
    {
        return Faq::class;
    }

    public function key(): string
    {
        return 'faqs';
    }

    public function title(): string
    {
        return 'پرسش‌های متداول';
    }

    public function with(): array
    {
        return ['language'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::select('category_id', 'دسته', fn () => Options::categories(CategoryType::Faq), ['nullable', 'exists:categories,id']),
            Field::text('question', 'پرسش', ['required', 'string', 'max:300']),
            Field::rich('answer', 'پاسخ', ['required', 'string', 'max:50000']),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['پرسش' => 'question', 'زبان' => 'language.code', 'ترتیب' => 'sort_order', 'فعال' => 'is_active'];
    }

    public function check(array $data, ?Model $model): void
    {
        $this->requireSameLanguage((int) $data['language_id'], $data, ['category_id' => Category::class]);
    }
}
