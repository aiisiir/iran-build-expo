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
 * The complete first content of the site, in fa and en, built only from panel records: logo, menu, hero slide, quick actions, partner logos,
 * about panel (settings), document cards, news, visitor-registration banner, gallery, companies, FAQ, footer. Nothing on the home page is written in a template.
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
                Edition::create(['code' => 'demo', 'name' => 'نمایشگاه بین‌المللی ساختمان ایران — دورهٔ نمونه'])->claimFlag('is_current');
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
        $code = $lang->code;
        $page = fn (string $key, string $title, ?string $body = null, bool $home = false) => tap(Page::create([
            'language_id' => $lang->id, 'translation_key' => "demo-{$key}", 'title' => $title, 'slug' => $title, 'body' => $body,
            'meta_description' => $t['meta'], 'status' => PublishStatus::Published, 'published_at' => now()->subDay(),
        ]), fn (Page $p) => $home ? $p->claimFlag('is_home', ['language_id' => $lang->id]) : null);

        $home = $page('home', $t['home'], null, true);
        $about = $page('about', $t['about'], $t['about_body']);
        $exhibitors = $page('exhibitors', $t['exhibitors'], $t['exhibitors_body']);
        $news = $page('news', $t['news']);
        $archive = $page('archive', $t['archive'], $t['archive_body']);
        $services = $page('services', $t['services'], $t['services_body']);
        $companies = $page('companies', $t['companies']);
        $contact = $page('contact', $t['contact'], $t['contact_body']);
        $badge = "/{$code}/badge";

        SiteSettings::save($lang->id, $t['settings']);

        // News: three categories shown as the chip on each card.
        $cats = collect($t['categories'])->map(fn ($name, $i) => Category::create([
            'language_id' => $lang->id, 'translation_key' => "demo-cat-{$i}", 'type' => 'post', 'name' => $name, 'slug' => $name, 'sort_order' => $i,
        ]))->values();

        $post = fn (string $key, Page $on, Category $cat, string $title, string $lead, ?string $image, int $order, array $extra = []) => Post::create($extra + [
            'language_id' => $lang->id, 'translation_key' => "demo-post-{$key}", 'page_id' => $on->id, 'category_id' => $cat->id,
            'title' => $title, 'slug' => $title, 'lead' => $lead, 'summary' => $lead, 'body' => "<p>{$lead}</p><p>{$t['sample']}</p>",
            'image_id' => $image ? $this->asset($image)->id : null, 'sort_order' => $order, 'status' => PublishStatus::Published,
            'published_at' => now()->subDays($order * 3 + 1),
        ]);
        foreach ($t['news_list'] as $i => [$title, $lead, $cat]) {
            $post("news-{$i}", $news, $cats[$cat], $title, $lead, 'news-'.(($i % 4) + 1), $i);
        }
        // The hero slide is a post of the home page: title = bold line, lead = light line, summary = text, source = button label.
        [$slideTitle, $slideLead, $slideText, $slideButton] = $t['hero'];
        $post('slide-0', $home, $cats[0], $slideTitle, $slideLead, 'hero-1', 0, ['summary' => $slideText, 'is_featured' => true, 'link_url' => $badge, 'source_name' => $slideButton]);

        foreach ($t['companies_list'] as $i => [$name, $industry]) {
            Company::create([
                'language_id' => $lang->id, 'translation_key' => "demo-company-{$i}", 'page_id' => $companies->id, 'name' => $name, 'slug' => $name,
                'logo_id' => $this->asset('company-'.($i + 1), 100, 100)->id, 'industry' => $industry, 'description' => "<p>{$t['sample']}</p>", 'sort_order' => $i, 'is_active' => true,
            ]);
        }

        $gallery = Gallery::create(['language_id' => $lang->id, 'title' => $t['gallery_title'], 'is_active' => true, 'sort_order' => 0]);
        foreach ($t['photos'] as $i => $caption) {
            $gallery->items()->create(['media_id' => $this->asset('gallery-1-'.($i + 1), 1200, 800)->id, 'caption' => $caption, 'sort_order' => $i + 1]);
        }

        foreach ($t['faq'] as $i => [$q, $a]) {
            Faq::create(['language_id' => $lang->id, 'question' => $q, 'answer' => "<p>{$a}</p>", 'sort_order' => $i, 'is_active' => true]);
        }

        // One mechanism for menu, quick actions, documents, footer: link groups.
        $group = fn (string $placement, ?string $title, int $order, bool $showTitle = true) => LinkGroup::create([
            'language_id' => $lang->id, 'placement' => $placement, 'title' => $title, 'show_title' => $title !== null && $showTitle, 'sort_order' => $order, 'is_active' => true,
        ]);
        $link = fn (LinkGroup $g, string $label, ?Page $to, int $order, ?string $url = null, array $extra = []) => Link::create($extra + [
            'link_group_id' => $g->id, 'label' => $label, 'type' => $to ? 'page' : 'url', 'page_id' => $to?->id,
            'url' => $url, 'show_label' => true, 'sort_order' => $order, 'is_active' => true,
        ]);

        // The header logo is the first link of the "logo" group (icon = picture, label = brand name).
        $logo = $group('logo', null, 0);
        $link($logo, $t['settings']['site_name'], null, 1, "/{$code}", ['icon_media_id' => $this->asset('logo', 112, 36)->id]);

        $menu = $group('menu', null, 1);
        foreach ([$home, $about, $exhibitors, $news, $archive, $services, $contact] as $i => $to) {
            $link($menu, $to->title, $to, $i + 1);
        }
        $link($menu, $t['badge'], null, 8, $badge, ['css_class' => 'button button--green nav-cta']);

        // Quick actions in the hero strip (module class "in-hero") and the two links of the about panel ("in-about").
        $quick = $group('quick_links', $t['quick'], 2, false);
        foreach ($t['quick_list'] as $i => [$label]) {
            $to = [null, $exhibitors, $companies][$i];
            $link($quick, $label, $to, $i + 1, $to ? null : $badge, ['icon_media_id' => $this->asset('shortcut-'.($i + 1), 100, 100)->id]);
        }
        $features = $group('quick_links', $t['features'], 3, false);
        foreach ($t['features_list'] as $i => [$label, $text]) {
            $link($features, $label, [$about, $services][$i], $i + 1, null, ['body' => "<p>{$text}</p>", 'icon_media_id' => $this->asset('feature-'.($i + 1), 100, 100)->id]);
        }

        // Document cards: file = what is downloaded, picture = preview, "title" attribute = button text, CSS class = header icon.
        $docs = $group('quick_links', $t['docs'], 4, false);
        foreach ($t['docs_list'] as $i => [$label, $text, $button]) {
            $doc = $this->asset('doc-'.($i + 1), 438, 290);
            $link($docs, $label, null, $i + 1, null, ['type' => 'file', 'file_media_id' => $doc->id, 'icon_media_id' => $doc->id, 'title_attr' => $button, 'css_class' => ['image', 'map', 'users'][$i], 'body' => "<p>{$text}</p>"]);
        }

        $footerA = $group('footer', $t['footer_a'], 5);
        foreach ([$archive, $about, $companies, $contact] as $i => $to) {
            $link($footerA, $t['footer_a_list'][$i], $to, $i + 1);
        }
        $link($footerA, $t['footer_a_list'][4], null, 5, "/{$code}#gallery");
        $footerB = $group('footer', $t['footer_b'], 6);
        foreach ([$about, $exhibitors, $news, $archive, $services, $contact] as $i => $to) {
            $link($footerB, $t['footer_b_list'][$i], $to, $i + 1);
        }

        // Home page sections in display order (see the reference design). Slots: "in-hero" and "in-about" modules are drawn inside those blocks.
        $module = fn (string $type, ?string $title, string $display, int $limit, array $extra = []) => PageModule::create($extra + [
            'page_id' => $home->id, 'type' => $type, 'title' => $title, 'show_title' => $title !== null, 'display' => $display,
            'item_limit' => $limit, 'sort_order' => PageModule::where('page_id', $home->id)->count() + 1, 'is_active' => true,
        ]);

        $module('posts', null, 'hero', 1);
        $module('links', $t['quick'], 'grid', 3, ['css_class' => 'in-hero', 'link_group_id' => $quick->id]);
        $module('content', $t['partners'], 'grid', 1, ['css_class' => 'in-hero', 'media_id' => $this->asset('partners', 266, 66)->id]);
        $module('links', $t['features'], 'grid', 2, ['css_class' => 'in-about', 'show_title' => false, 'link_group_id' => $features->id]);
        $module('links', $t['docs_title'], 'grid', 3, ['css_class' => 'resources-section', 'link_group_id' => $docs->id, 'body' => $t['docs_intro']]);
        $module('posts', $t['news_title'], 'slider', 6, ['css_class' => 'news-section', 'source_page_id' => $news->id, 'body' => $t['news_intro']]);
        $module('content', $t['visitor_title'], 'grid', 1, ['css_class' => 'registration-section', 'media_id' => $this->asset('visitor-pass', 538, 438)->id, 'body' => $t['visitor_body']]);
        $module('gallery', $t['gallery_module'], 'grid', 10, ['css_class' => 'gallery-section', 'gallery_id' => $gallery->id, 'css_id' => 'gallery', 'body' => $t['gallery_intro']]);

        // Inner pages: the about page carries the FAQ.
        PageModule::create(['page_id' => $about->id, 'type' => 'faq', 'title' => $t['faq_title'], 'show_title' => true, 'display' => 'list', 'item_limit' => 8, 'sort_order' => 1, 'is_active' => true]);
    }

    /** @return array<string, array<string, mixed>> */
    private function texts(): array
    {
        $fa = [
            'home' => 'خانه', 'news' => 'اخبار', 'companies' => 'مشارکت‌کنندگان', 'about' => 'درباره ما', 'contact' => 'ارتباط با ما',
            'exhibitors' => 'ثبت نام مشارکت‌کنندگان', 'archive' => 'آرشیو دوره‌های قبل', 'services' => 'خدمات نمایشگاهی', 'badge' => 'سامانه ثبت نام',
            'about_body' => '<p>شرکت توسعه نمایشگاه‌های بین‌المللی بنیس در سال ۱۳۸۹ با هدف برگزاری رویدادهای تجاری من جمله نمایشگاه و کنفرانس، در سطوح ملی و بین‌المللی، در ایران تأسیس شد. این مجموعه با تکیه بر معرفی بازارهای مستعد داخلی و خارجی و با بهره‌گیری از کادری مجرب موفق به برگزاری رویدادهای مختلفی در ایران و خارج از آن شده است.</p><h2>حوزه‌های فعالیت</h2><ul><li>برگزاری نمایشگاه: اطلاعات رویداد، مسیرهای مشارکت و برنامه‌ریزی برای حضور در نمایشگاه.</li><li>مشارکت در نمایشگاه: دسترسی به فرم ثبت درخواست، مدارک و اطلاعات نمایشگاه.</li><li>اخذ مجوز غرفه‌سازی: مسیر ثبت درخواست غرفه و بررسی اطلاعات موردنیاز مشارکت‌کننده.</li></ul>',
            'exhibitors_body' => '<p>برای مشارکت در نمایشگاه، اطلاعات شرکت و نیازهای غرفه‌ی خود را برای دبیرخانه ارسال کنید. این متن نمونه است و باید با راهنمای رسمی مشارکت جایگزین شود.</p>',
            'archive_body' => '<p>گزارش و تصاویر دوره‌های گذشته در این بخش منتشر می‌شود. این متن نمونه است و باید با محتوای رسمی جایگزین شود.</p>',
            'services_body' => '<p>برگزاری نمایشگاه، مشارکت در نمایشگاه، اخذ مجوز غرفه‌سازی، بازدید از نمایشگاه، مدارک و اطلاعات، ارتباط و راهنمایی؛ همه مسیرهای موردنیاز برای مشارکت، بازدید و دسترسی به اطلاعات نمایشگاه.</p>',
            'contact_body' => '<p>برای دریافت راهنمایی درباره بازدید، مشارکت یا مدارک نمایشگاه با دبیرخانه در ارتباط باشید. اطلاعات تماس رسمی از بخش تنظیمات پنل وارد می‌شود.</p>',
            'sample' => 'این متن نمونه است و باید با محتوای رسمی جایگزین شود.',
            'meta' => 'توسعه نمایشگاه‌های بین‌المللی بنیس دیبا؛ برگزاری نمایشگاه‌ها، سمینارها و کنفرانس‌ها. اطلاعات نمایشگاه، اخبار، گالری و خدمات مشارکت‌کنندگان و بازدیدکنندگان.',
            'settings' => [
                'site_name' => 'توسعه نمایشگاه‌های بین‌المللی بنیس دیبا', 'tagline' => 'برگزاری نمایشگاه‌ها، سمینارها و کنفرانس‌ها', 'brand_caption' => 'INTERNATIONAL EXHIBITIONS',
                'header_contact_title' => 'همراه شما در مسیر برگزاری رویداد', 'header_contact_text' => 'برای اطلاع از رویدادها با ما در تماس باشید',
                'meta_description' => 'توسعه نمایشگاه‌های بین‌المللی بنیس دیبا؛ برگزاری نمایشگاه‌ها، سمینارها و کنفرانس‌ها.',
                'footer_about' => 'برگزاری نمایشگاه‌ها، سمینارها و کنفرانس‌ها', 'footer_dates' => 'تاریخ برگزاری: به‌زودی اعلام می‌شود', 'footer_venue' => 'محل برگزاری: به‌زودی اعلام می‌شود',
                'copyright' => 'کلیه حقوق این وب‌سایت متعلق به بنیس دیبا است.',
                'home_video_title' => 'درباره ما',
                'home_video_text' => 'شرکت توسعه نمایشگاه‌های بین‌المللی بنیس در سال ۱۳۸۹ با هدف برگزاری رویدادهای تجاری من جمله نمایشگاه و کنفرانس، در سطوح ملی و بین‌المللی، در ایران تأسیس شد. این مجموعه با تکیه بر معرفی بازارهای مستعد داخلی و خارجی و با بهره‌گیری از کادری مجرب موفق به برگزاری رویدادهای مختلفی در ایران و خارج از آن شده است…',
                'home_video_url' => '/video/atmosphere.mp4', 'home_video_poster_url' => '/uploads/demo/gallery-1-2.webp',
            ],
            'categories' => ['خبر نمایشگاه', 'اطلاعیه', 'گزارش ویژه'],
            'hero' => ['برگزاری نمایشگاه‌ها', 'سمینارها و کنفرانس‌ها', 'معرفی بازارهای مستعد داخلی و خارجی و با بهره‌گیری از کادری مجرب', 'سامانه ثبت نام'],
            'news_list' => [
                ['معرفی آثار برجسته شرکت‌کنندگان', 'منتخبی از زیباترین و ارزشمندترین آثار شرکت‌کنندگان امسال معرفی شد.', 0],
                ['اعلام برنامه افتتاحیه نمایشگاه', 'مراسم افتتاحیه نمایشگاه بین‌المللی با حضور مقامات و فعالان صنعت برگزار می‌شود.', 1],
                ['گزارشی از کارگاه‌های زنده', 'در کارگاه‌های زنده نمایشگاه، فرایند ساخت و بافت آثار از نزدیک به نمایش گذاشته شد.', 2],
                ['آغاز ثبت‌نام بازدیدکنندگان', 'بازدیدکنندگان می‌توانند پیش از حضور در نمایشگاه اطلاعات خود را تکمیل کنند.', 1],
                ['نشست‌های تخصصی نمایشگاه', 'برنامه‌ی نشست‌ها و کارگاه‌های تخصصی هم‌زمان با نمایشگاه اعلام شد.', 0],
                ['میزبانی از هیئت‌های خارجی', 'هیئت‌هایی از کشورهای مختلف برای آشنایی با بازار و مشارکت‌کنندگان به نمایشگاه می‌آیند.', 2],
            ],
            'companies_list' => [['ایران‌رادیاتور', 'تأسیسات حرارتی'], ['لوله و ماشین‌سازی ایران', 'لوله و اتصالات'], ['ایران‌ترانسفو', 'تجهیزات برق'], ['سیمان هگمتانه', 'سیمان'],
                ['فولاد خوزستان', 'فولاد و ورق'], ['کاشی حافظ', 'کاشی و سرامیک'], ['کاشی سعدی', 'کاشی و سرامیک'], ['شیشه قزوین', 'شیشهٔ ساختمانی']],
            'gallery_title' => 'گالری نمایشگاه',
            'photos' => ['هنر قالیبافی؛ از تار تا پود', 'نمایی از سالن نمایشگاه', 'نقش و رنگ در فرش ایرانی', 'بازدید از آثار هنری', 'ظرافت هنر ایرانی', 'هنر دست و نقاشی روی سفال', 'بازدیدکنندگان در نمایشگاه', 'رنگ‌های قالی ایرانی', 'نمای بیرونی محل نمایشگاه', 'جزئیات بافت و نقش'],
            'quick' => 'دسترسی سریع', 'quick_list' => [['سامانه ثبت نام', null], ['اخذ مجوز غرفه‌سازی', null], ['مشارکت‌کنندگان', null]], 'partners' => 'نشان‌های بین‌المللی نمایشگاهی',
            'features' => 'درباره ما', 'features_list' => [['گواهینامه‌ها', 'معرفی توانمندی‌های نمایشگاهی'], ['خدمات', 'خدمات تخصصی نمایشگاهی']],
            'docs' => 'مدارک', 'docs_title' => 'مدارک و اطلاعات کاربردی نمایشگاه',
            'docs_intro' => '<h3>اطلاعات نمایشگاه</h3><p>دسترسی سریع به لیست مشارکت‌کنندگان، نقشه محل برگزاری و پوستر رسمی نمایشگاه</p>',
            'docs_list' => [['پوستر نمایشگاه', 'پوستر رسمی و اطلاع‌رسانی نمایشگاه', 'دانلود پوستر'], ['نقشه محل برگزاری', 'پلان غرفه‌ها و مسیرهای دسترسی به بخش‌های مختلف', 'دانلود نقشه'], ['لیست مشارکت‌کنندگان', 'فهرست کامل شرکت‌ها و برندهای حاضر در نمایشگاه', 'دانلود فایل']],
            'news_title' => 'جدیدترین اخبار و اطلاعیه‌های نمایشگاه', 'news_intro' => '<h3>آخرین اخبار</h3><p>رویدادها، اعلامیه‌ها و تازه‌ترین خبرهای مرتبط با نمایشگاه را دنبال کنید</p>',
            'visitor_title' => 'ثبت نام بازدیدکنندگان',
            'visitor_body' => '<h3>توسعه نمایشگاه‌های بین‌المللی بنیس دیبا</h3><h4>فرصتی برای دیدار، شناخت و ارتباط</h4><p>ثبت‌نام پیش از بازدید از نمایشگاه</p><p>برای حضور در نمایشگاه و آشنایی با آثار و مشارکت‌کنندگان،<br>اطلاعات بازدید خود را تکمیل کنید.</p><p><a href="/fa/badge">کلیک کنید</a></p>',
            'gallery_module' => 'تصاویری از حال و هوای نمایشگاه', 'gallery_intro' => '<h3>گالری تصاویر</h3><p>مجموعه‌ای از لحظات برگزیده، غرفه‌ها، آثار و بازدیدکنندگان</p>',
            'footer_a' => 'دسترسی سریع', 'footer_a_list' => ['نمایشگاه در یک نگاه', 'درباره برگزارکننده', 'مشارکت‌کنندگان', 'محل برگزاری', 'گالری تصاویر'],
            'footer_b' => 'لینک‌های سریع', 'footer_b_list' => ['درباره نمایشگاه', 'ثبت نام مشارکت‌کنندگان', 'اخبار', 'آرشیو دوره‌های قبل', 'خدمات نمایشگاهی', 'ارتباط با ما'],
            'faq_title' => 'پرسش‌های راهنما',
            'faq' => [['چگونه درخواست مشارکت را آماده کنم؟', 'در بخش ثبت‌نام مشارکت‌کنندگان، اطلاعات فردی و مجموعه را وارد کنید.'], ['نقشه و مدارک نمایشگاه کجاست؟', 'در بخش «مدارک و اطلاعات» صفحه‌ی اصلی؛ پوستر، نقشه و فهرست مشارکت‌کنندگان.'], ['چگونه کارت بازدید بگیرم؟', 'از «سامانه ثبت نام» اطلاعات را وارد کنید و کارت خود را دریافت کنید.']],
        ];
        $en = [
            'home' => 'Home', 'news' => 'News', 'companies' => 'Exhibitors', 'about' => 'About us', 'contact' => 'Contact',
            'exhibitors' => 'Exhibitor registration', 'archive' => 'Previous editions', 'services' => 'Exhibition services', 'badge' => 'Register to visit',
            'about_body' => '<p>Benis International Exhibition Development was established in Iran in 2010 to organise trade events, exhibitions and conferences at national and international level. Its work brings together emerging markets, experienced organisers and exhibition communities.</p><h2>Areas of work</h2><ul><li>Organising exhibitions: event information, ways to take part and planning your presence.</li><li>Taking part: the application form, documents and exhibition information.</li><li>Stand-building permits: how to apply and what to prepare.</li></ul>',
            'exhibitors_body' => '<p>To take part in the exhibition, send your company details and stand requirements to the secretariat. Sample text: replace it with the official guide.</p>',
            'archive_body' => '<p>Reports and photographs of previous editions are published here. Sample text: replace it with official content.</p>',
            'services_body' => '<p>Organising exhibitions, taking part, stand-building permits, visiting, documents and information, support: every route you need to take part, visit and find exhibition information.</p>',
            'contact_body' => '<p>Contact the secretariat for help with visiting, taking part or exhibition documents. Official contact details are entered in the panel settings.</p>',
            'sample' => 'Sample text. Replace it with official content.',
            'meta' => 'Benis Fairs: exhibitions, seminars and conferences. Exhibition information, news, gallery and services for exhibitors and visitors.',
            'settings' => [
                'site_name' => 'Benis Fairs', 'tagline' => 'Exhibitions, seminars & conferences', 'brand_caption' => 'INTERNATIONAL EXHIBITIONS',
                'header_contact_title' => 'With you on the way to the event', 'header_contact_text' => 'Talk to our exhibition team',
                'meta_description' => 'Benis Fairs: exhibitions, seminars and conferences.',
                'footer_about' => 'Bringing exhibitions, ideas and people together.', 'footer_dates' => 'Dates: to be announced', 'footer_venue' => 'Venue: to be announced',
                'copyright' => '© All rights reserved for Benis Fairs.',
                'home_video_title' => 'About Benis Fairs',
                'home_video_text' => 'Benis International Exhibition Development was established in Iran in 2010 to organise trade events, exhibitions and conferences at national and international level. Its work brings together emerging markets, experienced organisers and exhibition communities.',
                'home_video_url' => '/video/atmosphere.mp4', 'home_video_poster_url' => '/uploads/demo/gallery-1-2.webp',
            ],
            'categories' => ['Exhibition news', 'Announcement', 'Special report'],
            'hero' => ['International exhibitions', 'Seminars & conferences', 'Connecting local and international markets through an experienced team.', 'Register to visit'],
            'news_list' => [
                ['Discover the exhibitors’ standout works', 'A selection of the craftsmanship and works featured in the exhibition.', 0],
                ['Exhibition opening programme', 'An introduction to the opening programme and the exhibition community.', 1],
                ['Inside the live workshops', 'A closer look at the process and skill behind the works on show.', 2],
                ['Visitor registration is open', 'Visitors can prepare their information before arriving at the exhibition.', 1],
                ['Specialist sessions announced', 'The programme of sessions and workshops running alongside the exhibition.', 0],
                ['Welcoming international delegations', 'Delegations from several countries visit to meet the market and exhibitors.', 2],
            ],
            'companies_list' => [['Iran Radiator', 'Heating equipment'], ['Iran Tube & Machine Manufacturing', 'Pipes and fittings'], ['Iran Transfo', 'Electrical equipment'], ['Hegmatan Cement', 'Cement'],
                ['Khuzestan Steel', 'Steel and sheet'], ['Hafez Tile', 'Tiles and ceramics'], ['Saadi Tile', 'Tiles and ceramics'], ['Qazvin Glass', 'Architectural glass']],
            'gallery_title' => 'Exhibition gallery',
            'photos' => ['The art of carpet weaving', 'Inside the exhibition hall', 'Persian carpet patterns', 'Discovering craftsmanship', 'Persian decorative arts', 'Hand-painted ceramics', 'Visitors at the exhibition', 'The colours of Persian weaving', 'The exhibition venue', 'Details of a Persian carpet'],
            'quick' => 'Quick actions', 'quick_list' => [['Visitor registration', null], ['Booth services', null], ['Exhibitors', null]], 'partners' => 'International exhibition marks',
            'features' => 'About us', 'features_list' => [['Credentials', 'Exhibition experience'], ['Our services', 'Specialist exhibition services']],
            'docs' => 'Documents', 'docs_title' => 'Everything you need for the exhibition',
            'docs_intro' => '<h3>Exhibition information</h3><p>Quick access to exhibitor information, the venue plan and exhibition artwork.</p>',
            'docs_list' => [['Exhibition poster', 'Exhibition artwork and information', 'Download poster'], ['Venue floor plan', 'Stands, halls and access routes', 'Download map'], ['Exhibitor directory', 'Companies and brands at the exhibition', 'Download file']],
            'news_title' => 'News and stories from the exhibition', 'news_intro' => '<h3>Latest news</h3><p>Follow exhibition stories, announcements and moments from the show.</p>',
            'visitor_title' => 'Visitor registration',
            'visitor_body' => '<h3>Benis International Exhibition Development</h3><h4>A place to meet, discover and connect</h4><p>Plan your exhibition visit</p><p>Explore the exhibition and its participants.<br>Prepare your visitor information in advance.</p><p><a href="/en/badge">Register to visit</a></p>',
            'gallery_module' => 'The exhibition, in pictures', 'gallery_intro' => '<h3>Photo gallery</h3><p>A collection of moments, stands, craftsmanship and visitors.</p>',
            'footer_a' => 'Quick access', 'footer_a_list' => ['The exhibition at a glance', 'About the organiser', 'Exhibitors', 'Venue', 'Photo gallery'],
            'footer_b' => 'Exhibition guide', 'footer_b_list' => ['About Benis Fairs', 'Exhibitor registration', 'Exhibition news', 'Previous editions', 'Exhibition services', 'Contact us'],
            'faq_title' => 'Guide questions',
            'faq' => [['How do I prepare my application to take part?', 'In exhibitor registration, enter your personal and company details.'], ['Where are the map and documents?', 'In the "Everything you need" section of the home page: poster, plan and exhibitor list.'], ['How do I get a visitor card?', 'Use "Register to visit", fill in your details and receive your card.']],
        ];

        return ['fa' => $fa, 'en' => $en];
    }
}
