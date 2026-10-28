# طراحی دیتابیس v1 — پیش‌نویس برای تأیید مالک

**وضعیت:** `needs-validation`. **تا تأیید مالک هیچ migration ساخته نمی‌شود.** نمودار: `er-diagram.png` (متن: `er-diagram.mmd`).
**موتور:** MariaDB 10.4.34، InnoDB، `utf8mb4_unicode_ci`. زمان: منطقهٔ `Asia/Tehran` (ADR-0007). مبالغ: `BIGINT UNSIGNED` ریال.

## جدول‌ها (۱۱ جدول کسب‌وکاری)
| جدول | کار | قید/نکتهٔ مهم |
|---|---|---|
| `users` | کاربران پنل | `username` یکتا؛ `role` ∈ admin/manager/company/scanner؛ `company_id` برای نقش company؛ رمز bcrypt/argon |
| `companies` | شرکت‌ها | `status` ∈ pending/approved/rejected/suspended؛ `slug` یکتا؛ فقط approved می‌تواند رزرو کند |
| `editions` | دوره‌های نمایشگاه | `status` ∈ draft/published/archived؛ `hold_minutes` پیش‌فرض ۱۸۰ |
| `halls` | سالن‌ها | یکتا: (`edition_id`,`code`) |
| `booths` | غرفه‌ها | یکتا: (`hall_id`,`code`)؛ `price_rial` قیمت نهایی؛ `status` ∈ active/blocked |
| `booth_reservations` | رزرو/خرید غرفه | `status` ∈ held/pending_verification/confirmed/expired/cancelled؛ **دو ستون محاسبه‌شده یکتا** (زیر) |
| `proformas` | پیش‌فاکتور | `number` یکتا؛ `reservation_id` یکتا؛ `amount_rial` snapshot؛ جدا از پرداخت |
| `badges` | کارت‌های QR | `kind` ∈ visitor/exhibitor/manager؛ `token_hash` = SHA-256 توکن تصادفی (خود توکن ذخیره نمی‌شود) |
| `scan_points` + `scan_point_kinds` | نقطه‌های اسکن و اینکه کدام نوع کارت مجاز است | `frequency` ∈ once_per_day/unlimited |
| `scans` | لاگ اسکن | `dedupe_key` یکتا (nullable) برای جلوگیری از ثبت دوباره در روز |
| `audit_events` | ممیزی | بدون رمز/توکن/OTP |

بدون جدول: sessions/cache/jobs (درایور file/sync)، password_resets (ادمین رمز را تعویض می‌کند).

## قیدهای کلیدی (چطور باگ را در دیتابیس می‌بندیم)
1. **یک غرفه = یک رزرو فعال:** `booth_reservations.active_booth_id = IF(status IN ('held','pending_verification','confirmed'), booth_id, NULL)` STORED + `UNIQUE`. (MariaDB قید مشروط ندارد؛ NULL چندبار مجاز است.)
2. **یک شرکت = یک غرفه در هر دوره:** `active_company_edition = IF(<فعال>, CONCAT(company_id,'-',edition_id), NULL)` STORED + `UNIQUE`.
3. **انقضای ۳ ساعته بدون cron:** زمان در قید دیتابیس نمی‌آید. هنگام رزرو جدید، داخل تراکنش ردیف غرفه `FOR UPDATE` قفل و رزروهای `held` منقضی‌شدهٔ همان غرفه/شرکت به `expired` تبدیل می‌شوند و بعد درج انجام می‌شود. خواندن‌ها (لیست عمومی) رزرو منقضی را آزاد حساب می‌کنند. (ADR-0005)
4. **ناهار یک‌بار در روز:** `scans.dedupe_key = badge:point:date` فقط برای اسکن پذیرفته‌شدهٔ `once_per_day`؛ اسکن هم‌زمان دوم خطای یکتا می‌گیرد و «duplicate» ثبت می‌شود.
5. **پول:** فقط عدد صحیح؛ قیمت در پیش‌فاکتور snapshot است (تغییر بعدی قیمت غرفه روی آن اثر ندارد).

## تعامل با قواعد کسب‌وکار
- شرکت ثبت‌نام می‌کند → `pending` → ادمین `approved` → انتخاب غرفه → رزرو `held` (۳ ساعت) + پیش‌فاکتور `issued`.
- (پیشنهاد، OPEN-012) شرکت «پرداخت کردم» می‌زند → `pending_verification` (بدون انقضا) → ادمین `confirmed` + پیش‌فاکتور `settled`.
- صفحهٔ عمومی: فقط `confirmed` با نام شرکت.
- زرین‌پال بعداً: جدول `payments` جدا به `proformas` وصل می‌شود؛ ساختار فعلی تغییر نمی‌کند.

## نقش‌ها
admin (مدیر کل)، manager (مدیر نمایشگاه)، company (مدیر شرکت)، scanner (اپراتور اسکن). ماتریس: `docs/04-security/authorization-matrix.md`.

## فعلاً خارج از طرح (بعداً با تأیید جدا)
CMS و ترجمه/نسخه‌ها، محصولات/کاتالوگ، سرنخ B2B، حامی مالی، رسانه، پرداخت آنلاین، نقشهٔ گرافیکی (مختصات غرفه).

## سؤال‌های باز مرتبط
OPEN-011 (ثبت‌نام بازدیدکننده)، OPEN-012 (تمدید رزرو)، OPEN-013 (ثبت پرداخت دستی)، OPEN-015 (فیلدهای شرکت).

## ریسک‌های فنی (باید روی MariaDB لوکال تست شوند)
- ستون STORED با شرط `IF` و `UNIQUE` روی MariaDB 10.4 (انتظار می‌رود OK؛ **تأیید نشده**).
- تست هم‌زمانی با چند اتصال واقعی (نه SQLite).
