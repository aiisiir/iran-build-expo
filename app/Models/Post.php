<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'translation_key', 'page_id', 'category_id', 'edition_id', 'title', 'slug', 'lead', 'summary', 'body', 'image_id', 'source_name', 'link_url', 'is_featured', 'sort_order', 'status', 'published_at', 'expires_at', 'meta_title', 'meta_description'])]
class Post extends Model
{
    use Audited, HasSlug, HasTranslations, Publishable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $post) => $post->body = HtmlSanitizer::clean($post->body));
    }

    protected function slugScope(): array
    {
        return ['language_id', 'page_id'];
    }

    /** Visible to the public right now. */
    public function scopeLive(Builder $query): void
    {
        $query->published()->notExpired();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'image_id');
    }

    public function isPublic(): bool
    {
        return $this->status === PublishStatus::Published
            && ($this->published_at === null || $this->published_at <= now())
            && ($this->expires_at === null || $this->expires_at > now());
    }

    public function path(): string
    {
        return "/{$this->language->code}/{$this->page->slug}/{$this->slug}";
    }
}
