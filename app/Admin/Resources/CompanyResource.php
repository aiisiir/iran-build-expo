<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Enums\MediaKind;
use App\Models\Company;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;

class CompanyResource extends Resource
{
    public array $virtual = ['edition_ids'];

    public function model(): string
    {
        return Company::class;
    }

    public function key(): string
    {
        return 'companies';
    }

    public function title(): string
    {
        return 'شرکت‌ها';
    }

    public function with(): array
    {
        return ['language'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::select('page_id', 'صفحهٔ فهرست شرکت‌ها (پیشوند آدرس)', fn () => Options::pages(), ['required', 'exists:pages,id']),
            Field::text('name', 'نام شرکت', ['required', 'string', 'max:191']),
            Field::text('slug', 'نشانی (slug)', ['nullable', 'string', 'max:160'], 'خالی = خودکار از نام'),
            Field::text('translation_key', 'کلید ترجمه', ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/']),
            Field::select('logo_id', 'لوگو', fn () => Options::media(MediaKind::Image), ['nullable', 'exists:media_assets,id']),
            Field::text('industry', 'حوزهٔ فعالیت (نشان گوشه)', ['nullable', 'string', 'max:120']),
            Field::multiselect('edition_ids', 'دوره‌های حضور', fn () => Options::editions(), fn (Model $m) => $m->exists ? $m->editions()->pluck('editions.id')->all() : []),
            Field::text('meta_title', 'عنوان سئو', ['nullable', 'string', 'max:191']),
            Field::text('meta_description', 'توضیح سئو', ['nullable', 'string', 'max:300']),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
            Field::rich('description', 'توضیحات'),
        ];
    }

    public function columns(): array
    {
        return ['نام' => 'name', 'زبان' => 'language.code', 'حوزه' => 'industry', 'فعال' => 'is_active'];
    }

    public function check(array $data, ?Model $model): void
    {
        $this->requireSameLanguage((int) $data['language_id'], $data, ['page_id' => Page::class]);
    }

    public function afterSave(Model $model, array $data): void
    {
        $model->editions()->sync($data['edition_ids'] ?? []);
    }

    public function children(Model $model): array
    {
        return ['محصولات' => route('admin.resource.index', ['resource' => 'products', 'company_id' => $model->id])];
    }
}
