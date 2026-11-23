<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Support\DateFormat;
use App\Support\Digits;
use App\Support\Jalali;
use App\Models\Concerns\HasSingletonFlag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['code', 'name', 'name_en', 'starts_on', 'ends_on', 'hours'])]
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

    public function title(Language $language): string
    {
        return $language->code === 'fa' ? $this->name : ($this->name_en ?: $this->name);
    }

    /** "Saturday 14 Azar 1405 to Monday 16 Azar 1405 | 10:00 - 18:00", in the reader's calendar. */
    public function schedule(Language $language): string
    {
        $fa = $language->code === 'fa';
        $day = fn ($d) => $fa ? Jalali::weekday($d).' '.DateFormat::show($d, $language) : $d->format('l j F Y');
        $dates = ! $this->starts_on ? '' : ($this->ends_on && ! $this->ends_on->isSameDay($this->starts_on)
            ? $day($this->starts_on).($fa ? ' تا ' : ' – ').$day($this->ends_on) : $day($this->starts_on));
        $hours = $this->hours ? ($fa ? 'ساعت '.Digits::toPersian($this->hours) : $this->hours) : '';

        return implode(' | ', array_filter([$dates, $hours]));
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }
}
