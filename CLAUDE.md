# Iran Build Expo — Claude Code instructions

Read `AGENTS.md`, `README.md`, `docs/AI_Learns.md` and the relevant documents under `docs/` before changing implementation.

The current approved pilot stack is Laravel 13 + Blade, with server-rendered public routes and bilingual URL prefixes. Do not create a React/Next.js frontend or split API/frontend architecture. Keep SEO, authorization, company data isolation, booth-allocation concurrency, OTP/QR security, exact monetary representation, and auditability in scope for all relevant changes.

Follow Laravel's official version-specific documentation. If a requested change conflicts with a documented decision, point out the conflict and propose an ADR update rather than silently overriding the decision. Do not invent test results or claim that unrun checks passed.
