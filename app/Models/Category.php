<?php

namespace App\Models;

use App\Enums\CategoryType;
use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'translation_key', 'type', 'name', 'slug', 'sort_order', 'is_active'])]
class Category extends Model
{
    use Audited, HasSlug, HasTranslations, SoftDeletes;

    protected function casts(): array
    {
        return ['type' => CategoryType::class, 'is_active' => 'boolean'];
    }

    protected function slugScope(): array
    {
        return ['language_id', 'type'];
    }

    protected function slugSource(): string
    {
        return 'name';
    }
}
