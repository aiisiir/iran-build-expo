<?php

namespace App\Models;

use App\Enums\MediaKind;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

#[Fillable(['language_id', 'kind', 'path', 'original_name', 'mime', 'size', 'width', 'height', 'alt', 'title', 'created_by'])]
class MediaAsset extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['kind' => MediaKind::class, 'is_inline' => 'boolean'];
    }

    /** Public URL (uploads live in the web root; PHP execution there is disabled by .htaccess). */
    public function url(): string
    {
        return url('uploads/'.$this->path);
    }

    /** Every column that points at a media row: table => columns. Keep in sync when a new media field is added. */
    public const REFERENCES = [
        'posts' => ['image_id'], 'companies' => ['logo_id'], 'page_modules' => ['media_id'], 'gallery_items' => ['media_id'],
        'product_images' => ['media_id'], 'links' => ['file_media_id', 'icon_media_id'], 'pages' => ['og_media_id'],
    ];

    /** Still used by any record (soft-deleted ones count: they can be restored). */
    public function isUsed(): bool
    {
        foreach (self::REFERENCES as $table => $columns) {
            foreach ($columns as $column) {
                if (DB::table($table)->where($column, $this->id)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    /** A file uploaded from a record's form is removed (row and file) once nothing uses it any more. */
    public function deleteIfOrphan(): void
    {
        if ($this->is_inline && ! $this->isUsed()) {
            File::delete(public_path('uploads/'.$this->path));
            $this->forceDelete();
        }
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
