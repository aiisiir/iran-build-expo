<?php

namespace App\Models;

use App\Enums\BadgeStatus;
use App\Enums\Gender;
use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['edition_id', 'badge_type_id', 'full_name', 'phone', 'email', 'gender', 'age', 'address', 'education_degree', 'education_field', 'activity_field', 'photo_path'])]
#[Hidden(['phone', 'email', 'address', 'photo_path'])]
class Badge extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return [
            'phone' => 'encrypted',
            'email' => 'encrypted',
            'address' => 'encrypted',
            'gender' => Gender::class,
            'status' => BadgeStatus::class,
            'reviewed_at' => 'datetime',
            'printed_at' => 'datetime',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BadgeType::class, 'badge_type_id');
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
