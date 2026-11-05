<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\Gallery;
use Illuminate\Database\Eloquent\Model;

class GalleryResource extends Resource
{
    public function model(): string
    {
        return Gallery::class;
    }

    public function key(): string
    {
        return 'galleries';
    }

    public function title(): string
    {
        return 'گالری‌ها';
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::select('edition_id', 'دوره', fn () => Options::editions(), ['nullable', 'exists:editions,id']),
            Field::text('title', 'عنوان', ['required', 'string', 'max:191']),
            Field::textarea('description', 'توضیح'),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['عنوان' => 'title', 'ترتیب' => 'sort_order', 'فعال' => 'is_active'];
    }

    public function children(Model $model): array
    {
        return ['تصاویر گالری' => route('admin.resource.index', ['resource' => 'gallery-items', 'gallery_id' => $model->id])];
    }
}
