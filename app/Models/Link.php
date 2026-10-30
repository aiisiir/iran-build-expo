<?php

namespace App\Models;

use App\Enums\LinkType;
use App\Models\Concerns\Audited;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['link_group_id', 'parent_id', 'label', 'show_label', 'type', 'page_id', 'url', 'file_media_id', 'icon_media_id', 'css_class', 'target', 'title_attr', 'body', 'sort_order', 'is_active'])]
class Link extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['type' => LinkType::class, 'show_label' => 'boolean', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $link) => $link->body = HtmlSanitizer::clean($link->body));
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(LinkGroup::class, 'link_group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('is_active', true)->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function fileMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'file_media_id');
    }

    public function iconMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'icon_media_id');
    }

    /** Final href; null when the target no longer exists. */
    public function href(): ?string
    {
        return match ($this->type) {
            LinkType::Page => $this->page?->path(),
            LinkType::File => $this->fileMedia?->url(),
            LinkType::Url => $this->url,
        };
    }
}
