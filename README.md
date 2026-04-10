# Mini CRM — Ticket System

A mini CRM built with **Laravel 12** and **PHP 8.2+** featuring a universal embeddable feedback widget, a REST API, and a Blade-based admin panel.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.2+ |
| Database | SQLite (default) / MySQL / PostgreSQL |
| Role management | spatie/laravel-permission v6 |
| File storage | spatie/laravel-medialibrary v11 |
| Auth (API) | Bearer token (api_token column) |
| Auth (Admin UI) | Session-based |

---

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm (only needed if you want to rebuild frontend assets)
- SQLite extension enabled (default on most PHP installs)

---

## Installation

```bash
# 1. Clone the repository
git clone <repo-url> mini-crm
cd mini-crm

# 2. Install PHP dependencies
composer install

# 3. Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# 4. Create the SQLite database file
touch database/database.sqlite

# 5. Run migrations and seed demo data
php artisan migrate --seed

# 6. Create the storage symlink (for media file URLs)
php artisan storage:link

# 7. Start the development server
php artisan serve
```

The application will be available at **http://localhost:8000**.

---

## Demo Credentials

### Admin Panel — http://localhost:8000/admin

| Field | Value |
|---|---|
| Email | `admin@example.com` |
| Password | `password` |

### API (Bearer token)

Obtain a token via `POST /api/auth/login`. The seeded tokens are random on each
`migrate:fresh --seed` run, so always log in first to get a fresh token.

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

| URL | Description |
|---|---|
| `GET /widget` | Embeddable feedback form (iframe-safe) |
| `GET /widget/embed` | Copy-paste `<iframe>` snippet + live preview |
| `GET /admin/login` | Admin panel login |
| `GET /admin/tickets` | Ticket list with filters |
| `GET /admin/tickets/{id}` | Ticket detail, attachments, status change |

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

The widget page sets `Content-Security-Policy: frame-ancestors *` so it can be
embedded on any origin without browser security errors.

A live preview and the copy-paste snippet are also available at
`GET /widget/embed`.

---

## REST API

All API responses are JSON. Authenticated endpoints require:

```
Authorization: Bearer <token>
```

### Authentication

#### Register
```
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
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

Response:
```json
{
  "user": { "id": 1, "name": "Admin", "email": "admin@example.com", "roles": ["admin"] },
  "token": "<80-char token>"
}
```

#### Logout
```
POST /api/auth/logout
Authorization: Bearer <token>
```

#### Current user
```
GET /api/auth/me
Authorization: Bearer <token>
```

---

### Widget — public ticket submission

No authentication required. Used by the `/widget` Blade page.

```
POST /api/widget/submit
Content-Type: multipart/form-data

name     = "John Doe"          (required)
email    = "john@example.com"  (optional)
phone    = "+12025550100"       (optional, E.164)
subject  = "Login issue"       (required)
content  = "I cannot log in…"  (required)
files[]  = <file>              (optional, max 5 × 10 MB)
```

Response `201`:
```json
{
  "message": "Your request has been submitted. We will get back to you shortly.",
  "ticket_id": 42,
  "reference": "TKT-00042"
}
```

---

### Tickets

#### List tickets
```
GET /api/tickets
Authorization: Bearer <token>

# Optional query params:
?status=new          # new | in_progress | completed
?page=2
```

Admins see all tickets. Operators see only tickets assigned to them.

#### Create ticket
```
POST /api/tickets
Authorization: Bearer <token>
Content-Type: multipart/form-data

