<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSlug;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['company_id', 'name', 'slug', 'description', 'meta_title', 'meta_description', 'sort_order', 'is_active'])]
class Product extends Model
{
    use Audited, HasSlug, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $product) => $product->description = HtmlSanitizer::clean($product->description));
    }

    protected function slugScope(): array
    {
        return ['company_id'];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function path(): string
    {
        return $this->company->path().'/'.$this->slug;
    }
}
