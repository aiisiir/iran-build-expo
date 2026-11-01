<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSingletonFlag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'starts_on', 'ends_on'])]
class Edition extends Model
{
    use Audited, HasSingletonFlag, SoftDeletes;

    protected array $singletonFlags = ['is_current'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'is_current' => 'boolean'];
    }

    public static function current(): ?self
    {
        return static::where('is_current', 1)->first();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }
}
