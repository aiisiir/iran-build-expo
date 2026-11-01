<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSingletonFlag;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Support\HtmlSanitizer;
use App\Support\Slug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'translation_key', 'title', 'slug', 'body', 'meta_title', 'meta_description', 'og_media_id', 'noindex', 'status', 'published_at'])]
class Page extends Model
{
    use Audited, HasSingletonFlag, HasTranslations, Publishable, SoftDeletes;
    use HasSlug {
        slugTaken as private slugTakenInScope;
    }

    protected array $singletonFlags = ['is_home'];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'is_home' => 'boolean',
            'noindex' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $page) => $page->body = HtmlSanitizer::clean($page->body));
    }

    protected function slugScope(): array
    {
        return ['language_id'];
    }

    /** Fixed routes live at the same level as page slugs, so those words are never handed out. */
    protected function slugTaken(string $slug): bool
    {
        return in_array($slug, Slug::RESERVED, true) || $this->slugTakenInScope($slug);
    }

    public function isPublic(): bool
    {
        return $this->status === PublishStatus::Published && ($this->published_at === null || $this->published_at <= now());
    }

    public function modules(): HasMany
    {
        return $this->hasMany(PageModule::class)->orderBy('sort_order');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'og_media_id');
    }

    /** Public path without host, e.g. /fa/about (home is /fa). */
    public function path(): string
    {
        $locale = $this->language->code;

        return $this->is_home ? "/{$locale}" : "/{$locale}/{$this->slug}";
    }
}
