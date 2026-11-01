<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['language_id', 'category_id', 'question', 'answer', 'sort_order', 'is_active'])]
class Faq extends Model
{
    use Audited, SoftDeletes;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(fn (self $faq) => $faq->answer = (string) HtmlSanitizer::clean($faq->answer));
    }
}
