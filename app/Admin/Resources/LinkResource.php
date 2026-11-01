<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Enums\MediaKind;
use App\Models\Link;
use App\Models\LinkGroup;
use App\Models\Page;
use App\Rules\SafeUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class LinkResource extends Resource
{
    public function model(): string
    {
        return Link::class;
    }

    public function key(): string
    {
        return 'links';
    }

    public function title(): string
    {
        return 'لینک‌ها';
    }

    public function with(): array
    {
        return ['group'];
    }

    public function filters(): array
    {
        return ['link_group_id'];
    }

    public function fields(?Model $model): array
    {
        return [
            Field::select('link_group_id', 'گروه', fn () => Options::linkGroups(), ['required', 'exists:link_groups,id']),
            Field::select('parent_id', 'والد (برای زیرمنو)', fn () => Options::links(), ['nullable', 'exists:links,id']),
            Field::text('label', 'متن', ['required', 'string', 'max:191']),
            Field::checkbox('show_label', 'نمایش متن', true),
            Field::select('type', 'نوع', Options::linkTypes(), ['required', 'in:page,url,file']),
            Field::select('page_id', 'صفحه (نوع صفحه)', fn () => Options::pages(), ['nullable', 'exists:pages,id']),
            Field::text('url', 'نشانی (نوع نشانی)', ['nullable', 'string', 'max:2048', new SafeUrl]),
            Field::select('file_media_id', 'فایل (نوع فایل)', fn () => Options::media(), ['nullable', 'exists:media_assets,id']),
            Field::select('icon_media_id', 'آیکون/تصویر', fn () => Options::media(MediaKind::Image), ['nullable', 'exists:media_assets,id']),
            Field::text('css_class', 'کلاس CSS', ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\- ]+$/']),
            Field::select('target', 'باز شدن', Options::targets(), ['required', 'in:_self,_blank'], '_self'),
            Field::text('title_attr', 'عنوان (title)', ['nullable', 'string', 'max:191']),
            Field::rich('body', 'متن توضیحی'),
            Field::number('sort_order', 'ترتیب'),
            Field::checkbox('is_active', 'فعال'),
        ];
    }

    public function columns(): array
    {
        return ['متن' => 'label', 'گروه' => 'group.title', 'نوع' => 'type', 'ترتیب' => 'sort_order', 'فعال' => 'is_active'];
    }

    public function check(array $data, ?Model $model): void
    {
        $needs = ['page' => 'page_id', 'url' => 'url', 'file' => 'file_media_id'][$data['type']];

        if (empty($data[$needs])) {
            throw ValidationException::withMessages([$needs => 'برای این نوع لینک، این مورد لازم است.']);
        }

        $languageId = (int) LinkGroup::whereKey($data['link_group_id'])->value('language_id');
        $this->requireSameLanguage($languageId, $data, ['page_id' => Page::class]);
    }
}
