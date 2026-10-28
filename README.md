# Iran Build Expo

سامانهٔ مدیریت و معرفی نمایشگاه‌های Iran Build Expo (Laravel 13 + Blade). مخزن محصول مستقل از `mainstructure` است.

**وضعیت:** فاز ۱ تا ۳ (تست استقرار، مستندات، طراحی دیتابیس، اسکلت پایه) انجام شد؛ **هنوز هیچ migration/ویژگی کسب‌وکاری ساخته نشده** (منتظر تأیید طراحی دیتابیس: `docs/03-domain/database-design.md`).

## تصمیم‌های قطعی
- Laravel 13 + Blade، رندر سمت سرور؛ PHP 8.4؛ MariaDB 10.4؛ بدون React/SPA و بدون Node در بیلد.
- آدرس‌ها `/fa/...` و `/en/...`؛ فونت‌ها self-host (Vazirmatn، Poppins)؛ تقویم جلالی (`App\Support\Jalali`).
- استقرار روی ساب‌دامین، بدون SSH: `docs/07-operations/deployment.md`.

## نصب محلی (XAMPP / PHP ≥ 8.4 و Composer)
1. دیتابیس `iran_build_expo` را با collation `utf8mb4_unicode_ci` بساز (phpMyAdmin).
2. در پوشهٔ پروژه:
```bash
composer install
cp .env.example .env        # در ویندوز: copy .env.example .env
php artisan key:generate
php artisan test
php artisan serve           # http://localhost:8000
```
3. `composer.lock` ساخته‌شده را حتماً commit کن (فعلاً در مخزن نیست).

> قفل `config.platform.php = 8.4.0` باعث می‌شود حتی با PHP 8.5 محلی، وابستگی‌ها برای سرور (PHP 8.4) انتخاب شوند.

## ساختار
- `app/` کد، `routes/web.php` مسیرها، `resources/views/` قالب‌ها (`errors/` صفحات خطا)، `lang/` ترجمه‌ها.
- `public/` ریشهٔ وب (`css/`, `fonts/`, `images/`)؛ `deploy/public_html/` فقط برای تست اولیهٔ هاست.
- `docs/` نیازمندی‌ها، معماری (ADR)، دیتابیس، امنیت، تست، استقرار، `AI_Learns.md`.

## اصول غیرقابل‌مذاکره
مجوزدهی سمت سرور؛ جداسازی داده‌های شرکت‌ها؛ رزرو غرفه با تراکنش + قید دیتابیس؛ QR بدون اطلاعات شخصی؛ مبالغ ریال عدد صحیح؛ لاگ بدون رمز/توکن؛ ادعای موفقیت تست فقط با خروجی واقعی.

## همکاری با Git (برای مالک)
Claude روی branch `claude` کار می‌کند. در GitHub: Pull requests → PR را باز کن → **Merge pull request**. بعد روی کامپیوتر: `git pull`.
