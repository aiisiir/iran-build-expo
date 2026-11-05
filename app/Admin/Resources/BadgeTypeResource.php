<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Resource;
use App\Models\BadgeType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class BadgeTypeResource extends Resource
{
    public bool $adminOnly = true;

    public function model(): string
    {
        return BadgeType::class;
    }

    public function key(): string
    {
        return 'badge-types';
    }

    public function title(): string
    {
        return 'سمت‌های کارت سینه';
    }

    public function fields(?Model $model): array
    {
        return [
            Field::text('name', 'سمت', ['required', 'string', 'max:60', Rule::unique('badge_types', 'name')->ignore($model?->id)]),
            Field::color('color', 'رنگ کارت'),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['سمت' => 'name', 'رنگ' => 'color', 'ترتیب' => 'sort_order', 'فعال' => 'is_active'];
    }
}
