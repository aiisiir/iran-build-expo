# مدل تهدید اولیه

| دارایی/سطح | تهدید محتمل | کنترل لازم | آزمون/شاهد |
|---|---|---|---|
| حساب کاربری | credential stuffing، brute force، session theft | rate limit، سیاست session، TLS، محدودیت تلاش و پایش | تست rate-limit و نشست |
| OTP | استفادهٔ مجدد، brute force، افشای کد در log | زمان انقضا، یک‌بارمصرف، محدودیت ارسال/تلاش، عدم log | تست expiry/replay و بازبینی log |
| شرکت‌ها | IDOR/دسترسی cross-tenant | policy/authorization سروری، query scoping، آزمون منفی | تست شرکت A علیه رکورد B |
| تخصیص غرفه | race condition و double booking | transaction، قفل/قیود یکتا و retry محدود | تست هم‌زمانی با DB مشابه production |
| QR/check-in | جعل یا استفادهٔ مجدد، افشای PII | token تصادفی opaque، hash، expiry/لغو در صورت نیاز، idempotency | تست token و اسکن تکراری |
| CMS | stored XSS یا انتشار draft | sanitization allowlist، escaping Blade، مجوز انتشار | payload test و بازبینی policy |
| فایل‌ها | آپلود فایل اجرایی یا دسترسی غیرمجاز | نوع/حجم محدود، نام تصادفی، ذخیره خارج از public، بررسی download policy | تست MIME و مجوز فایل |
| پیش‌فاکتور | دستکاری مبلغ/وضعیت | محاسبه سمت سرور، representation دقیق، audit | تست مقادیر مرزی و دسترسی |
| خروجی گزارش | افشای داده یا CSV injection | مجوز مستقل export، فیلتر ستون‌ها، خنثی‌سازی spreadsheet formula | تست export و tenant isolation |
| استقرار | افشای `.env`، debug فعال، backup عمومی | public root فقط `public/`، secrets خارج Git، debug خاموش | بررسی deployment config |

## خطوط قرمز
- رمز، OTP، کوکی/session، توکن QR و API key در log ظاهر نشود.
- `APP_DEBUG=true` در production ممنوع است.
- پنهان‌کردن دکمه در UI کنترل دسترسی محسوب نمی‌شود.
- `robots.txt` امنیت نیست؛ دادهٔ خصوصی باید authorization داشته باشد.
- برای هر نقص بحرانی، انتشار تا رفع یا پذیرش ریسک توسط مسئول مشخص متوقف می‌شود.

## موارد نیازمند تکمیل
مدل مهاجم، داده‌های مشمول حفاظت، دورهٔ نگهداری، مسئول رخداد امنیتی، روند پاسخ به نقض داده و الزامات حقوقی/رضایت باید پیش از بهره‌برداری مشخص شوند.
