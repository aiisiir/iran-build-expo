# ADR-0012 — One generic admin controller driven by Resource descriptors

**Status:** accepted (2026-11-18)

**Context.** The panel manages 17 content types with the same flow (list, create, edit, soft-delete, validation, audit). Laravel's conventional answer is one resource controller per model with Form Requests and Policies.

**Decision.** Keep one `ResourceController` and describe each type in a small `Resource` class (fields with rules, columns, hooks). The `{resource}` route parameter is bound to the descriptor (unknown key = 404).

**Why.** One place for validation, authorization, transactions, soft delete and audit instead of 17 copies; a new content type is one class. Fewer places for a security bug.

**Trade-offs.** Less familiar than per-model controllers; no per-model Form Request or Policy. Revisit when roles are extended (see user management): move `adminOnly`/`canDelete` to Gates/Policies and keep the generic controller.

**Rejected.** A packaged admin generator (new dependency, larger attack surface).
