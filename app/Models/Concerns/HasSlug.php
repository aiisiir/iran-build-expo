<?php

namespace App\Models\Concerns;

use App\Support\Slug;
use Illuminate\Database\Eloquent\Model;

/**
 * Generates/normalises `slug` and keeps it unique inside the scope given by slugScope().
 * Soft-deleted rows are included, so a deleted record keeps its slug reserved.
 */
trait HasSlug
{
    /** @return list<string> columns that, together with slug, must be unique */
    abstract protected function slugScope(): array;

    protected function slugSource(): string
    {
        return 'title';
    }

    protected static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            if ($model->exists && $model->slug !== '' && ! $model->isDirty('slug')) {
                return;
            }

            $base = Slug::make($model->slug ?: (string) $model->{$model->slugSource()});
            $slug = $base;

            for ($i = 2; $model->slugTaken($slug); $i++) {
                $slug = mb_substr($base, 0, 160 - strlen((string) $i) - 1).'-'.$i;
            }

            $model->slug = $slug;
        });
    }

    protected function slugTaken(string $slug): bool
    {
        $query = static::withTrashed()->where('slug', $slug);

        foreach ($this->slugScope() as $column) {
            $query->where($column, $this->{$column});
        }

        return $query->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))->exists();
    }
}
