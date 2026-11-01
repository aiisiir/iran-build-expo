<?php

namespace App\Models;

use App\Enums\LinkPlacement;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'placement', 'title', 'show_title', 'is_active', 'sort_order'])]
class LinkGroup extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['placement' => LinkPlacement::class, 'show_title' => 'boolean', 'is_active' => 'boolean'];
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class)->orderBy('sort_order');
    }
}
