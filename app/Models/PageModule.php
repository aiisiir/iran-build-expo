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

#[Fillable(['page_id', 'type', 'title', 'show_title', 'is_active', 'sort_order', 'display', 'item_limit', 'source_page_id', 'category_id', 'link_group_id', 'gallery_id', 'media_id', 'body', 'css_class', 'css_id'])]
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

    /** "auto" means a slider from 4 items up, a static grid below that. */
    public function effectiveDisplay(): string
    {
        return $this->display === ModuleDisplay::Auto ? ($this->items->count() >= 4 ? 'slider' : 'grid') : $this->display->value;
    }

    /** Placement of the module's group, or of its first link's group when the module just shows the groups placed on the page. */
    private function placement(): ?\App\Enums\LinkPlacement
    {
        return $this->linkGroup?->placement ?? ($this->relationLoaded('items') ? $this->items->first()?->group?->placement : null);
    }

    /** Supporters layout: chosen by the `supporters-section` CSS class or by a group placed as "supporters". */
    public function isSupporters(): bool
    {
        return in_array('supporters-section', explode(' ', (string) $this->css_class), true)
            || $this->placement() === \App\Enums\LinkPlacement::Supporters;
    }

    /** Organizer logos (hero strip pill): a links module whose group is placed as "organizers". */
    public function isOrganizers(): bool
    {
        return $this->type->value === 'links' && $this->placement() === \App\Enums\LinkPlacement::Organizers;
    }

    /** Page whose records feed this module (defaults to the module's own page). */
    public function effectiveSourcePageId(): int
    {
        return $this->source_page_id ?? $this->page_id;
    }
}
