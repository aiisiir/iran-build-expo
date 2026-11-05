<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Language;
use App\Models\Link;
use App\Models\LinkGroup;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageModule;
use App\Models\Post;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sample content for every section of the home page (menu, footer, hero, news, links, companies, gallery, tour, FAQ), in fa and en.
 * Local use only:  php artisan db:seed --class=DemoContentSeeder   (then export SQL). All texts are placeholders: no real dates, venues or names.
 * Refuses to run on a database that already has pages, so real content is never touched.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction() || Page::withTrashed()->exists()) {
            $this->command?->warn('Skipped: production or the database already has pages.');

            return;
        }

        DB::transaction(function (): void {
            $media = collect(range(1, 4))->map(fn ($i) => MediaAsset::create([
                'kind' => 'image', 'path' => "demo/demo-{$i}.svg", 'original_name' => "demo-{$i}.svg", 'mime' => 'image/svg+xml',
                'size' => (int) @filesize(public_path("uploads/demo/demo-{$i}.svg")), 'width' => 1600, 'height' => 900,
            ]));

            foreach (['fa', 'en'] as $code) {
                $this->site(Language::where('code', $code)->firstOrFail(), $this->texts()[$code], $media);
            }
        });
    }

    private function site(Language $lang, array $t, $media): void
    {
        $page = fn (string $key, string $slug, string $title, ?string $body = null, bool $home = false) => tap(Page::create([
            'language_id' => $lang->id, 'translation_key' => "demo-{$key}", 'title' => $title, 'slug' => $slug, 'body' => $body,
            'meta_description' => $t['meta'], 'status' => PublishStatus::Published, 'published_at' => now()->subDay(),
        ]), fn (Page $p) => $home ? $p->claimFlag('is_home', ['language_id' => $lang->id]) : null);

        $home = $page('home', $t['home'], $t['home'], null, true);
        $news = $page('news', $t['news'], $t['news']);
        $companies = $page('companies', $t['companies'], $t['companies']);
        $about = $page('about', $t['about'], $t['about'], $t['about_body']);
        $contact = $page('contact', $t['contact'], $t['contact'], $t['contact_body']);

        SiteSettings::save($lang->id, $t['settings']);

        // Categories and posts (hero slides are the four featured posts).
        $cats = collect($t['categories'])->map(fn ($name, $i) => Category::create([
            'language_id' => $lang->id, 'translation_key' => "demo-cat-{$i}", 'type' => 'post', 'name' => $name, 'slug' => $name, 'sort_order' => $i,
        ]))->values();

        foreach ($t['posts'] as $i => [$cat, $title, $lead, $featured]) {
            Post::create([
                'language_id' => $lang->id, 'translation_key' => "demo-post-{$i}", 'page_id' => $news->id, 'category_id' => $cats[$cat]->id,
                'title' => $title, 'slug' => $title, 'lead' => $lead, 'summary' => $lead, 'body' => "<p>{$t['sample']}</p>",
                'image_id' => $media[$i % 4]->id, 'is_featured' => $featured, 'sort_order' => $i, 'status' => PublishStatus::Published,
                'published_at' => now()->subDays($i + 1),
            ]);
        }

        foreach ($t['companies_list'] as $i => [$name, $industry]) {
            Company::create([
                'language_id' => $lang->id, 'translation_key' => "demo-company-{$i}", 'page_id' => $companies->id, 'name' => $name, 'slug' => $name,
                'industry' => $industry, 'description' => "<p>{$t['sample']}</p>", 'sort_order' => $i, 'is_active' => true,
            ]);
        }

        $gallery = Gallery::create(['language_id' => $lang->id, 'title' => $t['gallery'], 'is_active' => true]);
        foreach (range(0, 4) as $i) {
            $gallery->items()->create(['media_id' => $media[$i % 4]->id, 'caption' => "{$t['gallery_caption']} ".($i + 1), 'sort_order' => $i]);
        }

        foreach ($t['faq'] as $i => [$q, $a]) {
            Faq::create(['language_id' => $lang->id, 'question' => $q, 'answer' => "<p>{$a}</p>", 'sort_order' => $i, 'is_active' => true]);
        }

        // One mechanism for menu, quick links, footer, organizers and supporters.
        $group = fn (string $placement, ?string $title, int $order) => LinkGroup::create([
            'language_id' => $lang->id, 'placement' => $placement, 'title' => $title, 'show_title' => $title !== null, 'sort_order' => $order, 'is_active' => true,
        ]);
        $link = fn (LinkGroup $g, string $label, ?Page $to, int $order, ?Link $parent = null, ?string $url = null) => Link::create([
            'link_group_id' => $g->id, 'parent_id' => $parent?->id, 'label' => $label, 'type' => $to ? 'page' : 'url', 'page_id' => $to?->id,
            'url' => $url, 'sort_order' => $order, 'is_active' => true,
        ]);
        $badge = "/{$lang->code}/badge";

        $menu = $group('menu', null, 1);
        $link($menu, $t['home'], $home, 1);
        $link($menu, $t['news'], $news, 2);
        $link($menu, $t['companies'], $companies, 3);
        $aboutLink = $link($menu, $t['about'], $about, 4);
        $link($menu, $t['contact'], $contact, 1, $aboutLink);
        $link($menu, $t['badge'], null, 2, $aboutLink, $badge);

        $quick = $group('quick_links', $t['quick'], 2);
        $link($quick, $t['news'], $news, 1);
        $link($quick, $t['companies'], $companies, 2);
        $link($quick, $t['badge'], null, 3, null, $badge);
        $link($quick, $t['contact'], $contact, 4);

        $organizers = $group('organizers', $t['organizers'], 3);
        $supporters = $group('supporters', $t['supporters'], 4);
        foreach ([1, 2, 3] as $n) {
            $link($organizers, "{$t['organizer']} {$n}", null, $n, null, '#');
            $link($supporters, "{$t['supporter']} {$n}", null, $n, null, '#');
        }

        $footerA = $group('footer', $t['footer_a'], 5);
        $link($footerA, $t['about'], $about, 1);
        $link($footerA, $t['contact'], $contact, 2);
        $footerB = $group('footer', $t['footer_b'], 6);
        $link($footerB, $t['news'], $news, 1);
        $link($footerB, $t['companies'], $companies, 2);
        $link($footerB, $t['badge'], null, 3, null, $badge);

        // Home page sections in display order (hero first, the video band is added by the template).
        $module = fn (string $type, ?string $title, string $display, int $limit, array $extra = []) => PageModule::create($extra + [
            'page_id' => $home->id, 'type' => $type, 'title' => $title, 'show_title' => $title !== null, 'display' => $display,
            'item_limit' => $limit, 'sort_order' => PageModule::where('page_id', $home->id)->count() + 1, 'is_active' => true,
        ]);
        $fromNews = ['source_page_id' => $news->id];

        $module('posts', null, 'hero', 4, $fromNews + ['category_id' => null]);
        $module('posts', $t['latest'], 'grid', 6, $fromNews + ['category_id' => $cats[0]->id]);
        $module('posts', $t['announcements'], 'list', 4, $fromNews + ['category_id' => $cats[1]->id]);
        $module('posts', $t['posters'], 'grid', 4, $fromNews + ['category_id' => $cats[2]->id]);
        $module('links', $t['quick'], 'grid', 6, ['link_group_id' => $quick->id]);
        $module('companies', $t['companies_title'], 'grid', 8, ['source_page_id' => $companies->id]);
        $module('gallery', $t['gallery'], 'grid', 6, ['gallery_id' => $gallery->id]);
        $module('embed', $t['tour'], 'grid', 1);
        $module('links', $t['organizers'], 'grid', 6, ['link_group_id' => $organizers->id]);
        $module('links', $t['supporters'], 'grid', 6, ['link_group_id' => $supporters->id]);
        $module('faq', $t['faq_title'], 'list', 8);
    }

    /** @return array<string, array<string, mixed>> */
    private function texts(): array
    {
        $fa = [
            'home' => 'خانه', 'news' => 'اخبار', 'companies' => 'شرکت‌ها', 'about' => 'درباره ما', 'contact' => 'تماس با ما', 'badge' => 'ثبت‌نام کارت سینه',
            'about_body' => '<p>این متن نمونه است و باید با معرفی رسمی نمایشگاه جایگزین شود.</p>', 'contact_body' => '<p>اطلاعات تماس رسمی از بخش تنظیمات پنل وارد می‌شود.</p>',
            'sample' => 'این متن نمونه است و باید با محتوای رسمی جایگزین شود.',
            'meta' => 'نمایشگاه بین‌المللی ساختمان ایران؛ اخبار، شرکت‌ها و محصولات صنعت ساختمان.',
            'settings' => ['site_name' => 'نمایشگاه ساختمان ایران', 'tagline' => 'درگاه یکپارچهٔ صنعت ساختمان', 'meta_description' => 'نمایشگاه بین‌المللی ساختمان ایران؛ اخبار، شرکت‌ها و محصولات صنعت ساختمان.',
                'footer_about' => 'درگاه اطلاع‌رسانی نمایشگاه ساختمان ایران.', 'footer_dates' => 'تاریخ برگزاری: به‌زودی اعلام می‌شود', 'footer_venue' => 'محل برگزاری: به‌زودی اعلام می‌شود', 'copyright' => '© نمایشگاه ساختمان ایران'],
            'categories' => ['اخبار', 'اطلاعیه‌ها', 'پوسترها'],
            'posts' => [
                [0, 'نمونهٔ خبر: معرفی بخش‌های نمایشگاه', 'متن کوتاه نمونه برای خبر نخست.', true],
                [0, 'نمونهٔ خبر: فراخوان حضور شرکت‌ها', 'متن کوتاه نمونه برای خبر دوم.', true],
                [0, 'نمونهٔ خبر: برنامهٔ بازدید متخصصان', 'متن کوتاه نمونه برای خبر سوم.', true],
                [0, 'نمونهٔ خبر: گزارش تصویری', 'متن کوتاه نمونه برای خبر چهارم.', true],
                [0, 'نمونهٔ خبر: نشست‌های تخصصی', 'متن کوتاه نمونه برای خبر پنجم.', false],
                [0, 'نمونهٔ خبر: مسیرهای دسترسی', 'متن کوتاه نمونه برای خبر ششم.', false],
                [1, 'نمونهٔ اطلاعیه: ساعت بازدید', 'متن کوتاه نمونه برای اطلاعیه.', false],
                [1, 'نمونهٔ اطلاعیه: ثبت‌نام کارت سینه', 'متن کوتاه نمونه برای اطلاعیه.', false],
                [2, 'نمونهٔ پوستر نخست', 'توضیح کوتاه نمونه.', false],
                [2, 'نمونهٔ پوستر دوم', 'توضیح کوتاه نمونه.', false],
            ],
            'companies_list' => [['شرکت نمونه ۱', 'مصالح ساختمانی'], ['شرکت نمونه ۲', 'تأسیسات'], ['شرکت نمونه ۳', 'سازه و فولاد'], ['شرکت نمونه ۴', 'کاشی و سرامیک']],
            'latest' => 'آخرین اخبار', 'announcements' => 'اطلاعیه‌ها', 'posters' => 'پوسترها', 'quick' => 'دسترسی سریع', 'companies_title' => 'شرکت‌های حاضر',
            'gallery' => 'گالری تصاویر', 'gallery_caption' => 'تصویر نمونه', 'tour' => 'تور مجازی', 'organizers' => 'برگزارکنندگان', 'supporters' => 'حامیان',
            'organizer' => 'برگزارکنندهٔ نمونه', 'supporter' => 'حامی نمونه', 'footer_a' => 'دربارهٔ ما', 'footer_b' => 'دسترسی', 'faq_title' => 'پرسش‌های متداول',
            'faq' => [['این پرسش نمونه است؟', 'بله؛ پاسخ‌ها را از پنل ویرایش کنید.'], ['چگونه کارت سینه بگیرم؟', 'از فرم «ثبت‌نام کارت سینه» اطلاعات را وارد کنید.'], ['اطلاعات رسمی کجا اعلام می‌شود؟', 'در همین سایت، بخش اخبار و اطلاعیه‌ها.']],
        ];
        $en = [
            'home' => 'Home', 'news' => 'News', 'companies' => 'Companies', 'about' => 'About', 'contact' => 'Contact', 'badge' => 'Badge registration',
            'about_body' => '<p>Sample text. Replace it with the official introduction of the exhibition.</p>', 'contact_body' => '<p>Official contact details are entered in the panel settings.</p>',
            'sample' => 'Sample text. Replace it with official content.',
            'meta' => 'Iran Build Expo: news, companies and products of the building industry.',
            'settings' => ['site_name' => 'Iran Build Expo', 'tagline' => 'One gateway to the building industry', 'meta_description' => 'Iran Build Expo: news, companies and products of the building industry.',
                'footer_about' => 'Information portal of Iran Build Expo.', 'footer_dates' => 'Dates: to be announced', 'footer_venue' => 'Venue: to be announced', 'copyright' => '© Iran Build Expo'],
            'categories' => ['News', 'Announcements', 'Posters'],
            'posts' => [
                [0, 'Sample news: introducing the exhibition sections', 'Short sample text for the first news item.', true],
                [0, 'Sample news: call for exhibitors', 'Short sample text for the second news item.', true],
                [0, 'Sample news: visit programme for professionals', 'Short sample text for the third news item.', true],
                [0, 'Sample news: photo report', 'Short sample text for the fourth news item.', true],
                [0, 'Sample news: specialist sessions', 'Short sample text for the fifth news item.', false],
                [0, 'Sample news: how to get here', 'Short sample text for the sixth news item.', false],
                [1, 'Sample announcement: visiting hours', 'Short sample text for an announcement.', false],
                [1, 'Sample announcement: badge registration', 'Short sample text for an announcement.', false],
                [2, 'Sample poster one', 'Short sample description.', false],
                [2, 'Sample poster two', 'Short sample description.', false],
            ],
            'companies_list' => [['Sample Company 1', 'Building materials'], ['Sample Company 2', 'HVAC and plumbing'], ['Sample Company 3', 'Structure and steel'], ['Sample Company 4', 'Tiles and ceramics']],
            'latest' => 'Latest news', 'announcements' => 'Announcements', 'posters' => 'Posters', 'quick' => 'Quick links', 'companies_title' => 'Participating companies',
            'gallery' => 'Photo gallery', 'gallery_caption' => 'Sample image', 'tour' => 'Virtual tour', 'organizers' => 'Organizers', 'supporters' => 'Supporters',
            'organizer' => 'Sample organizer', 'supporter' => 'Sample supporter', 'footer_a' => 'About', 'footer_b' => 'Explore', 'faq_title' => 'Frequently asked questions',
            'faq' => [['Is this a sample question?', 'Yes. Edit the answers in the panel.'], ['How do I get a badge?', 'Fill in the "Badge registration" form.'], ['Where are official details published?', 'Here on this site, in News and Announcements.']],
        ];

        return ['fa' => $fa, 'en' => $en];
    }
}