customer_id = 1               (required, existing customer id)
subject     = "Billing issue" (required)
content     = "Details…"      (required)
assigned_to = 2               (optional, user id)
files[]     = <file>          (optional)
```

Response `201`:
```json
{
  "id": 5,
  "customer_id": 1,
  "subject": "Billing issue",
  "content": "Details…",
  "status": "new",
  "admin_response": null,
  "responded_at": null,
  "created_at": "2026-04-10T12:00:00.000000Z",
  "customer": { "id": 1, "name": "John Doe", "email": "john@example.com" }
}
```

#### Get ticket
```
GET /api/tickets/{id}
Authorization: Bearer <token>
```

Response includes an `attachments` array:
```json
{
  "id": 5,
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

#### Update ticket
```
POST /api/tickets/{id}
Authorization: Bearer <token>
Content-Type: multipart/form-data

status         = "in_progress"     (optional)
admin_response = "We are on it."   (optional)
assigned_to    = 3                 (optional)
files[]        = <file>            (optional, appends new files)
```

#### Delete ticket (admin only)
```
DELETE /api/tickets/{id}
Authorization: Bearer <token>
```

#### Delete attachment
```
DELETE /api/tickets/{id}/attachments/{mediaId}
Authorization: Bearer <token>
```

#### Ticket statistics (admin only)
```
GET /api/tickets/statistics
Authorization: Bearer <token>
```

Response:
```json
{
  "daily": {
    "total": 3,
    "new": 2,
    "in_progress": 1,
    "completed": 0
  },
  "weekly": {
    "total": 14,
    "new": 6,
    "in_progress": 5,
    "completed": 3
  },
  "monthly": {
    "total": 48,
    "new": 15,
    "in_progress": 12,
    "completed": 21
  },
  "all_time": {
    "total": 48,
    "new": 15,
    "in_progress": 12,
    "completed": 21
  }
}
```

---

### Customers

```
GET    /api/customers              # paginated list with tickets_count
POST   /api/customers              # create
GET    /api/customers/{id}         # detail with tickets
PUT    /api/customers/{id}         # update
DELETE /api/customers/{id}         # delete
```

Create / update body:
```json
{
  "name":  "John Doe",
  "email": "john@example.com",
  "phone": "+12025550100"
}
```

Phone must be in **E.164 format** (`+` followed by 2–15 digits).

---

### Users (admin only)

```
GET    /api/users              # list all users with roles
GET    /api/users/{id}         # single user
PUT    /api/users/{id}         # update name / email
DELETE /api/users/{id}         # delete (cannot delete yourself)
PUT    /api/users/{id}/roles   # sync roles
```

Sync roles body:
```json
{ "roles": ["operator"] }
```

Valid roles: `admin`, `operator`.

---

## Admin Panel

Access at **http://localhost:8000/admin** (session-based, admin role required).

### Ticket list — `/admin/tickets`

Filters available:
- Status (`new` / `in_progress` / `completed`)
- Customer email
- Customer phone
- Date range (`date_from` / `date_to`)

### Ticket detail — `/admin/tickets/{id}`

Shows:
- Customer name, email, phone
- Full message content
- Admin response (if any) with responded_at timestamp
- Assigned operator
- All attachments with individual **Download** links
- Status change form (dropdown + save button)

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
      CustomerController.php
      RoleController.php            # role sync
      TicketController.php          # REST API + statistics
      UserController.php
      WidgetController.php          # public widget submit
      WidgetPageController.php      # Blade widget page + embed page
    Middleware/
      AdminAuthenticate.php         # session guard for admin panel
      AllowIframeEmbedding.php      # removes X-Frame-Options
      AuthenticateWithApiToken.php  # Bearer token guard
      CheckRole.php                 # spatie role check
  Models/
    Customer.php
    Ticket.php                      # Eloquent scopes: daily/weekly/monthly
    User.php                        # HasRoles (spatie)

database/
  factories/   UserFactory, CustomerFactory, TicketFactory
  migrations/
  seeders/     RoleSeeder → UserSeeder → CustomerSeeder → TicketSeeder

resources/views/
  admin/
    layout.blade.php
    login.blade.php
    pagination.blade.php
    tickets/index.blade.php
    tickets/show.blade.php
  widget.blade.php
  widget-embed.blade.php

routes/
  api.php
  web.php
```

---

## Running Tests

```bash
php artisan test
```

---

## Re-seeding

```bash
php artisan migrate:fresh --seed
```

This drops all tables, re-runs migrations, and seeds fresh demo data including
the fixed admin/operator accounts listed above.
