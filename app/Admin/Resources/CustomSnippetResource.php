<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Resource;
use App\Models\CustomSnippet;
use Illuminate\Database\Eloquent\Model;

/** Site-wide custom CSS/JS. Superadmin only: JavaScript here runs for every visitor. */
class CustomSnippetResource extends Resource
{
    public bool $superOnly = true;

    public function model(): string
    {
        return CustomSnippet::class;
    }

    public function key(): string
    {
        return 'custom-code';
    }

    public function title(): string
    {
        return 'استایل و اسکریپت اختصاصی';
    }

    public function fields(?Model $model): array
    {
        return [
            Field::text('title', 'عنوان (برای یادآوری)', ['required', 'string', 'max:120']),
            Field::select('type', 'نوع', ['css' => 'استایل (CSS)', 'js' => 'اسکریپت (JavaScript)'], ['required', 'in:css,js']),
            Field::textarea('code', 'کد', ['required', 'string', 'max:200000'], 'فقط کد؛ بدون تگ <style> یا <script>. اسکریپت از همین دامنه اجرا می‌شود (CSP).'),
            Field::checkbox('is_active', 'فعال (با خاموش کردن، فوراً از سایت برداشته می‌شود)'),
            Field::number('sort_order', 'ترتیب', 0),
        ];
    }

    public function columns(): array
    {
        return ['عنوان' => 'title', 'نوع' => 'type', 'فعال' => 'is_active', 'ترتیب' => 'sort_order'];
    }
}
