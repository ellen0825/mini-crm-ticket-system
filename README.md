# Mini CRM — Ticket System

A mini CRM built with **Laravel 12** and **PHP 8.2+** featuring a universal
embeddable feedback widget, a REST API, and a Blade-based admin panel.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Database | SQLite (local) / MySQL (Docker / production) |
| Role management | spatie/laravel-permission v6 |
| File storage | spatie/laravel-medialibrary v11 |
| Auth (API) | Bearer token (`api_token` column) |
| Auth (Admin UI) | Session-based |
| API docs | l5-swagger (OpenAPI 3 / Swagger UI) |

---

## Quick Start — Docker (recommended)

Requires Docker and Docker Compose.

```bash
git clone <repo-url> mini-crm
cd mini-crm

# Start the full stack (app + MySQL)
docker compose up --build -d
```

The entrypoint script automatically:
- copies `.env.docker` → `.env`
- generates the app key
- runs `migrate --force`
- runs `db:seed --force`
- creates the storage symlink

App is available at **http://localhost:8000** once the container is healthy.

To stop:
```bash
docker compose down
```

To wipe the database volume and start fresh:
```bash
docker compose down -v
docker compose up --build -d
```

---

## Quick Start — Local (SQLite)

Requirements: PHP 8.2+, Composer, SQLite extension.

```bash
git clone <repo-url> mini-crm
cd mini-crm

composer install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite

php artisan migrate --seed
php artisan storage:link
php artisan serve
```

App is available at **http://localhost:8000**.

> **Port conflict with Docker Desktop?**
> Docker Desktop on Windows binds port 8000 by default. If you see "File not found"
> at `localhost:8000`, run on a different port:
> ```bash
> php artisan serve --port=8080
> ```
> Then use `http://localhost:8080` for all URLs in this README.

---

## Environment Variables

Key variables in `.env` / `.env.example`:

| Variable | Default | Description |
|---|---|---|
| `APP_URL` | `http://localhost` | Used in media file URLs and iframe embed snippet |
| `DB_CONNECTION` | `sqlite` | `sqlite`, `mysql`, or `pgsql` |
| `DB_HOST` | — | MySQL host (Docker: `db`) |
| `DB_DATABASE` | — | Database name |
| `DB_USERNAME` | — | Database user |
| `DB_PASSWORD` | — | Database password |
| `CACHE_STORE` | `database` | Used by the rate limiter — switch to `redis` in production |
| `FILESYSTEM_DISK` | `local` | Media storage disk (`local` or `s3`) |
| `SESSION_DRIVER` | `database` | Admin panel session driver |

---

## Demo Credentials

### Admin Panel — http://localhost:8000/admin

| Field | Value |
|---|---|
| Email | `admin@example.com` |
| Password | `password` |

### API (Bearer token)

Obtain a token via `POST /api/auth/login`. Tokens are regenerated on each
`migrate:fresh --seed`, so always log in first to get a fresh one.

**Seeded users**

| Name | Email | Password | Role |
|---|---|---|---|
| Admin | `admin@example.com` | `password` | admin |
| Operator | `operator@example.com` | `password` | operator |
| Operator 2–4 | _(random emails)_ | `password` | operator |

**Seeded customers** (fixed)

| Name | Email | Phone |
|---|---|---|
| John Doe | `john@example.com` | `+12025550100` |
| Jane Smith | `jane@example.com` | `+447911123456` |
| Carlos Rivera | `carlos@example.com` | `+34612345678` |
| Yuki Tanaka | `yuki@example.com` | `+819012345678` |

Plus 16 randomly generated customers and ~50 tickets spread across all statuses.

---

## Pages

| URL | Auth | Description |
|---|---|---|
| `GET /widget` | None | Embeddable feedback form (iframe-safe) |
| `GET /widget/embed` | None | Copy-paste `<iframe>` snippet + live preview |
| `GET /admin/login` | None | Admin panel login |
| `GET /admin/tickets` | Admin session | Ticket list with filters |
| `GET /admin/tickets/{id}` | Admin session | Ticket detail, attachments, status change |
| `GET /api/documentation` | None | Swagger UI (OpenAPI 3) |

---

## Widget — iframe Embed

Paste this snippet anywhere in your website HTML:

```html
<iframe
  src="https://your-domain.com/widget"
  width="480"
  height="620"
  frameborder="0"
  style="border:none;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.1);"
  title="Contact Us"
  loading="lazy"
></iframe>
```

Replace `https://your-domain.com` with your actual `APP_URL`.

The widget sets `Content-Security-Policy: frame-ancestors *` so it embeds on
any origin without browser security errors.

