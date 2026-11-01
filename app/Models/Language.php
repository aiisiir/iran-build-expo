<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Models\Concerns\HasSingletonFlag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['code', 'name', 'native_name', 'direction', 'is_active', 'sort_order'])]
class Language extends Model
{
    use Audited, HasSingletonFlag;

    protected array $singletonFlags = ['is_default'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_default' => 'boolean'];
    }

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('languages.active');
        static::saved($forget);
        static::deleted($forget);
    }

    /** Active languages keyed by code (cached; the routing layer reads this on every request). */
    public static function active(): Collection
    {
        return Cache::rememberForever('languages.active', fn () => static::query()
            ->where('is_active', true)->orderBy('sort_order')->get()->keyBy('code'));
    }

    public static function default(): ?self
    {
        return static::active()->first(fn ($l) => $l->is_default) ?? static::active()->first();
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    public function htmlLang(): string
    {
        return $this->code === 'fa' ? 'fa-IR' : $this->code;
    }
}
