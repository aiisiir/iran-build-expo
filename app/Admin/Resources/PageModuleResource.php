<?php

namespace App\Admin\Resources;

use App\Admin\Field;
use App\Admin\Options;
use App\Admin\Resource;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\LinkGroup;
use App\Models\Page;
use App\Models\PageModule;
use Illuminate\Database\Eloquent\Model;

class PageModuleResource extends Resource
{
    public function model(): string
    {
        return PageModule::class;
    }

    public function key(): string
    {
        return 'page-modules';
    }

    public function title(): string
    {
        return 'بلوک‌های صفحه';
    }

    public function with(): array
    {
        return ['page'];
    }

    public function filters(): array
    {
        return ['page_id'];
    }

    public function fields(?Model $model): array
    {
        $optional = fn (string $table) => ['nullable', "exists:{$table},id"];

        return [
            Field::select('page_id', 'صفحه', fn () => Options::pages(), ['required', 'exists:pages,id']),
            Field::select('type', 'نوع بلوک', Options::moduleTypes(), ['required', 'in:'.implode(',', array_keys(Options::moduleTypes()))]),
            Field::text('title', 'عنوان بلوک', ['nullable', 'string', 'max:191'], 'مثلاً «آخرین اخبار»'),
            Field::checkbox('show_title', 'نمایش عنوان'),
            Field::checkbox('is_active', 'فعال'),
            Field::number('sort_order', 'ترتیب'),
            Field::select('display', 'نحوهٔ نمایش', Options::displays(), ['required', 'in:list,grid,slider,auto'], 'list'),
            Field::number('item_limit', 'تعداد آیتم', 6),
            Field::select('source_page_id', 'صفحهٔ منبع (اخبار/شرکت‌ها)', fn () => Options::pages(), $optional('pages'), null, 'خالی = همین صفحه'),
            Field::select('category_id', 'دستهٔ خبر', fn () => Options::categories(), $optional('categories')),
            Field::select('link_group_id', 'گروه لینک', fn () => Options::linkGroups(), $optional('link_groups')),
            Field::select('gallery_id', 'گالری', fn () => Options::galleries(), $optional('galleries')),
            Field::select('media_id', 'تصویر (بلوک متن)', fn () => Options::media(\App\Enums\MediaKind::Image), $optional('media_assets')),
            Field::rich('body', 'متن (بلوک متن)'),
        ];
    }

    public function columns(): array
    {
        return ['عنوان' => 'title', 'صفحه' => 'page.title', 'نوع' => 'type', 'نمایش' => 'display', 'ترتیب' => 'sort_order', 'فعال' => 'is_active'];
    }

    public function check(array $data, ?Model $model): void
    {
        $this->requireSameLanguage((int) Page::whereKey($data['page_id'])->value('language_id'), $data, [
            'source_page_id' => Page::class, 'category_id' => Category::class, 'link_group_id' => LinkGroup::class, 'gallery_id' => Gallery::class,
        ]);
    }
}