A live preview and the ready-to-copy snippet are also at `GET /widget/embed`.

### Submission rate limit

The widget enforces **one submission per unique email or phone number per
calendar day**. A second attempt returns `429 Too Many Requests` with a clear
message. The limit resets at midnight (UTC).

---

## API Documentation (Swagger)

Interactive Swagger UI is available at:

```
GET /api/documentation
```

To regenerate the spec after changing annotations:

```bash
php artisan l5-swagger:generate
```

---

## REST API

All responses are JSON. Authenticated endpoints require:

```
Authorization: Bearer <token>
```

### Full route table

| Method | Path | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/register` | — | Register (assigned operator role) |
| POST | `/api/auth/login` | — | Login, returns token |
| POST | `/api/auth/logout` | Any | Invalidate token |
| GET | `/api/auth/me` | Any | Current user + roles |
| POST | `/api/widget/submit` | — | Public widget submission |
| GET | `/api/tickets` | Any | List tickets (paginated) |
| POST | `/api/tickets` | Any | Create ticket |
| GET | `/api/tickets/statistics` | Any | Daily/weekly/monthly stats |
| GET | `/api/tickets/{id}` | Any | Ticket detail + attachments |
| POST | `/api/tickets/{id}` | Any | Update ticket (multipart) |
| DELETE | `/api/tickets/{id}` | Admin | Delete ticket |
| DELETE | `/api/tickets/{id}/attachments/{mediaId}` | Any | Delete attachment |
| GET | `/api/customers` | Any | List customers |
| POST | `/api/customers` | Any | Create customer |
| GET | `/api/customers/{id}` | Any | Customer detail + tickets |
| PUT | `/api/customers/{id}` | Any | Update customer |
| DELETE | `/api/customers/{id}` | Any | Delete customer |
| GET | `/api/users` | Admin | List users with roles |
| GET | `/api/users/{id}` | Admin | Single user |
| PUT | `/api/users/{id}` | Admin | Update name / email |
| DELETE | `/api/users/{id}` | Admin | Delete user |
| PUT | `/api/users/{id}/roles` | Admin | Sync roles |

---

### Authentication

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

Response `200`:
```json
{
  "user": { "id": 1, "name": "Admin", "email": "admin@example.com", "roles": ["admin"] },
  "token": "<80-char token>"
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer <token>
```

#### Current user
```http
GET /api/auth/me
Authorization: Bearer <token>
```

---

### Widget — public submission

No authentication required. Rate-limited to one per email/phone per day.

```http
POST /api/widget/submit
Content-Type: multipart/form-data

name     = "John Doe"          (required)
email    = "john@example.com"  (optional)
phone    = "+12025550100"       (optional, E.164)
subject  = "Login issue"       (required)
content  = "I cannot log in…"  (required)
files[]  = <file>              (optional, max 5 files × 10 MB each)
```

Response `201`:
```json
{
  "message": "Your request has been submitted. We will get back to you shortly.",
  "ticket_id": 42,
  "reference": "TKT-00042"
}
```

Response `429` (rate limit hit):
```json
{
  "message": "You have already submitted a request today. Please try again tomorrow."
}
```

---

### Tickets

#### List
```http
GET /api/tickets?status=new&page=1
Authorization: Bearer <token>
```

Admins see all tickets. Operators see only tickets assigned to them.

#### Create
```http
POST /api/tickets
Authorization: Bearer <token>
Content-Type: multipart/form-data

customer_id = 1
subject     = "Billing issue"
content     = "I was charged twice."
assigned_to = 2               (optional)
files[]     = <file>          (optional)
```

Response `201`:
```json
{
  "id": 5,
  "customer_id": 1,
  "subject": "Billing issue",
  "content": "I was charged twice.",
  "status": "new",
  "admin_response": null,
  "responded_at": null,
  "created_at": "2026-04-10T12:00:00.000000Z",
  "customer": { "id": 1, "name": "John Doe", "email": "john@example.com" }
}
```

#### Get (with attachments)
```http
GET /api/tickets/5
Authorization: Bearer <token>
```

```json
{
  "id": 5,
  "status": "new",
  "attachments": [
    {
      "id": 1,
      "name": "screenshot.png",
      "url": "http://localhost:8000/storage/1/screenshot.png",
      "mime": "image/png",
      "size": 204800
    }
  ]
}
```

#### Update
```http
POST /api/tickets/5
Authorization: Bearer <token>
Content-Type: multipart/form-data

status         = "in_progress"
admin_response = "We are looking into this."
assigned_to    = 3
files[]        = <file>        (appends, does not replace)
```

#### Delete (admin only)
```http
DELETE /api/tickets/5
Authorization: Bearer <token>
```

#### Delete attachment
```http
DELETE /api/tickets/5/attachments/1
Authorization: Bearer <token>
```

#### Statistics
```http
GET /api/tickets/statistics
Authorization: Bearer <token>
```

```json
{
  "daily":    { "total": 3,  "new": 2,  "in_progress": 1, "completed": 0  },
  "weekly":   { "total": 14, "new": 6,  "in_progress": 5, "completed": 3  },
  "monthly":  { "total": 48, "new": 15, "in_progress": 12,"completed": 21 },
  "all_time": { "total": 48, "new": 15, "in_progress": 12,"completed": 21 }
}
```

---

### Customers

```http
GET    /api/customers           # paginated, includes tickets_count
POST   /api/customers           # create
GET    /api/customers/{id}      # detail with tickets
PUT    /api/customers/{id}      # update
DELETE /api/customers/{id}      # delete
```

Body (create / update):
```json
{
  "name":  "John Doe",
  "email": "john@example.com",
  "phone": "+12025550100"
}
```

`phone` must be **E.164** format (`+` followed by 2–15 digits).

---

### Users (admin only)

```http
GET    /api/users               # list with roles
GET    /api/users/{id}
PUT    /api/users/{id}          # update name / email only
DELETE /api/users/{id}          # cannot delete yourself
PUT    /api/users/{id}/roles    # sync roles
```

Sync roles body:
```json
{ "roles": ["operator"] }
```

Valid roles: `admin`, `operator`.

---

## Admin Panel

Access at **http://localhost:8000/admin** — session-based, admin role required.

### Ticket list `/admin/tickets`

Filter by:
- Status (`new` / `in_progress` / `completed`)
- Customer email
- Customer phone (E.164)
- Date range (`date_from` / `date_to`)

### Ticket detail `/admin/tickets/{id}`

- Customer name, email, phone (with `mailto:` / `tel:` links)
- Full message and admin response
- `responded_at` timestamp
- Assigned operator
- All attachments with individual **Download** links
- Status change form (select + save, redirects with flash message)

---

## Running Tests

```bash
php artisan test
```

25 feature tests covering auth, ticket CRUD, operator visibility, statistics
accuracy, widget submission, and rate limiting. All run in ~2 seconds using
an in-memory SQLite database.

To run a specific suite:
```bash
php artisan test --filter WidgetSubmitTest
php artisan test --filter TicketApiTest
php artisan test --filter AuthTest
```

---

## Re-seeding

```bash
php artisan migrate:fresh --seed
```

Drops all tables, re-runs migrations, and seeds fresh demo data including the
fixed admin/operator accounts above.

---

## Project Structure

```
app/
  Http/
    Controllers/
      Admin/
        AdminAuthController.php     # session login / logout
        AdminTicketController.php   # list, show, status update
      AuthController.php            # API token auth
      CustomerController.php        # customer CRUD
      RoleController.php            # role sync endpoint
      SwaggerInfo.php               # OpenAPI global info + schemas
      TicketController.php          # ticket CRUD + statistics
      UserController.php            # user management
      WidgetController.php          # public widget submit + rate limit
      WidgetPageController.php      # /widget and /widget/embed pages
    Middleware/
      AdminAuthenticate.php         # session guard for admin panel
      AllowIframeEmbedding.php      # sets frame-ancestors * header
      AuthenticateWithApiToken.php  # Bearer token guard
      CheckRole.php                 # spatie role check
  Models/
    Customer.php
    Ticket.php                      # Eloquent scopes: daily/weekly/monthly/ofStatus
    User.php                        # HasRoles (spatie)

database/
  factories/
    CustomerFactory.php
    TicketFactory.php               # statusNew / inProgress / completed states
    UserFactory.php
  migrations/
  seeders/
    RoleSeeder.php                  # creates admin + operator roles
    UserSeeder.php                  # fixed admin + operator + 3 random operators
    CustomerSeeder.php              # 4 fixed + 16 random customers
    TicketSeeder.php                # 3 fixed (one per status) + bulk random

resources/views/
  admin/
    layout.blade.php
    login.blade.php
    pagination.blade.php
    tickets/
      index.blade.php
      show.blade.php
  widget.blade.php
  widget-embed.blade.php

routes/
  api.php
  web.php

tests/
  Feature/
    AuthTest.php
    TicketApiTest.php
    WidgetSubmitTest.php

docker/
  entrypoint.sh
  nginx.conf
docker-compose.yml
Dockerfile
ARCHITECTURE.md
```
