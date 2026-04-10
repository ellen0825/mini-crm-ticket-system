# Architecture & Technical Decisions

This document explains the architectural choices, library selections, and feature
implementations made in the Mini CRM project.

---

## 1. Framework — Laravel 12

Laravel 12 was chosen because:

- It ships with everything needed for a REST API and a server-rendered admin UI
  in a single codebase (routing, ORM, validation, middleware, Blade, queues).
- The Eloquent ORM makes the domain model expressive and easy to test.
- The built-in `RateLimiter` facade handles per-key throttling without any
  additional infrastructure.
- First-class support for file uploads and storage abstractions made integrating
  `spatie/laravel-medialibrary` straightforward.

---

## 2. Authentication — Custom API Token (Bearer)

**Decision:** A hand-rolled `api_token` column on the `users` table rather than
Laravel Sanctum or Passport.

**Rationale:**
- The project spec calls for a stateless REST API consumed by a widget and an
  admin panel. Sanctum would work, but it adds session cookie complexity for
  SPA use cases that are not needed here.
- A single random 80-character token stored in the database is simple, auditable,
  and easy to rotate (re-login issues a new token).
- The `AuthenticateWithApiToken` middleware is ~20 lines and does exactly one
  thing: resolve the user from the Bearer token and attach it to the request.
  No magic, no hidden behaviour.

**Trade-off:** Tokens do not expire automatically. In production you would add
a `token_expires_at` column or switch to Sanctum with expiry configured.

---

## 3. Role Management — spatie/laravel-permission

**Decision:** Replace a hand-rolled `role` string column with
`spatie/laravel-permission`.

**Rationale:**
- The `role` column approach works for two roles but does not scale. Adding a
  third role (e.g. `supervisor`) would require touching every `if ($user->role === 'admin')` check across the codebase.
- Spatie's package stores roles in a dedicated `roles` table and provides
  `hasRole()`, `hasAnyRole()`, `assignRole()`, and `syncRoles()` — all tested
  and battle-hardened.
- The `User::role('operator')` query scope (provided by `HasRoles`) replaced
  the raw `where('role', 'operator')` query in seeders cleanly.
- `CheckRole` middleware delegates to `$user->hasAnyRole($roles)`, so adding
  a new role to a route is a one-word change.

**Why not Gates/Policies?** Gates are better for object-level authorization
(e.g. "can this user edit this ticket?"). Role checks are coarser-grained and
belong in middleware. Both are used: middleware for role gates, inline checks
in controllers for ownership (operator can only see their own tickets).

---

## 4. File Storage — spatie/laravel-medialibrary

**Decision:** All file attachments are managed exclusively through
`spatie/laravel-medialibrary` rather than manual `Storage::put()` calls.

**Rationale:**
- The library handles the `media` table, file naming, disk routing, and URL
  generation in one place. Controllers never touch file paths directly.
- `registerMediaCollections()` on the `Ticket` model declares the `attachments`
  collection as the single source of truth for what files belong to a ticket.
- Deleting a ticket cascades to its media records automatically.
- The `getUrl()` method abstracts the storage driver — switching from `local`
  to `s3` in production requires only an `.env` change.

---

## 5. Widget — Public Embeddable Form

**Decision:** A dedicated Blade page at `/widget` that posts to
`POST /api/widget/submit` via `fetch()`.

**Rationale:**
- Separating the Blade page (`WidgetPageController`) from the API endpoint
  (`WidgetController`) follows SRP. The page controller only returns a view;
  the API controller only handles data.
- The page sets `Content-Security-Policy: frame-ancestors *` via the
  `AllowIframeEmbedding` middleware, applied only to the `/widget` route.
  This is the correct modern replacement for the deprecated
  `X-Frame-Options: ALLOWALL`.
- The JS submission layer uses `AbortController` for a 15-second timeout,
  maps Laravel 422 validation errors back to individual fields, and shows a
  ticket reference number (`TKT-00042`) on success.

---

## 6. Submission Rate Limiting — 1 per email/phone per day

**Decision:** Laravel's `RateLimiter` facade with a daily-rotating cache key.

**Rationale:**
- The key is `widget_submit:email:<address>:<date>` or
  `widget_submit:phone:<number>:<date>`. Including the date in the key means
  the limit resets at midnight without any scheduled job.
- `RateLimiter::hit($key, $secondsUntilEndOfDay())` sets the TTL to exactly
  the remaining seconds in the current calendar day, so the cache entry
  expires naturally.
