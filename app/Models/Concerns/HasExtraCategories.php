<?php

namespace App\Models\Concerns;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/** "Also in these categories", next to the main `category_id`. */
trait HasExtraCategories
{
    public function extraCategories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function scopeInCategory(Builder $query, int $categoryId): void
    {
        $query->where(fn (Builder $q) => $q->where($this->getTable().'.category_id', $categoryId)
            ->orWhereHas('extraCategories', fn ($c) => $c->whereKey($categoryId)));
    }
}
