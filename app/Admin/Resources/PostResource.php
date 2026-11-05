<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Enums\MediaKind;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Rules\SafeUrl;
use Illuminate\Database\Eloquent\Model;

class PostResource extends Resource
{
    public bool $tracksUsers = true;

    public function model(): string
    {
        return Post::class;
    }

    public function key(): string
    {
        return 'posts';
    }

    public function title(): string
    {
        return 'خبر، اطلاعیه و پوستر';
    }

    public function with(): array
    {
        return ['language', 'category', 'page'];
    }

    public function filters(): array
    {
        return ['page_id', 'category_id', 'language_id'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('language_id', 'زبان', fn () => Options::languages(), ['required', 'exists:languages,id']),
            Field::select('page_id', 'صفحه (پیشوند آدرس)', fn () => Options::pages(), ['required', 'exists:pages,id'], null, 'آدرس خبر: /زبان/صفحه/خبر'),
            Field::select('category_id', 'دسته', fn () => Options::categories(\App\Enums\CategoryType::Post), ['required', 'exists:categories,id']),
            Field::select('edition_id', 'دوره', fn () => Options::editions(), ['nullable', 'exists:editions,id']),
            Field::text('title', 'عنوان', ['required', 'string', 'max:191']),
            Field::text('slug', 'نشانی (slug)', ['nullable', 'string', 'max:160'], 'خالی = خودکار از عنوان'),
            Field::text('translation_key', 'کلید ترجمه', ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/']),
            Field::text('lead', 'روتیتر', ['nullable', 'string', 'max:300']),
            Field::textarea('summary', 'خلاصه'),
            Field::select('image_id', 'تصویر', fn () => Options::media(MediaKind::Image), ['nullable', 'exists:media_assets,id']),
            Field::text('source_name', 'منبع', ['nullable', 'string', 'max:191']),
            Field::text('link_url', 'لینک (داخلی یا خارجی)', ['nullable', 'string', 'max:2048', new SafeUrl]),
            Field::checkbox('is_featured', 'ویژه', false),
            Field::number('sort_order', 'ترتیب'),
            Field::select('status', 'وضعیت', Options::statuses(), ['required', 'in:draft,published'], 'draft'),
            Field::datetime('published_at', 'زمان انتشار', 'خالی = همین حالا'),
            Field::datetime('expires_at', 'زمان انقضا', 'بعد از این زمان نمایش داده نمی‌شود'),
            Field::text('meta_title', 'عنوان سئو', ['nullable', 'string', 'max:191']),
            Field::text('meta_description', 'توضیح سئو', ['nullable', 'string', 'max:300']),
            Field::rich('body', 'متن'),
        ];
    }

    public function columns(): array
    {
        return ['عنوان' => 'title', 'زبان' => 'language.code', 'دسته' => 'category.name', 'وضعیت' => 'status', 'انتشار' => 'published_at', 'بازدید' => 'view_count'];
    }

    public function check(array $data, ?Model $model): void
    {
        $this->requireSameLanguage((int) $data['language_id'], $data, ['page_id' => Page::class, 'category_id' => Category::class]);
    }
}
