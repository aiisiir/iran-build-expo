# Instructions for AI-assisted development

## Product constraints
- This pilot uses Laravel 13 + Blade and server-rendered pages. Do not introduce React, Next.js, a SPA, or a separate API frontend without an explicit architecture decision approved by the project owner.
- Use PHP 8.3+ as the target platform for Laravel 13. Verify real hosting requirements before deployment.
- Public content must be present in the initial HTML response. Treat SEO as a release requirement, not an optional cleanup.
- Bilingual public URLs use `/fa/...` and `/en/...`; keep canonicals self-referential and language alternates reciprocal.
- No online payment integration in v1. A proforma is not a payment, invoice settlement, or proof of funds received.

## Security and integrity
- Enforce authorization server-side on every read/write/export/download; never trust hidden fields or frontend checks.
- Every company-owned resource needs an explicit ownership/tenant check.
- Booth allocation must be safe under concurrent requests and protected by database constraints/transactions.
- OTP must expire, be rate limited, and never be written to logs. QR credentials must be opaque, contain no PII, and support idempotent check-in.
- Do not render unsanitized CMS HTML or arbitrary scripts.
- Use exact monetary representation; do not use floating point for currency.
- Never commit `.env`, real credentials, user records, OTPs, or production exports.

## Engineering workflow
- Read `docs/` before changing architecture or domain behavior.
- For material design decisions, add or update an ADR in `docs/02-architecture/decisions/` with status and rationale.
- Add/adjust a test for every critical requirement and bug fix. Run tests and formatting checks; report commands/results honestly.
- Keep changes small and focused. Do not add libraries, automation, or abstractions without a stated need and maintenance rationale.
- Verify version-sensitive Laravel behavior against official documentation: <https://laravel.com/docs/13.x>.
- Do not mark requirements complete merely because a document or route exists; require implementation and test evidence.
