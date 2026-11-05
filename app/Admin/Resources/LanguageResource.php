<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class LanguageResource extends Resource
{
    public bool $adminOnly = true;

    public array $virtual = ['is_default'];

    public function model(): string
    {
        return Language::class;
    }

    public function key(): string
    {
        return 'languages';
    }

    public function title(): string
    {
        return 'زبان‌ها';
    }

    public function fields(?Model $model): array
    {
        return [
            Field::text('code', 'کد زبان (مثل fa)', ['required', 'string', 'regex:/^[a-z]{2,3}(-[A-Za-z]{2,4})?$/', Rule::unique('languages', 'code')->ignore($model?->id)], 'بعد از ساخت تغییر ندهید؛ در آدرس‌ها می‌آید.'),
            Field::text('name', 'نام انگلیسی', ['required', 'string', 'max:60']),
            Field::text('native_name', 'نام بومی', ['required', 'string', 'max:60']),
            Field::select('direction', 'جهت', Options::directions(), ['required', 'in:rtl,ltr']),
            Field::checkbox('is_active', 'فعال'),
            Field::checkbox('is_default', 'زبان پیش‌فرض', false),
            Field::number('sort_order', 'ترتیب'),
        ];
    }

    public function columns(): array
    {
        return ['کد' => 'code', 'نام' => 'native_name', 'جهت' => 'direction', 'فعال' => 'is_active', 'پیش‌فرض' => 'is_default'];
    }

    public function afterSave(Model $model, array $data): void
    {
        if (! empty($data['is_default'])) {
            $model->claimFlag('is_default');
        }
    }

    public function canDelete(Model $model): bool
    {
        return false; // languages are deactivated, never deleted
    }
}