- This approach works with any cache driver (database, Redis, file) and
  requires zero additional infrastructure.
- The check happens **after** validation but **before** the ticket is created,
  so invalid requests are rejected cheaply.

---

## 7. Admin Panel — Blade UI (Session Auth)

**Decision:** A separate session-based admin panel at `/admin/*` rather than
reusing the API token auth.

**Rationale:**
- Browser-based admin UIs are a natural fit for session cookies. Requiring
  admins to manually pass a Bearer token in every request would be poor UX.
- `AdminAuthenticate` middleware checks `session('admin_user_id')` — a simple,
  stateful guard that does not interfere with the stateless API middleware.
- The admin panel is intentionally thin: it reads data through Eloquent scopes
  (the same scopes used by the API) and delegates status changes to a single
  `PATCH` endpoint. No business logic lives in the Blade controllers.

---

## 8. Eloquent Scopes + Carbon for Statistics

**Decision:** Named scopes (`daily`, `weekly`, `monthly`, `ofStatus`,
`forCustomerEmail`, `forCustomerPhone`) on the `Ticket` model.

**Rationale:**
- Scopes keep query logic in the model, not scattered across controllers and
  admin views. Both `TicketController::statistics()` and
  `AdminTicketController::index()` reuse the same scopes.
- Carbon's `startOfWeek()`, `endOfWeek()`, `today()`, and
  `secondsUntilEndOfDay()` are used throughout. Carbon is already a Laravel
  dependency — no additional package needed.
- The `statistics()` method uses a closure to avoid repeating the
  `daily/weekly/monthly` breakdown logic four times (DRY).

---

## 9. Testing Strategy

**Decision:** Laravel feature tests with `RefreshDatabase` and an in-memory
SQLite database.

**Rationale:**
- Feature tests exercise the full HTTP stack (middleware, validation,
  controller, model, database) in a single test. This gives high confidence
  that the integration works end-to-end.
- `RefreshDatabase` wraps each test in a transaction that is rolled back,
  keeping tests isolated and fast (~1.8 s for 25 tests).
- A base `TestCase` seeds roles before every test so `assignRole()` never
  fails due to a missing role record.
- Tests cover: auth (register, login, wrong password), ticket CRUD, operator
  visibility restrictions, `responded_at` auto-set, statistics accuracy, and
  widget rate limiting.

---

## 10. Docker Setup

**Decision:** A single application container (PHP-FPM + Nginx) with a separate
MySQL container orchestrated by `docker-compose`.

**Rationale:**
- Combining PHP-FPM and Nginx in one image simplifies local development: one
  `docker compose up` command starts the entire stack.
- MySQL 8 is used in Docker (instead of SQLite) to match a realistic production
  environment. SQLite remains the default for local development without Docker.
- The entrypoint script runs `migrate --force` and `db:seed --force`
  automatically on first boot, so the application is immediately usable after
  `docker compose up`.
- Health checks on the `db` service prevent the app container from starting
  before MySQL is ready to accept connections.

---

## 11. API Documentation — Swagger / OpenAPI 3

**Decision:** `darkaonline/l5-swagger` with PHP 8 native attributes
(`#[OA\...]`).

**Rationale:**
- Docblock annotations (`@OA\...`) are supported by swagger-php v3/v4 but
  swagger-php v6 (bundled with l5-swagger v11) defaults to PHP 8 native
  attributes. Using attributes keeps annotations co-located with the code they
  describe and avoids the docblock parsing overhead.
- A dedicated `SwaggerInfo` class holds the global `#[OA\Info]`,
  `#[OA\Server]`, `#[OA\SecurityScheme]`, and shared schema definitions.
  This keeps controller files focused on HTTP logic.
- The generated UI is available at `GET /api/documentation` with no additional
  configuration.

---

## Summary of Libraries

| Library | Version | Purpose |
|---|---|---|
| `laravel/framework` | ^12.0 | Application framework |
| `spatie/laravel-permission` | ^6.25 | Role-based access control |
| `spatie/laravel-medialibrary` | ^11.21 | File attachment management |
| `darkaonline/l5-swagger` | ^11.0 | OpenAPI 3 / Swagger UI |
| `fakerphp/faker` | ^1.23 | Realistic test data generation |
| `phpunit/phpunit` | ^11.5 | Test runner |
