# SmartHealth

A full-featured clinical operations platform built for modern healthcare practices. SmartHealth combines AI-powered clinical documentation with end-to-end practice management — from patient onboarding and appointment scheduling to billing, messaging, and care operations — all in a clean, responsive interface that works equally well in light and dark mode.

---

## Features

### AI-Powered Clinical Documentation
- Generate structured **SOAP notes** from raw consultation text using OpenAI
- Edit individual SOAP sections inline before saving
- Export consultation notes as **PDF** for patient records or referrals

### Role-Based Access
- Three distinct roles: **Admin**, **Doctor**, and **Patient**
- Each role gets a tailored dashboard and scoped data visibility
- Admins manage the entire clinic; doctors see their own schedule and patients; patients see only their own records

### Patient Management
- Full patient profiles with demographics, insurance, and medical history
- Multi-step **onboarding wizard** (personal info → contact → insurance → consent)
- Track onboarding progress per patient

### Appointment Scheduling
- Create, confirm, reschedule, and complete appointments
- **Conflict detection** — blocks double-booking a doctor
- Doctor availability rules respected when booking
- Patients can self-book appointments through the portal
- Calendar view with month navigation

### Billing & Invoicing
- Create itemised invoices linked to appointments or services
- Record manual payments against invoices
- Track paid, pending, and overdue balances
- Role-scoped stats — patients only see their own billing data
- Insurance claims management

### Messaging
- Threaded patient ↔ provider messaging
- Unread message count shown live in the sidebar badge

### Real-Time Notifications
- Database-backed notifications for appointment bookings and new messages
- Notification bell in the topbar with mark-all-read action
- Notifications scoped per user role

### Care Operations
- Referrals, care plans, and waitlist management
- Insurance providers and plan management
- Analytics dashboard for admins

### Developer Experience
- Light + dark theme with no-flash persistence via `localStorage`
- Fully responsive — optimised for mobile, tablet, and desktop
- Branded scrollbars that adapt to the current theme

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 · Laravel 13 |
| Frontend | Vue 3 (Composition API) · Inertia.js v3 |
| Styling | Tailwind CSS v4 |
| Charts | ApexCharts · vue3-apexcharts |
| Icons | Heroicons v2 |
| UI Utilities | @vueuse/core · @headlessui/vue · dayjs |
| AI | openai-php/laravel |
| PDF | barryvdh/laravel-dompdf |
| Build | Vite 8 |
| Database | SQLite (default) · any Laravel-supported DB |
| Testing | Pest v4 |

---

## Requirements

- PHP >= 8.3
- Composer
- Node.js >= 18
- An OpenAI API key (for SOAP note generation)

---

## Setup

### 1. Clone the repository

```bash
git clone <repo-url> smarthealth
cd smarthealth
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set:

```env
# OpenAI — required for SOAP note generation
OPENAI_API_KEY=sk-...
OPENAI_ORGANIZATION=   # optional

# Database — SQLite is used by default, no changes needed
# To use MySQL/Postgres, update DB_CONNECTION and credentials
DB_CONNECTION=sqlite
```

### 4. Set up the database

```bash
# Create the SQLite file (skip if using MySQL/Postgres)
touch database/database.sqlite

# Run all migrations
php artisan migrate

# Seed demo data (admin, doctors, patients, appointments)
php artisan db:seed
```

Demo accounts created by the seeder:

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Doctor | doctor@example.com | password |
| Patient | patient@example.com | password |

### 5. Build frontend assets

```bash
# Production build
npm run build

# Or start the dev server with hot reload
npm run dev
```

### 6. Start the application

```bash
# Starts Laravel + queue worker + log viewer + Vite dev server together
composer run dev
```

Then visit `http://localhost:8000` and log in with one of the demo accounts above.

---

## Running Tests

```bash
composer run test
# or
php artisan test
```

---

## Project Structure

```
app/
  Http/Controllers/     — resource controllers (one per module)
  Models/               — Eloquent models with relationships
  Notifications/        — database notification classes
  Services/             — ClinicalScribeService (OpenAI integration)
resources/
  js/
    Layouts/            — SmartHealthLayout.vue (sidebar shell)
    Pages/              — Inertia page components per module
    Components/         — StatCard, Card, StatusPill, SearchableSelect …
  css/app.css           — Tailwind v4 theme tokens + custom scrollbars
  views/pdf/            — Blade PDF templates
database/
  migrations/           — ordered schema migrations
  seeders/              — RoleSeeder, DemoSeeder
routes/web.php          — role-gated route groups
```

---

## License

MIT
