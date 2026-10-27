# راهبرد تست

## لایه‌ها
- **Unit:** محاسبهٔ مبالغ، قواعد وضعیت و منطق مستقل.
- **Feature/HTTP:** routeها، validation، احراز هویت، مجوز، metadata و پاسخ‌های خطا.
- **Integration:** DB، سرویس SMS، ذخیره‌سازی فایل و صف در صورت انتخاب.
- **Concurrency:** تخصیص هم‌زمان غرفه و check-in؛ اجرا روی موتور DB هدف.
- **Security regression:** tenant isolation، IDOR، XSS، rate-limit، فایل و خروجی‌ها.
- **SEO:** HTML اولیه، canonical، hreflang، robots، sitemap، status/redirect و صفحات draft/private.
- **Accessibility/usability:** keyboard navigation، focus، labels، contrast و RTL/LTR.
- **Operations:** backup/restore، رفتار هنگام قطع SMS/DB و rollback انتشار.

## حداقل کنترل CI
1. اعتبارسنجی `composer.json`.
2. نصب dependencyها از lockfile پس از ایجاد lockfile معتبر.
3. PHP lint.
4. تست‌های خودکار.

در این بسته هنوز فرمت‌سنج خودکار فعال نشده؛ بعد از نصب واقعی وابستگی‌ها می‌توان Laravel Pint را اجرا و سپس به CI اضافه کرد. تا زمانی که `composer.lock` در محیط توسعه تولید و بازبینی نشده، وابستگی‌های transitive کاملاً قفل نشده‌اند. پیش از اولین PR اجرایی، `composer install` و تست‌ها باید در محیط دارای اینترنت انجام شوند و `composer.lock` commit شود.

## تعریف done
برای هر قابلیت: معیار پذیرش قابل اثبات، تست مرتبط، authorization review، SEO review در صورت عمومی بودن، پیام خطای مناسب، logging بدون secrets و مستندات لازم. برای تست انجام‌نشده برچسب «تأیید نشده» بزن؛ آن را موفق اعلام نکن.
