# ADR-0004: میزبانی و استقرار

- **وضعیت:** پذیرفته‌شده (مالک، 2026-10-28)
## تصمیم
ساب‌دامین `iranbuildexpo.mahdiarmansouri.ir`؛ `app/` کنار `public_html/`؛ بدون SSH؛ SQL دستی؛ queue=`sync`؛ بدون cron.
## دلیل
ساب‌دامین ریشهٔ جدا می‌دهد: `robots.txt`، canonical، کوکی و `APP_URL` بدون پیشوند مسیر کار می‌کنند و کد/`.env` بیرون از وب می‌ماند. زیرپوشه (`/iranbuildexpo`) رد شد.
## پیامد
`public/index.php` هر دو چیدمان (`app/` هم‌سطح یا `public/` داخل پروژه) را تشخیص می‌دهد. هیچ دستور artisan روی سرور اجرا نمی‌شود. ریسک‌ها: `docs/07-operations/deployment.md`.

## Addendum 2026-11-03
Release is built locally by `scripts/build-release.php` (zip of `app/` + `public_html/`, no secrets or runtime data); rollback = previous zip + SQL backup. The DB engine is pinned to InnoDB in `config/database.php`; `deploy/public_html/check.php` verifies it on the host. Details: `docs/07-operations/deployment.md`.
