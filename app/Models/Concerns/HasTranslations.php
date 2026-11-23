<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Records of different languages that share a translation_key are equivalents (used for hreflang and the language switcher). */
trait HasTranslations
{
    public function language(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Language::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(static::class, 'translation_key', 'translation_key')
            ->where('language_id', '!=', $this->language_id);
    }

    /** Equivalent records in other languages that are publicly visible (for hreflang and the language switcher). */
    public function publicTranslations(): \Illuminate\Support\Collection
    {
        return $this->translations()->with('language')->get()->filter->isPublic()->values();
    }
}
