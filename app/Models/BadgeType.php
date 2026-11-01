<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'color', 'sort_order', 'is_active'])]
class BadgeType extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
