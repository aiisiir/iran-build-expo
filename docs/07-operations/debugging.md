# عیب‌یابی خطای ۵۰۰ (دستی)

سایت در production عمداً متن خطا را به بازدیدکننده نشان نمی‌دهد (امنیت). متن خطا در لاگ ذخیره می‌شود. سه روش، از امن‌تر به کم‌امن‌تر:

## ۱) لاگ سرور (امن، اولین کار)
- فایل: `app/storage/logs/` — اگر `LOG_STACK=daily` است، فایل `laravel-تاریخ-امروز.log`؛ اگر `single` است، `laravel.log` (خطای جدید **ته فایل** است، نه ابتدا).
- ته فایل را بخوان و آخرین خط `production.ERROR` را پیدا کن؛ جملهٔ اول آن علت است (مثلاً `Call to undefined relationship`، `SQLSTATE...`).
- خطا را با ساعت دقیق بلافاصله بعد از تکرار مشکل پیدا کن.

## ۲) `diag-admin.php` (امن، بدون دیدن رمز)
1. فایل `deploy/public_html/diag-admin.php` را در `public_html/` بگذار.
2. باز کن: `https://سایت/diag-admin.php?path=/fa/badge` (به‌جای `/fa/badge` هر صفحهٔ دیگر؛ برای پنل `?path=/admin/r/faqs`).
3. خروجی «status» و نام و پیام خطا را می‌دهد.
4. **بعد از استفاده حذفش کن.**

## ۳) نمایش موقت خطا در مرورگر (فقط اگر دو روش بالا کمک نکرد)
1. `SITE_INDEXABLE=false` را مطمئن شو.
2. در `app/.env`: `APP_DEBUG=true`.
3. صفحه را باز کن و متن خطا را بخوان. **ممکن است مسیرها و بخش‌هایی از کد و کوئری‌ها در صفحه دیده شوند.**
4. بلافاصله `APP_DEBUG=false` کن.

## اگر خطا در لاگ لاراول نیست
یعنی از خود Apache یا PHP است: در DirectAdmin بخش «Error Log» دامنه یا فایل `error_log` را در `public_html` ببین.

## دو خطای رایج
- `Field 'id' doesn't have a default value` ← جدول‌ها AUTO_INCREMENT ندارند (Import ناقص؛ از `mysqldump` استفاده کن).
- `Call to undefined relationship` ← کد قدیمی روی هاست؛ آخرین نسخه را آپلود کن.
