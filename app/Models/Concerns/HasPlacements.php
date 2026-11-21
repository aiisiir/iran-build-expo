<?php

namespace App\Models\Concerns;

use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * "Also shown in these pages". Models that have their own `page_id` keep it as the main page
 * (its URL is the canonical one); every other model is shown only where it is placed.
 */
trait HasPlacements
{
    public function placedPages(): MorphToMany
    {
        return $this->morphToMany(Page::class, 'placeable', 'page_placements');
    }

    /** Records whose main page or any extra page is $pageId. */
    public function scopeOnPage(Builder $query, int $pageId): void
    {
        $query->where(function (Builder $q) use ($pageId): void {
            $q->whereHas('placedPages', fn ($p) => $p->whereKey($pageId));

            if ($this->hasMainPage()) {
                $q->orWhere($this->getTable().'.page_id', $pageId);
            }
        });
    }

    protected function hasMainPage(): bool
    {
        return in_array('page_id', $this->getFillable(), true);
    }
}
