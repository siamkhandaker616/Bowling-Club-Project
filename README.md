# The Tenth Frame Bowling

A private bowling club website and management simulation built with Laravel — the CSE391 final project.

Every frame counts. The club runs a public-facing site for bookings, complaints, compensation and reviews, plus a role-based management layer where a manager runs the club day to day: staffing lanes, handling accidents, issuing penalties and bonuses, and keeping the reputation alive.

## Features

- Role-based dashboards for **manager**, **steward**, **caretaker** and **visitor** accounts
- Lane booking with date/time slots, wait queues and compensation claims
- Complaint handling, staff reviews, visitor reviews and voting
- Staff simulation: hiring, personalities, shifts, accidents, penalties and bonuses
- Crew chat with async NPC responses, typing indicators, and personality-driven dialogue
- Confrontation & interrogation system with context-aware chip questions and verdicts
- Snitch reports escalated from crew chat vents
- Inventory management with purchase bill approval pipeline
- League match prep with readiness tracking
- Google sign-in via Laravel Socialite alongside regular accounts
- Custom design system (see `resources/css/design-system.css`)

Full feature breakdowns and the 40+ table database schema live in the [docs](/docs) folder.

## Tech stack

- **Laravel 12** (Blade templating, Eloquent ORM, migrations, queues)
- **MySQL**
- **Tailwind CSS** + **Vite**
- **Vanilla JS** (no frontend framework)
- **Groq API** (LLM-powered NPC dialogue with rule-based fallback)
- **Laravel Socialite** (Google OAuth)
- **SSL Commerz** (Bangladesh payment gateway)

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` to point at your database, then:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve --port=8020
```

For local development with live reload, run `npm run dev` instead of `npm run build`.

The seeder creates the club plus role accounts (manager, steward, caretaker, customer) — see `database/seeders/DatabaseSeeder.php` for credentials.

## Project structure

- `app/` — models, controllers, middleware, and services (Simulation, Bowling, Payments)
- `routes/` — auth, public site, simulation, and game routes
- `database/` — 53 migrations, factories, and seeders (including SimulationDataSeeder)
- `resources/views/` — Blade templates, dashboards, and the mini-game
- `docs/` — project documentation, design notes, and CSE391 deliverables

## License

MIT
