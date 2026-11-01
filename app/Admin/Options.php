<?php

namespace App\Admin;

use App\Enums\CategoryType;
use App\Enums\MediaKind;
use App\Models\Category;
use App\Models\Company;
use App\Models\Edition;
use App\Models\Gallery;
use App\Models\Language;
use App\Models\LinkGroup;
use App\Models\Link;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Product;

/** Select-box choices and Persian labels used by admin forms. */
final class Options
{
    public static function languages(): array
    {
        return Language::active()->mapWithKeys(fn ($l) => [$l->id => $l->native_name])->all();
    }

    public static function pages(): array
    {
        return Page::with('language')->orderBy('language_id')->orderBy('title')->get()
            ->mapWithKeys(fn ($p) => [$p->id => "[{$p->language->code}] {$p->title}"])->all();
    }

    public static function categories(?CategoryType $type = null): array
    {
        return Category::with('language')->when($type, fn ($q) => $q->where('type', $type))->orderBy('language_id')->orderBy('name')->get()
            ->mapWithKeys(fn ($c) => [$c->id => "[{$c->language->code}] {$c->name}"])->all();
    }

    public static function editions(): array
    {
        return Edition::orderByDesc('id')->pluck('name', 'id')->all();
    }

    public static function media(?MediaKind $kind = null): array
    {
        return MediaAsset::when($kind, fn ($q) => $q->where('kind', $kind))->latest('id')->limit(500)->get()
            ->mapWithKeys(fn ($m) => [$m->id => "#{$m->id} {$m->original_name}"])->all();
    }

    public static function linkGroups(): array
    {
        return LinkGroup::with('language')->orderBy('language_id')->get()
            ->mapWithKeys(fn ($g) => [$g->id => "[{$g->language->code}] ".(self::placements()[$g->placement->value] ?? '').' '.$g->title])->all();
    }

    public static function links(): array
    {
        return Link::with('group.language')->latest('id')->limit(500)->get()
            ->mapWithKeys(fn ($l) => [$l->id => "[{$l->group->language->code}] {$l->label}"])->all();
    }

    public static function galleries(): array
    {
        return Gallery::with('language')->get()->mapWithKeys(fn ($g) => [$g->id => $g->title])->all();
    }

    public static function companies(): array
    {
        return Company::with('language')->orderBy('name')->get()->mapWithKeys(fn ($c) => [$c->id => "[{$c->language->code}] {$c->name}"])->all();
    }

    public static function products(): array
    {
        return Product::with('company')->orderBy('name')->get()->mapWithKeys(fn ($p) => [$p->id => "{$p->company->name} / {$p->name}"])->all();
    }

    public static function statuses(): array
    {
        return ['draft' => 'پیش‌نویس', 'published' => 'منتشرشده'];
    }

    public static function roles(): array
    {
        return ['admin' => 'مدیر', 'editor' => 'ویراستار'];
    }

    public static function categoryTypes(): array
    {
        return ['post' => 'خبر و اطلاعیه', 'faq' => 'پرسش‌های متداول'];
    }

    public static function moduleTypes(): array
    {
        return ['posts' => 'لیست خبر/اطلاعیه/پوستر', 'links' => 'لینک‌ها', 'content' => 'متن و تصویر', 'gallery' => 'گالری', 'faq' => 'پرسش‌های متداول', 'companies' => 'شرکت‌ها', 'embed' => 'تور سه‌بعدی'];
    }

    public static function displays(): array
    {
        return ['list' => 'لیست', 'grid' => 'شبکه', 'slider' => 'اسلایدر', 'auto' => 'خودکار (۴ مورد یا بیشتر اسلایدر)'];
    }

    public static function linkTypes(): array
    {
        return ['page' => 'صفحهٔ سایت', 'url' => 'نشانی', 'file' => 'فایل'];
    }

    public static function placements(): array
    {
        return ['menu' => 'منوی اصلی', 'logo' => 'لوگو', 'quick_links' => 'دسترسی سریع', 'organizers' => 'برگزارکنندگان', 'supporters' => 'حامیان', 'footer' => 'فوتر'];
    }

    public static function targets(): array
    {
        return ['_self' => 'همین پنجره', '_blank' => 'پنجرهٔ جدید'];
    }

    public static function directions(): array
    {
        return ['rtl' => 'راست‌به‌چپ', 'ltr' => 'چپ‌به‌راست'];
    }

    public static function genders(): array
    {
        return ['male' => 'مرد', 'female' => 'زن'];
    }

    public static function badgeStatuses(): array
    {
        return ['pending' => 'در انتظار تأیید', 'approved' => 'تأیید شده', 'rejected' => 'رد شده'];
    }
}
