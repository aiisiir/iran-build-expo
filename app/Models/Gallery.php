<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\HasPlacements;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'edition_id', 'title', 'description', 'sort_order', 'is_active'])]
class Gallery extends Model
{
    use Audited, HasPlacements, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    /** Public page listing all images, e.g. /fa/gallery/3. */
    public function path(): string
    {
        return "/{$this->language->code}/gallery/{$this->id}";
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
