<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

/** One piece of site-wide custom CSS or JS. All active pieces of a type are served as one cached file (see CustomAssetController). */
#[Fillable(['title', 'type', 'code', 'is_active', 'sort_order'])]
class CustomSnippet extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('custom.bundles');
        static::saved($forget);
        static::deleted($forget);
        static::restored($forget);
    }

    /** @return array{css: array{code: string, version: string}|null, js: array{code: string, version: string}|null} */
    public static function bundles(): array
    {
        return Cache::rememberForever('custom.bundles', function (): array {
            $active = static::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()->groupBy('type');

            return collect(['css', 'js'])->mapWithKeys(function (string $type) use ($active): array {
                $code = $active->get($type, collect())->map(fn ($s) => "/* {$s->title} */\n".$s->code)->implode("\n\n");

                return [$type => $code === '' ? null : ['code' => $code, 'version' => substr(md5($code), 0, 10)]];
            })->all();
        });
    }
}
