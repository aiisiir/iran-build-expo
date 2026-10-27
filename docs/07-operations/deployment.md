# راهنمای استقرار — پیش‌نویس

## پیش‌شرط‌ها
- PHP 8.3+ و افزونه‌های رسمی مورد نیاز Laravel 13.
- Composer سازگار با `composer.json` و `composer.lock`.
- DB سازگار و دسترسی امن؛ SSL فعال.
- وب‌سرور به `public/` اشاره کند. **ریشهٔ کل پروژه را public نکن.**
- امکان تنظیم `APP_KEY`، متغیرهای محیطی، دسترسی `storage`/`bootstrap/cache`، لاگ و backup.
- برای کارهای دوره‌ای، queue یا ارسال SMS باید محدودیت Cron/process در هاست مشخص شود.

## Checklist
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` روی دامنهٔ canonical با HTTPS
- [ ] `APP_KEY` امن و منحصربه‌فرد
- [ ] secrets از Git خارج
- [ ] وب‌سرور فقط `public/` را سرو کند
- [ ] تنظیم DB و حداقل مجوز لازم
- [ ] `php artisan migrate --force` فقط پس از backup و بررسی migration تأییدشده
- [ ] `php artisan config:cache`, `route:cache`, `view:cache` طبق سازگاری و روش استقرار
- [ ] storage permissions امن و لینک فایل‌ها کنترل‌شده
- [ ] backup خارج از همان میزبان و تست restore
- [ ] ایمیل/SMS و خطاهای provider پایش شوند
- [ ] robots/sitemap production URL معتبر نشان دهد
- [ ] صفحات staging noindex بمانند

## محدودیت
تا زمانی که امکانات هاست مشخص نشده، این راهنما الزاماتی برای بررسی است، نه دستورالعمل production تأییدشده.
