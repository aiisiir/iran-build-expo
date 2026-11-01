<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\LinkGroup;
use Illuminate\Database\Eloquent\Model;

class LinkGroupResource extends Resource
{
    public function model(): string
    {
        return LinkGroup::class;
    }

    public function key(): string
    {
        return 'link-groups';
    }

    public function title(): string
    {
        return 'گروه‌های لینک';
    }

    public function with(): array
    {
        return ['language'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::select('placement', 'جایگاه', Options::placements(), ['required', 'in:'.implode(',', array_keys(Options::placements()))], null, 'منوی اصلی، لوگو و فوتر در هدر/فوتر سایت می‌آیند؛ بقیه را با بلوک «لینک‌ها» در صفحه بگذارید.'),
            Field::text('title', 'عنوان', ['nullable', 'string', 'max:191']),
            Field::checkbox('show_title', 'نمایش عنوان'),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['عنوان' => 'title', 'زبان' => 'language.code', 'جایگاه' => 'placement', 'فعال' => 'is_active'];
    }

    public function children(Model $model): array
    {
        return ['لینک‌های این گروه' => route('admin.resource.index', ['resource' => 'links', 'link_group_id' => $model->id])];
    }
}
