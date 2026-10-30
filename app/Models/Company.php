<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslations;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'translation_key', 'page_id', 'name', 'slug', 'logo_id', 'industry', 'description', 'meta_title', 'meta_description', 'sort_order', 'is_active'])]
class Company extends Model
{
    use Audited, HasSlug, HasTranslations, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $company) => $company->description = HtmlSanitizer::clean($company->description));
    }

    protected function slugScope(): array
    {
        return ['language_id', 'page_id'];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'logo_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function editions(): BelongsToMany
    {
        return $this->belongsToMany(Edition::class)->withTimestamps();
    }

    public function path(): string
    {
        return "/{$this->language->code}/{$this->page->slug}/{$this->slug}";
    }
}
