<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\Page;
use Illuminate\Database\Eloquent\Model;

class PageResource extends Resource
{
    public bool $tracksUsers = true;

    public array $virtual = ['is_home'];

    public function model(): string
    {
        return Page::class;
    }

    public function key(): string
    {
        return 'pages';
    }

    public function title(): string
    {
        return 'صفحه‌ها';
    }

    public function with(): array
    {
        return ['language'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::text('title', 'عنوان', ['required', 'string', 'max:191']),
            Field::text('slug', 'نشانی (slug)', ['nullable', 'string', 'max:160'], 'خالی = خودکار از عنوان'),
            Field::text('translation_key', 'کلید ترجمه', ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'], 'اختیاری؛ در دو زبان یکسان بنویسید.'),
            Field::checkbox('is_home', 'صفحهٔ اصلی این زبان', false),
            Field::select('status', 'وضعیت', Options::statuses(), ['required', 'in:draft,published'], 'draft'),
            Field::datetime('published_at', 'زمان انتشار', 'خالی = همین حالا (برای زمان‌بندی، آیندهٔ نزدیک بگذارید)'),
            Field::text('meta_title', 'عنوان سئو', ['nullable', 'string', 'max:191']),
            Field::text('meta_description', 'توضیح سئو', ['nullable', 'string', 'max:300']),
            Field::select('og_media_id', 'تصویر اشتراک‌گذاری', fn () => Options::media(\App\Enums\MediaKind::Image), ['nullable', 'exists:media_assets,id']),
            Field::checkbox('noindex', 'نمایه نشود (noindex)', false),
            Field::rich('body', 'متن'),
        ];
    }

    public function columns(): array
    {
        return ['عنوان' => 'title', 'زبان' => 'language.code', 'slug' => 'slug', 'وضعیت' => 'status', 'اصلی' => 'is_home', 'انتشار' => 'published_at'];
    }

    public function children(Model $model): array
    {
        return ['بلوک‌های صفحه' => route('admin.resource.index', ['resource' => 'page-modules', 'page_id' => $model->id])];
    }

    public function afterSave(Model $model, array $data): void
    {
        if (! empty($data['is_home'])) {
            $model->claimFlag('is_home', ['language_id' => $model->language_id]);
        } elseif ($model->is_home) {
            $model->forceFill(['is_home' => null])->save();
        }
    }
}
