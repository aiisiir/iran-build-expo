# ثبت نسخهٔ چارچوب مورد استفاده

- **مخزن چارچوب:** `mainstructure` (https://github.com/aiisiir/mainstructure)
- **نسخهٔ پذیرفته‌شده:** `0.1.1`
- **تاریخ ثبت:** 2026-10-27؛ بازبینی 2026-10-28
- **مدل استفاده:** فقط مرجع؛ هیچ وابستگی اجرایی، submodule یا sync خودکار وجود ندارد. چارچوب را هرگز از این مخزن تغییر نمی‌دهیم.

## قوانین پذیرفته‌شده در این محصول
| قانون چارچوب | کاربرد در محصول |
|---|---|
| `FRAMEWORK.md` §4 (مصنوعات لازم) | اسناد زیر `docs/` |
| `core/security.md`، `core/privacy-and-data.md` | `docs/04-security/` |
| `core/seo-and-discoverability.md` | `docs/01-requirements/seo-requirements.md` |
| `quality-gates/` (requirements, architecture, security, seo, release) | معیار پایان فازها |
| `templates/architecture-decision.md` | قالب ADRها |
| `core/project-lifecycle.md` — شرایط «توقف خط» | توقف کار هنگام ابهام در هدف/امنیت داده |

## پذیرفته‌نشده
پروفایل‌های `api-service`، `mobile-application`، `data-platform`؛ `tooling/adapters`؛ نصب افزونه‌های جانبی.

## شکاف شناسایی‌شده (تصمیم مخصوص این محصول، نه تغییر چارچوب)
چارچوب دربارهٔ هاست اشتراکی بدون SSH قاعده‌ای ندارد؛ تصمیم‌های مربوط در `docs/07-operations/deployment.md` ثبت شده است.

## سیاست تغییر
تغییرات چارچوب خودکار اعمال نمی‌شوند: informational / recommended / required-security / breaking با ارزیابی و تسک جدا.
