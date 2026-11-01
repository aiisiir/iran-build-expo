<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Resource;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class EditionResource extends Resource
{
    public bool $adminOnly = true;

    public array $virtual = ['is_current'];

    public function model(): string
    {
        return Edition::class;
    }

    public function key(): string
    {
        return 'editions';
    }

    public function title(): string
    {
        return 'دوره‌های نمایشگاه';
    }

    public function fields(?Model $model): array
    {
        return [
            Field::text('code', 'کد (مثل 1405)', ['required', 'string', 'max:30', Rule::unique('editions', 'code')->ignore($model?->id)]),
            Field::text('name', 'نام داخلی', ['required', 'string', 'max:150']),
            Field::date('starts_on', 'شروع'),
            Field::date('ends_on', 'پایان'),
            Field::checkbox('is_current', 'دورهٔ جاری', false),
        ];
    }

    public function columns(): array
    {
        return ['کد' => 'code', 'نام' => 'name', 'شروع' => 'starts_on', 'پایان' => 'ends_on', 'جاری' => 'is_current'];
    }

    public function afterSave(Model $model, array $data): void
    {
        if (! empty($data['is_current'])) {
            $model->claimFlag('is_current');
        }
    }
}
