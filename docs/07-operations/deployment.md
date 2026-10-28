# راهنمای استقرار (هاست اشتراکی، بدون SSH)

**وضعیت:** تصمیم‌های هاست تأیید شده؛ مراحل ۱ تا ۳ فقط پس از اجرای واقعی توسط مالک «تأییدشده» حساب می‌شوند.

## واقعیت‌های هاست (تأییدشده توسط مالک، ۲۰۲۶-۱۰-۲۸)
- DirectAdmin؛ **بدون SSH/ترمینال/Composer** روی سرور.
- PHP 8.4 در دسترس؛ دیتابیس MariaDB **10.4.34** (phpMyAdmin دارد).
- آدرس نهایی: `https://iranbuildexpo.mahdiarmansouri.ir` (ساب‌دامین، SSL رایگان).
- ساختار: `domains/iranbuildexpo.mahdiarmansouri.ir/` شامل `public_html/` و پوشهٔ هم‌سطح `app/`.
- Migration روی سرور اجرا نمی‌شود: دیتابیس روی لوکال ساخته و تست می‌شود و **فایل SQL** با phpMyAdmin ایمپورت می‌شود.
- صف (queue) و cron لازم نیست؛ `QUEUE_CONNECTION=sync`.

## ساختار روی سرور
```
domains/iranbuildexpo.mahdiarmansouri.ir/
├── app/          ← کل پروژه (.env، vendor، storage، …) — بیرون از دسترس وب
└── public_html/  ← فقط محتوای پوشهٔ public/ پروژه
```
`public/index.php` خودش تشخیص می‌دهد که `app/` هم‌سطح است؛ نیازی به ویرایش دستی نیست.

## مرحلهٔ ۱ — تنظیم هاست (یک‌بار)
1. در DirectAdmin ساب‌دامین `iranbuildexpo` را بساز.
2. SSL (Let's Encrypt) را برای ساب‌دامین فعال کن.
3. نسخهٔ PHP ساب‌دامین را روی **8.4** بگذار.
4. یک دیتابیس و کاربر بساز (رمز قوی، مجوز فقط روی همان دیتابیس) و نام/رمز را برای `.env` نگه دار.

## مرحلهٔ ۲ — تست محیط (فاز ۱)
1. محتوای پوشهٔ `deploy/public_html/` را در `public_html/` آپلود کن؛ پوشهٔ خالی `app` را هم‌سطح آن بساز.
2. آدرس‌ها را باز کن: `/` (صفحهٔ ساده)، `/check.php`، `/rewrite-test`.
3. خروجی `check.php` را برای Claude بفرست؛ **سپس `check.php` را حذف کن**.

## مرحلهٔ ۳ — استقرار واقعی (از فاز ۳ به بعد)
روی کامپیوتر خودت:
1. `composer install --no-dev --optimize-autoloader` (قفل روی PHP 8.4 است).
2. `php artisan key:generate --show` → کلید **جدید** برای production (از کلید لوکال استفاده نکن).
3. پوشهٔ پروژه را zip کن **بدون**: `.env`، `.git`، `tests`، `node_modules`، `storage/logs/*`.
4. zip را در File Manager داخل `app/` آپلود و Extract کن.
5. محتوای `app/public/` را به `public_html/` منتقل کن (پوشهٔ `public` داخل `app` اضافه است؛ پاک شود).
6. در `app/` فایل `.env` را از `.env.production.example` بساز و مقدارها را پر کن.
7. فایل SQL را با phpMyAdmin ایمپورت کن.
8. `storage/` و `bootstrap/cache/` باید برای PHP قابل نوشتن باشند (معمولاً 755 یا 775).
9. `php artisan config:cache` و مشابه آن را **روی لوکال اجرا نکن** (مسیرهای لوکال داخل کش می‌رود).

## چک‌لیست production
- [ ] `APP_ENV=production`، `APP_DEBUG=false`، `APP_URL=https://iranbuildexpo.mahdiarmansouri.ir`
- [ ] `SESSION_SECURE_COOKIE=true`، `SESSION_DOMAIN` خالی
- [ ] `check.php` حذف شده؛ `.env` از وب قابل دانلود نیست (تست: `/.env` باید 403/404 بدهد)
- [ ] `robots.txt` و `sitemap.xml` آدرس production را نشان می‌دهند
- [ ] بکاپ دیتابیس خارج از سرور و تست بازیابی

## ریسک‌های شناخته‌شده (پایلوت؛ طبق تصمیم مالک مانع نیستند، ثبت شده‌اند)
1. **MariaDB 10.4 از ۱۸ ژوئن ۲۰۲۴ پایان پشتیبانی (EOL) شده** و وصلهٔ امنیتی نمی‌گیرد؛ در صورت امکان درخواست ارتقا (10.11+) به هاست.
2. هاست اشتراکی: پروژه‌های دیگر روی همان سرور/حساب ممکن است ریسک همسایگی ایجاد کنند.
3. بدون SSH: اشکال‌زدایی فقط با لاگ‌ها و File Manager؛ هر تغییر دیتابیس فقط با SQL دستی.
4. نبود queue/cron: کارهای زمان‌دار (مثل انقضای رزرو) باید lazy و هنگام درخواست حساب شوند.
