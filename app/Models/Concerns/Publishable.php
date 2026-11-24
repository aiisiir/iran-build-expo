<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** Time-based visibility is evaluated at query time (the host has no cron). */
trait Publishable
{
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeNotExpired(Builder $query): void
    {
        $query->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
