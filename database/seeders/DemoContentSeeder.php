<?php

namespace Database\Seeders;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Company;
use App\Models\Edition;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Language;
use App\Models\Link;
use App\Models\LinkGroup;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\PageModule;
use App\Models\Post;
use App\Support\PublicUrl;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The complete first content of the site, in fa and en, built only from panel records: logo, menu, hero slides, news, supporters,
 * galleries, companies, FAQ, footer, the video band (settings). Nothing on the home page is written in a template.
 * Pictures: public/uploads/demo/{key}.webp|jpg|jpeg|png|svg is used when present (drop real photos there, see docs/content-guide.md);
 * otherwise a placeholder SVG is generated under that key. Company and supporter names are real Iranian building-industry firms used as pilot demo data
 * at the owner's request and responsibility (no logos are bundled: drop real ones under company-N / supporter-N). Dates and venues stay placeholders.
 * Refuses to run in production or on a database that already has pages, so real content is never touched.
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
            // The badge form only opens while a "current" exhibition edition exists.
            if (! Edition::current()) {
                Edition::create(['code' => 'demo', 'name' => 'دورهٔ نمونه'])->claimFlag('is_current');
            }

            foreach (['fa', 'en'] as $code) {
                $this->site(Language::where('code', $code)->firstOrFail(), $this->texts()[$code]);
            }
        });
    }

    /** @var array<string, MediaAsset> one media row per picture key, shared by both languages */
    private array $assets = [];

    private function asset(string $key, int $w = 1600, int $h = 900): MediaAsset
    {
        if (isset($this->assets[$key])) {
            return $this->assets[$key];
        }

        $dir = public_path('uploads/demo');
        $file = glob("{$dir}/{$key}.{webp,jpg,jpeg,png,svg}", GLOB_BRACE)[0] ?? null;
        if (! $file) {
            @mkdir($dir, 0755, true);
            file_put_contents($file = "{$dir}/{$key}.svg", $this->placeholder($key, $w, $h));
        }
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $dim = $ext === 'svg' ? [$w, $h] : (getimagesize($file) ?: [$w, $h]);

        return $this->assets[$key] = MediaAsset::create([
            'kind' => 'image', 'path' => "demo/{$key}.{$ext}", 'original_name' => "{$key}.{$ext}", 'size' => (int) filesize($file),
            'mime' => ['svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$ext],
            'width' => $dim[0], 'height' => $dim[1],
        ]);
    }

    /** Neutral stand-in picture (hall skyline for photos, monogram for square logos); deterministic per key. */
    private function placeholder(string $key, int $w, int $h): string
    {
        $seed = crc32($key);
        $hue = $seed % 360;
        mt_srand($seed);
        $body = '';
        if ($w === $h) {
            $body = "<path fill=\"#fff\" d=\"M50 12 88 50 50 88 12 50Z M50 34 66 50 50 66 34 50Z\" fill-rule=\"evenodd\"/>";
        } else {
            foreach (range(0, 8) as $i) {
                $bw = (int) ($w / 11);
                $bh = mt_rand((int) ($h * .18), (int) ($h * .62));
                $body .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="#fff" opacity=".%d"/>', (int) (($i + .5) * $w / 9 - $bw / 2), $h - $bh, $bw, $bh, mt_rand(12, 30));
            }
        }
        $box = $w === $h ? '0 0 100 100' : "0 0 {$w} {$h}";

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"{$box}\" width=\"{$w}\" height=\"{$h}\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\">"
            ."<stop offset=\"0\" stop-color=\"hsl({$hue} 45% 28%)\"/><stop offset=\"1\" stop-color=\"hsl(".(($hue + 40) % 360)." 50% 14%)\"/></linearGradient></defs>"
            ."<rect width=\"100%\" height=\"100%\" fill=\"url(#g)\"/>{$body}</svg>";
    }

    private function site(Language $lang, array $t): void
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

        $post = fn (string $key, Page $on, Category $cat, string $title, string $lead, ?string $image, int $order, array $extra = []) => Post::create($extra + [
            'language_id' => $lang->id, 'translation_key' => "demo-post-{$key}", 'page_id' => $on->id, 'category_id' => $cat->id,
            'title' => $title, 'slug' => $title, 'lead' => $lead, 'summary' => $lead, 'body' => "<p>{$t['sample']}</p>",
            'image_id' => $image ? $this->asset($image)->id : null, 'sort_order' => $order, 'status' => PublishStatus::Published,
            'published_at' => now()->subDays($order + 1),
        ]);
        foreach ($t['news_list'] as $i => [$title, $lead]) {
            $post("news-{$i}", $news, $cats[0], $title, $lead, 'news-'.($i + 1), $i);
        }
        foreach ($t['announcements_list'] as $i => [$title, $lead]) {
            $post("ann-{$i}", $news, $cats[1], $title, $lead, null, $i);
        }
        // Hero slides are posts of the slider category that live on the home page; each button leads to a real page.
        $slideTo = [$news, $companies, null, $about];
        foreach ($t['slides'] as $i => [$title, $lead]) {
            $post("slide-{$i}", $home, $cats[2], $title, $lead, 'hero-'.($i + 1), $i,
                ['is_featured' => true, 'link_url' => $slideTo[$i] ? PublicUrl::path($slideTo[$i]->path()) : "/{$lang->code}/badge"]);
        }

        foreach ($t['companies_list'] as $i => [$name, $industry]) {
            Company::create([
                'language_id' => $lang->id, 'translation_key' => "demo-company-{$i}", 'page_id' => $companies->id, 'name' => $name, 'slug' => $name,
                'logo_id' => $this->asset('company-'.($i + 1), 100, 100)->id, 'industry' => $industry, 'description' => "<p>{$t['sample']}</p>", 'sort_order' => $i, 'is_active' => true,
            ]);
        }

        $galleries = collect($t['galleries'])->map(function ($title, $g) use ($lang, $t) {
            $gallery = Gallery::create(['language_id' => $lang->id, 'title' => $title, 'is_active' => true, 'sort_order' => $g]);
            foreach (range(1, 10) as $i) {
                $gallery->items()->create(['media_id' => $this->asset("gallery-".($g + 1)."-{$i}", 1200, 800)->id, 'caption' => "{$title} — {$i}", 'sort_order' => $i]);
            }

            return $gallery;
        });

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

        // The header logo is the first link of the "logo" group (icon = picture, label = brand name).
        $logo = $group('logo', null, 0);
        $link($logo, $t['settings']['site_name'], null, 1, null, "/{$lang->code}")->update(['icon_media_id' => $this->asset('logo', 100, 100)->id, 'show_label' => true]);

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
        foreach ($t['organizers_list'] as $i => $name) {
            $link($organizers, $name, null, $i + 1, null, '#')->update(['icon_media_id' => $this->asset('organizer-'.($i + 1), 100, 100)->id]);
        }
        foreach ($t['supporters_list'] as $i => $name) {
            $link($supporters, $name, null, $i + 1, null, '#')->update(['icon_media_id' => $this->asset('supporter-'.($i + 1), 100, 100)->id]);
        }

        $footerA = $group('footer', $t['footer_a'], 5);
        $link($footerA, $t['about'], $about, 1);
        $link($footerA, $t['contact'], $contact, 2);
        $footerB = $group('footer', $t['footer_b'], 6);
        $link($footerB, $t['news'], $news, 1);
        $link($footerB, $t['companies'], $companies, 2);
        $link($footerB, $t['badge'], null, 3, null, $badge);

        // Home page sections in display order. The hero slider is the "posts / hero" module; the video band comes from the settings.
        $module = fn (string $type, ?string $title, string $display, int $limit, array $extra = []) => PageModule::create($extra + [
            'page_id' => $home->id, 'type' => $type, 'title' => $title, 'show_title' => $title !== null, 'display' => $display,
            'item_limit' => $limit, 'sort_order' => PageModule::where('page_id', $home->id)->count() + 1, 'is_active' => true,
        ]);

        $module('posts', null, 'hero', 4, ['category_id' => $cats[2]->id]);
        $module('content', $t['pillars_title'], 'grid', 1, ['css_class' => 'pillars-block', 'body' => $t['pillars']]);
        $module('posts', $t['latest'], 'grid', 4, ['source_page_id' => $news->id, 'category_id' => $cats[0]->id]);
        $module('posts', $t['announcements'], 'list', 4, ['source_page_id' => $news->id, 'category_id' => $cats[1]->id]);
        $module('links', $t['quick'], 'grid', 6, ['link_group_id' => $quick->id]);
        $module('companies', $t['companies_title'], 'grid', 8, ['source_page_id' => $companies->id]);
        foreach ($galleries as $gallery) {
            $module('gallery', $gallery->title, 'grid', 5, ['gallery_id' => $gallery->id]);
        }
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
            'meta' => 'توسعه نمایشگاه‌های بین‌المللی بنیس دیبا؛ اخبار، شرکت‌ها و محصولات نمایشگاه‌های ساختمان.',
            'settings' => ['site_name' => 'توسعه نمایشگاه‌های بین‌المللی بنیس دیبا', 'tagline' => 'درگاه یکپارچهٔ صنعت ساختمان', 'meta_description' => 'توسعه نمایشگاه‌های بین‌المللی بنیس دیبا؛ اخبار، شرکت‌ها و محصولات نمایشگاه‌های ساختمان.',
                'footer_about' => 'درگاه اطلاع‌رسانی توسعه نمایشگاه‌های بین‌المللی بنیس دیبا.', 'footer_dates' => 'تاریخ برگزاری: به‌زودی اعلام می‌شود', 'footer_venue' => 'محل برگزاری: به‌زودی اعلام می‌شود', 'copyright' => '© توسعه نمایشگاه‌های بین‌المللی بنیس دیبا',
                'home_video_title' => 'جایی که صنعت دیدار می‌کند', 'home_video_text' => 'درگاه یکپارچهٔ معرفی نمایشگاه، شرکت‌ها، محصولات و فرصت‌های همکاری در صنعت ساختمان.',
                'home_video_url' => '/video/atmosphere.mp4', 'home_video_poster_url' => '/video/atmosphere-poster.jpg'],
            'categories' => ['اخبار', 'اطلاعیه‌ها', 'اسلایدر صفحهٔ اصلی'],
            'news_list' => [['نمونهٔ خبر: معرفی بخش‌های نمایشگاه', 'متن کوتاه نمونه برای خبر نخست.'], ['نمونهٔ خبر: فراخوان حضور شرکت‌ها', 'متن کوتاه نمونه برای خبر دوم.'],
                ['نمونهٔ خبر: برنامهٔ بازدید متخصصان', 'متن کوتاه نمونه برای خبر سوم.'], ['نمونهٔ خبر: گزارش تصویری', 'متن کوتاه نمونه برای خبر چهارم.']],
            'announcements_list' => [['نمونهٔ اطلاعیه: ساعت بازدید', 'متن کوتاه نمونه برای اطلاعیه.'], ['نمونهٔ اطلاعیه: ثبت‌نام کارت سینه', 'متن کوتاه نمونه برای اطلاعیه.']],
            'slides' => [['معماری در مقیاس یادمانی', 'نشانه‌های معاصری که افق صنعت ساختمان را می‌سازند.'], ['میراث معماری ایران، بازتفسیر شده', 'زبانی کهن در معماری، ترجمه‌شده به نگاهی مدرن و بین‌المللی.'],
                ['طراحی، مهندسی و سازه', 'جایی که معماری بین‌المللی با مهندسی دقیق دیدار می‌کند.'], ['فضای نمایشگاه', 'محل دیدار صنعت: غرفه‌ها، محصولات و متخصصان.']],
            'galleries' => ['سالن اصلی', 'غرفه‌ها', 'نشست‌ها و کارگاه‌ها', 'بازدیدکنندگان'],
            'supporters_list' => ['فولاد مبارکه اصفهان', 'سیمان تهران', 'ذوب‌آهن اصفهان', 'کاشی الوند', 'شیشه همدان', 'گروه مپنا'],
            'organizers_list' => ['توسعه نمایشگاه‌های بین‌المللی بنیس دیبا'],
            'pillars_title' => 'یک درگاه برای صنعت ساختمان',
            'pillars' => '<ol><li><h3>نمایشگاه</h3><p>اطلاعات هر دورهٔ نمایشگاه.</p></li><li><h3>شرکت‌ها</h3><p>معرفی شرکت‌های حاضر در نمایشگاه.</p></li><li><h3>محصولات</h3><p>محصولاتی که هر شرکت عرضه می‌کند.</p></li><li><h3>فرصت‌های همکاری</h3><p>ارتباط و همکاری در سراسر صنعت ساختمان.</p></li></ol>',
            'companies_list' => [['ایران‌رادیاتور', 'تأسیسات حرارتی'], ['لوله و ماشین‌سازی ایران', 'لوله و اتصالات'], ['ایران‌ترانسفو', 'تجهیزات برق'], ['سیمان هگمتان', 'سیمان'],
                ['فولاد خوزستان', 'فولاد و ورق'], ['کاشی حافظ', 'کاشی و سرامیک'], ['کاشی سعدی', 'کاشی و سرامیک'], ['شیشه قزوین', 'شیشهٔ ساختمانی']],
            'latest' => 'آخرین اخبار', 'announcements' => 'اطلاعیه‌ها', 'quick' => 'دسترسی سریع', 'companies_title' => 'شرکت‌های حاضر',
            'tour' => 'تور مجازی', 'organizers' => 'برگزارکنندگان', 'supporters' => 'حامیان',
            'footer_a' => 'دربارهٔ ما', 'footer_b' => 'دسترسی', 'faq_title' => 'پرسش‌های متداول',
            'faq' => [['این پرسش نمونه است؟', 'بله؛ پاسخ‌ها را از پنل ویرایش کنید.'], ['چگونه کارت سینه بگیرم؟', 'از فرم «ثبت‌نام کارت سینه» اطلاعات را وارد کنید.'], ['اطلاعات رسمی کجا اعلام می‌شود؟', 'در همین سایت، بخش اخبار و اطلاعیه‌ها.']],
        ];
        $en = [
            'home' => 'Home', 'news' => 'News', 'companies' => 'Companies', 'about' => 'About', 'contact' => 'Contact', 'badge' => 'Badge registration',
            'about_body' => '<p>Sample text. Replace it with the official introduction of the exhibition.</p>', 'contact_body' => '<p>Official contact details are entered in the panel settings.</p>',
            'sample' => 'Sample text. Replace it with official content.',
            'meta' => 'Benis Fairs: news, companies and products of building exhibitions.',
            'settings' => ['site_name' => 'Benis Fairs', 'tagline' => 'One gateway to the building industry', 'meta_description' => 'Benis Fairs: news, companies and products of building exhibitions.',
                'footer_about' => 'Information portal of Benis Fairs.', 'footer_dates' => 'Dates: to be announced', 'footer_venue' => 'Venue: to be announced', 'copyright' => '© Benis Fairs',
                'home_video_title' => 'Where the industry meets', 'home_video_text' => 'A unified gateway to the exhibition, companies, products and business opportunities in the building industry.',
                'home_video_url' => '/video/atmosphere.mp4', 'home_video_poster_url' => '/video/atmosphere-poster.jpg'],
            'categories' => ['News', 'Announcements', 'Home slider'],
            'news_list' => [['Sample news: introducing the exhibition sections', 'Short sample text for the first news item.'], ['Sample news: call for exhibitors', 'Short sample text for the second news item.'],
                ['Sample news: visit programme for professionals', 'Short sample text for the third news item.'], ['Sample news: photo report', 'Short sample text for the fourth news item.']],
            'announcements_list' => [['Sample announcement: visiting hours', 'Short sample text for an announcement.'], ['Sample announcement: badge registration', 'Short sample text for an announcement.']],
            'slides' => [['Architecture at monumental scale', 'Contemporary landmarks that shape the skyline of the building industry.'], ['Iranian heritage, reinterpreted', 'A centuries-old architectural language, translated into a modern, international outlook.'],
                ['Design, engineering and structure', 'Where international architecture meets precise engineering.'], ['The exhibition floor', 'The meeting place of the industry: stands, products and professionals.']],
            'galleries' => ['Main hall', 'Stands', 'Sessions and workshops', 'Visitors'],
            'supporters_list' => ['Mobarakeh Steel Company', 'Tehran Cement Company', 'Esfahan Steel Company', 'Alvand Tile', 'Hamadan Glass', 'MAPNA Group'],
            'organizers_list' => ['Benis Fairs'],
            'pillars_title' => 'One gateway to the industry',
            'pillars' => '<ol><li><h3>The exhibition</h3><p>Information about each edition of the exhibition.</p></li><li><h3>Companies</h3><p>Profiles of the companies taking part.</p></li><li><h3>Products</h3><p>Products presented by each company.</p></li><li><h3>Business opportunities</h3><p>Contacts and cooperation across the building industry.</p></li></ol>',
            'companies_list' => [['Iran Radiator', 'Heating equipment'], ['Iran Tube & Machine Manufacturing', 'Pipes and fittings'], ['Iran Transfo', 'Electrical equipment'], ['Hegmatan Cement', 'Cement'],
                ['Khuzestan Steel', 'Steel and sheet'], ['Hafez Tile', 'Tiles and ceramics'], ['Saadi Tile', 'Tiles and ceramics'], ['Qazvin Glass', 'Architectural glass']],
            'latest' => 'Latest news', 'announcements' => 'Announcements', 'quick' => 'Quick links', 'companies_title' => 'Participating companies',
            'tour' => 'Virtual tour', 'organizers' => 'Organizers', 'supporters' => 'Supporters',
            'footer_a' => 'About', 'footer_b' => 'Explore', 'faq_title' => 'Frequently asked questions',
            'faq' => [['Is this a sample question?', 'Yes. Edit the answers in the panel.'], ['How do I get a badge?', 'Fill in the "Badge registration" form.'], ['Where are official details published?', 'Here on this site, in News and Announcements.']],
        ];

        return ['fa' => $fa, 'en' => $en];
    }
}
