<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Enums\MediaKind;
use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Model;

class GalleryItemResource extends Resource
{
    public function model(): string
    {
        return GalleryItem::class;
    }

    public function key(): string
    {
        return 'gallery-items';
    }

    public function title(): string
    {
        return 'تصاویر گالری';
    }

    public function with(): array
    {
        return ['media'];
    }

    public function filters(): array
    {
        return ['gallery_id'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('gallery_id', 'گالری', fn () => Options::galleries(), ['required', 'exists:galleries,id']),
            Field::select('media_id', 'تصویر', fn () => Options::media(MediaKind::Image), ['required', 'exists:media_assets,id']),
            Field::text('caption', 'عنوان تصویر', ['nullable', 'string', 'max:191']),
            Field::number('sort_order', 'ترتیب'),
        ];
    }

    public function columns(): array
    {
        return ['تصویر' => 'media.original_name', 'عنوان' => 'caption', 'ترتیب' => 'sort_order'];
    }
}
