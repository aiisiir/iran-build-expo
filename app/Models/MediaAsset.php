<?php

namespace App\Models;

use App\Enums\MediaKind;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'kind', 'path', 'original_name', 'mime', 'size', 'width', 'height', 'alt', 'title', 'created_by'])]
class MediaAsset extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['kind' => MediaKind::class];
    }

    /** Public URL (uploads live in the web root; PHP execution there is disabled by .htaccess). */
    public function url(): string
    {
        return url('uploads/'.$this->path);
    }
}
