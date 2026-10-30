<?php

namespace App\Models;

use App\Enums\ModuleDisplay;
use App\Enums\ModuleType;
use App\Models\Concerns\Audited;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['page_id', 'type', 'title', 'show_title', 'is_active', 'sort_order', 'display', 'item_limit', 'source_page_id', 'category_id', 'link_group_id', 'gallery_id', 'media_id', 'body'])]
class PageModule extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => ModuleType::class,
            'display' => ModuleDisplay::class,
            'show_title' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $module) => $module->body = HtmlSanitizer::clean($module->body));
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function sourcePage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'source_page_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function linkGroup(): BelongsTo
    {
        return $this->belongsTo(LinkGroup::class);
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'media_id');
    }

    /** Page whose records feed this module (defaults to the module's own page). */
    public function effectiveSourcePageId(): int
    {
        return $this->source_page_id ?? $this->page_id;
    }
}
