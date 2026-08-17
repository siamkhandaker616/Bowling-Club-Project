# The Tenth Frame — Project Documentation

## 1. Project Overview

`The Tenth Frame` is a Laravel 12 / MySQL bowling club management simulator for CSE 391. It combines a public-facing club website (facility map, fixtures, events, pro shop, touring portal) with a full day-by-day simulation layer where a manager oversees AI staff (stewards, caretakers), each with personality traits, happiness, relationships, and autonomous behaviours. Visitors spawn, book lanes, file complaints, and leave reviews. An HTML5 Canvas bowling mini-game with standard 10th-frame scoring and a leaderboard is included.

The design follows an "Oil Alley" retro bowling aesthetic — dark navy/teal palette, neon accents, custom CSS-v4 controls (lane-range sliders, pin-check toggles, fold-selects, `.lc` calendar), and a Module Dock navigation rail per role. No frontend framework is used; all interactivity is vanilla JavaScript.

Built with PHP 8.2+, Laravel 12, MySQL 8 (MariaDB compatible), Tailwind CSS, Vite, and vanilla JS. Google OAuth via Socialite. SSL Commerz for payments (Bangladesh).

## 2. Setup

### Local (XAMPP)

1. Place the `bowling/` folder in your web server root (e.g. `D:\CSE391\bowling`).
2. Copy `.env.example` to `.env` and update DB credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
3. Run `php artisan key:generate` to set `APP_KEY`.
4. Import the database: `php artisan migrate --force` then `php artisan db:seed`.
5. Build frontend assets: `npm install && npm run build`.
6. Start Apache + MySQL from XAMPP Control Panel.
7. Open `http://localhost:8020` in a browser (or set `php artisan serve --port=8020`).

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Manager (admin) | `admin@tenthframe.club` | `password` |
| Steward | `steward@tenthframe.club` | `password` |
| Caretaker | `caretaker@tenthframe.club` | `password` |
| Visitor (customer) | `visitor@tenthframe.club` | `password` |

### Full Dev Stack

```
composer dev
```

Runs concurrently: `artisan serve --port=8020`, `queue:listen`, `pail` (logs), `vite dev`.

## 3. File Structure

```text
bowling/
├── app/
│   ├── Console/Commands/          # ReconcilePayments scheduled command
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Customer/          # Visitor dashboard
│   │   │   ├── Game/              # Mini-game + leaderboard
│   │   │   ├── PublicPortal/      # Fixtures, events, touring, pro shop, stats, payments
│   │   │   ├── PublicSite/        # Facility map, snackbar, announcements
│   │   │   ├── Sim/               # Simulation controllers
│   │   │   │   ├── Caretaker/     # Shifts, inventory, crew chat, prep
│   │   │   │   ├── Manager/       # Day cycle, staff, inventory, bookings, bans, complaints, confrontations, league, touring, reviews, purchases
│   │   │   │   ├── Steward/       # Schedule, visitors, bans, complaints, snitch
│   │   │   │   └── Visitor/       # Bookings, queue, reviews, complaints
│   │   │   └── Steward/           # Steward dashboard
│   │   └── Middleware/
│   │       ├── CatchUpSim.php     # Auto-advance sim on page load
│   │       └── EnsureRole.php     # Role gate (admin/steward/caretaker/customer)
│   ├── Models/                    # 44 Eloquent models
│   └── Services/
│       ├── Bowling/ScoringEngine.php
│       ├── Payments/              # SslCommerzGateway, PaymentSettler
│       └── Simulation/            # DayCycle, Clock, AccidentEngine, ConfrontationService,
│                                  # InterrogationEngine, CrewChatEngine, DialogueService,
│                                  # SocialEngine, InventoryService, PurchaseBillService,
│                                  # MatchService, VisitorSpawner, VisitorRegistry
├── database/
│   ├── factories/                 # Model factories
│   ├── migrations/                # 48 migration files (~40 domain tables)
│   └── seeders/                   # Site, Portal, Event, ProShop, BowlingScore, SimulationData, InventoryCategories, etc.
├── docs/
│   ├── build/                     # Ownership contract (CONTEXT.md), agent reports
│   │   ├── CONTEXT.md
│   │   └── REPORTS/               # AUDIT_REPORT.md, FEATURES.md, LAYER_A/B/C/D.md, etc.
│   ├── CSExxx_ID_NAME_ApplicationName.md   # Course submission template
│   ├── PROJECT NOTES.md           # Spec + design decisions + footnotes
│   └── project_documentation.md   # This file
├── public/                        # Web root
├── resources/
│   └── views/
│       ├── auth/                  # Login, register, forgot/reset password, email verify
│       ├── components/            # Blade components (modal, toast, input, button, nav)
│       ├── dashboards/            # Manager, steward, caretaker, customer dashboard shells
│       ├── game/                  # Mini-game canvas + leaderboard
│       ├── layouts/               # app.blade.php (main), guest.blade.php (auth)
│       ├── mail/                  # RsvpConfirmation, RsvpReceipt, OrderReceipt, TouringWelcome
│       ├── portal/                # Fixtures, stats, events, touring, pro shop, payments
│       ├── profile/               # Profile edit + delete
│       ├── site/                  # Facility map, snackbar, announcements
│       └── sim/                   # Simulation views
│           ├── caretaker/         # Shifts, inventory, crew chat, prep
│           ├── manager/           # Bookings, complaints, confrontations, inventory, league, staff, touring, reviews, bans
│           ├── partials/          # Sidebar navs, Module Dock, bubble, dialogue, settings
│           ├── reapply/           # Staff reapply form
│           ├── steward/           # Schedule, visitors, complaints, bans, snitch
│           └── visitor/           # Bookings, queue, reviews, complaints
├── routes/
│   ├── auth.php                   # Breeze auth routes
│   ├── console.php                # Scheduled commands (reconcile)
│   ├── core.php                   # Public site + portal (A_1/A_2)
│   ├── game.php                   # Mini-game
│   ├── sim.php                    # Simulation (manager/steward/caretaker/visitor)
│   └── web.php                    # Auth + dashboards + profile
├── tailwind.config.js             # Tailwind config (Figtree font, forms plugin)
├── vite.config.js                 # Vite build config
└── composer.json                  # PHP dependencies
```

## 4. Pages

### Welcome / Home (`/`)

Oil Alley hero section, live lane availability widget (15 s JSON polling), bar OPEN/CLOSED status with countdown, announcement ticker (marquee), pro-shop bag badge, and scroll-reveal animations (IntersectionObserver). Links to facility map, fixtures, events, stats.

### Facility Map (`/facility-map`)

Interactive SVG map of the bowling club. Hover cards show zone details; click to expand. Deep-link via `?lane=N` routes directly into the booking form.

### Fixtures & Results (`/fixtures`)

Filter by league, team, date range, and status. Animated "Next Match" card. Win/Draw/Loss stacked bars and full standings.

### Events (`/events`, `/events/{event}`)

Social event hub with live countdown timers, capacity bars, RSVP tracking, and paid tickets via SSL Commerz. Mailable confirmation + receipt.

### Pro Shop (`/pro-shop`)

Product catalog with Oil Alley cards. Session-based cart with quantity stepper. Checkout with locked stock re-check, SSL Commerz or offline path, order receipt page.

### Touring (`/touring`)

Touring team request form → `touring_requests` DB record → mailable welcome pack + browser-print welcome page. Manager confirms/declines from `/manager/touring`.

### Season Statistics (`/stats`)

W/D/L stacked bars per team, rotating Player Spotlight card.

### Snackbar (`/snackbar`)

Club snack-bar menu display.

### Bowling Mini-Game (`/game`)

HTML5 Canvas top-down lane. Ball curve physics (aim, hook, power tiers). Pin physics + collision. Standard bowling scoring including 10th frame (server-side `ScoringEngine` validates + cross-checks client total). Leaderboard (top-10 + personal best).

### Manager Dashboard (`/manager`)

End-of-Day report card, day advance (14-step pipeline), Bad-Day toggle, Module Dock. Sub-pages: staff CRUD + bonus/penalty, inventory CRUD + restock + purchase bills, booking overview, ban requests, complaints, confrontations (with DM-style interrogation), league, touring, reviews.

### Steward Dashboard (`/steward`)

Shift schedule with Mark Complete, visitor management + staff reviews, ban request creation, complaint intake + escalation, snitch reports (escalate/dismiss).

### Caretaker Dashboard (`/caretaker`)

Shift list (complete/cancel), inventory view + adjust + restock, crew chat (DM threads, snitch reports, confrontation responses), fixture prep (welcome/kits/lane/training readiness).

### Visitor Dashboard (`/visitor`)

Lane booking (calendar + lane rack), queue display, reviews + helpful votes, complaints.

### Auth Pages (`/login`, `/register`, etc.)

Laravel Breeze auth with Oil Alley split-screen design. Google OAuth via Socialite. Polka-dot demo credential memo on login.

### Profile (`/profile`)

Edit profile information, update password, delete account.

## 5. Database Structure

48 migration files, approximately 40 domain tables across 6 logical groups:

### Auth & Infrastructure

| Table | Purpose |
|-------|---------|
| `users` | Email/password auth, role field, avatar, google_id, is_npc flag |
| `sessions`, `cache`, `jobs`, `failed_jobs` | Laravel infrastructure |

### Club & Facility (A_1)

| Table | Purpose |
|-------|---------|
| `clubs` | Club name, hours, lane count, pro-shop/bar/arcade status |
| `club_configs` | Singleton: sim day, reputation, revenue/expenses, bad_day_mode |
| `facility_zones` | Map zones with JSON facilities, open/close times |
| `announcements` | Priority (normal/urgent), active toggle, publish date |
| `snackbar_items` | Snack-bar menu items |
| `lanes` | Lane number, status (open/occupied/maintenance/reserved), oil_level |
| `lane_bookings` | Visitor + lane + date + time_slot + status + compensation |
| `booking_queues` | Queue position per booking |
| `bowling_scores` | Score + frames_data (JSON), is_high_score flag |

### Staff & Personnel (B)

| Table | Purpose |
|-------|---------|
| `staff` | Role (manager/steward/caretaker), salary, happiness, performance, honesty, warnings |
| `personalities` | 8 seeded personality traits |
| `staff_personalities` | Pivot: staff ↔ personality |
| `shifts` | Staff + date + time_slot + lane + status |
| `staff_events` | Event log (type, severity, happiness_change) |
| `staff_relationships` | Pairwise relationship level (hostile/neutral/friendly/trusted) + score |
| `staff_messages` | Chat messages (speech/thought bubble, DM threads) |
| `staff_reviews` | Staff review of visitors |
| `penalties` | Pay dock, extra hours, written warning |
| `bonuses` | Cash, time off, recognition |

### Discipline & Complaints (B)

| Table | Purpose |
|-------|---------|
| `accidents` | Staff accident (type, severity, resolved) |
| `confrontations` | Reporter vs accused; investigation result; manager verdict |
| `snitch_reports` | Staff snitch → steward escalation |
| `complaints` | Visitor/staff complaints, status, compensation_type, resolution |
| `ban_requests` | Steward proposes → admin approves/denies |

### Visitors & Reviews (A_1/B)

| Table | Purpose |
|-------|---------|
| `visitors` | Name, tier (regular/premium), reputation_score, is_banned |
| `visitor_reviews` | Rating + body + helpful/not_helpful counts |
| `review_votes` | Individual votes on reviews |

### League & Fixtures (A_2)

| Table | Purpose |
|-------|---------|
| `leagues` | League name + season |
| `teams` | Team name + league + W/D/L |
| `team_members` | Team roster with average_score |
| `fixtures` | Home vs away, date, lane, score, status |
| `fixture_preps` | Match prep readiness (kits, lane, training, welcome) |

### Events, Payments & Pro Shop (A_2)

| Table | Purpose |
|-------|---------|
| `events` | Title, date, venue, capacity, price |
| `rsvps` | Event RSVP with status |
| `payments` | Polymorphic (Event/Rsvp/Order/InventoryPurchase), SSL Commerz fields |
| `touring_requests` | Touring team requests |
| `products` | Pro shop items (name, price, stock, category) |
| `cart_items` | Session-based cart |
| `product_orders` + `order_items` | Order records |

### Inventory (B)

| Table | Purpose |
|-------|---------|
| `inventories` | Item name, category, quantity, max, condition, reorder threshold, cost |
| `inventory_events` | Stock change log |
| `inventory_purchases` | Purchase bills: caretaker requests → manager accept/reject |

## 6. Features

### 6.1 Public Website — Homepage & Live Widgets

**What it does:** The homepage shows an Oil Alley hero, a live lane availability widget (15 s polling via `/api/lanes`), a bar OPEN/CLOSED status with countdown, and a scrolling announcement ticker. Scroll-reveal animations trigger on viewport entry (IntersectionObserver with `prefers-reduced-motion` guards).

**How it works:** Lane data is fetched via `GET /api/lanes` returning JSON from the `lanes` table. Bar status reads `clubs.bar_open_hours`/`bar_close_hours` and computes remaining time client-side. Announcements are query-scoped to `is_active` and `published_at <= now()`. Scroll reveals use `IntersectionObserver` with `threshold: 0.1` and `.reveal` CSS class toggling.

### 6.2 Interactive SVG Facility Map

**What it does:** An SVG map of the club with hover cards showing zone details (name, facilities, hours). Clicking a zone expands it. A `?lane=N` query parameter deep-links directly into the booking form with that lane pre-selected.

**How it works:** Zone data comes from `facility_zones` (name, map_key, open/close times, JSON facilities). The SVG uses CSS hover transitions for card reveal. JavaScript handles `?lane=N` parsing and scrolls to + pre-selects the booking form.

### 6.3 Fixtures, Standings & Statistics

**What it does:** A filterable fixtures dashboard (league, team, date range, status) with an animated "Next Match" card. A statistics page shows W/D/L stacked bars per team and a rotating Player Spotlight.

**How it works:** Fixtures are queried with `when()` chain for each filter parameter. Standings are computed from `teams` wins/draws/losses, sorted wins-primary. The Player Spotlight cycles through `team_members` by average score. Scores are populated by `MatchService` during day-advance.

### 6.4 Events, RSVP & Payments

**What it does:** Social event cards with live countdown timers, capacity bars, and RSVP buttons. Paid events use SSL Commerz gateway — checkout, IPN webhook, success/fail/cancel callbacks. Capacity is row-locked to prevent double-booking. Mailable confirmation + receipt via `RsvpConfirmation` and `RsvpReceipt`.

**How it works:** RSVP uses `DB::transaction` + `lockForUpdate` on the event row to atomically check and increment `current_rsvps`. `PaymentSettler` handles status transitions with atomic CAS (skip non-`processing`). Reconcile command (`payments:reconcile --stale=24`) expires abandoned payments and releases held capacity. IPN verifies `sign` via official md5/ksort algorithm.

### 6.5 Pro Shop

**What it does:** Product catalog with Oil Alley themed cards, session-based cart with quantity stepper, checkout with locked stock re-check, SSL Commerz or offline dev path, and order receipt page.

**How it works:** Cart is session-keyed (`session_id`). Checkout re-checks stock inside `DB::transaction` + `lockForUpdate`, then calls `ProductOrder::fulfill()` (row-locked) to decrement stock. Cart is cleared by the owning session at confirmation.

### 6.6 Touring Team Portal

**What it does:** Touring teams submit a request form (team name, home club, arrival date, player count, message). The manager confirms or declines. Confirmed teams receive a mailable welcome pack and can access a browser-print welcome page.

**How it works:** Request stored in `touring_requests` (status: pending/confirmed/declined). Manager actions at `/manager/touring/{id}/confirm|decline` flip status. Welcome email sent on confirm.

### 6.7 Simulation — Day Cycle Engine

**What it does:** The core simulation advances one day per manual "Next Day" click. The 14-step pipeline runs in order:

1. **Promote queues** — waiting visitors promoted by reputation
2. **Serve today's bookings** — mark completed
3. **Expire old queues** — remove stale queue entries
4. **Accidents** — per-role base chance × personality/happiness/oil level; bad-day forces 100%
5. **Happiness drift** — personality + relationship + salary influence
6. **Daily drift** — gradual attribute changes
7. **Inventory decay** — daily quantity reduction
8. **Auto-complaints** — 10% regular / 6% premium chance per booking
9. **Resolve due matches** — league fixtures scheduled for today
10. **Resolve completed matches** — finalize scores, update standings
11. **Finance** — salaries/30 + accident costs; revenue from bookings/league
12. **Update reputation** — aggregate signal from accidents, complaints, reviews, quits
13. **Day++** — increment `current_day`
14. **Ensure schedule** — auto-generate tomorrow's shifts

**How it works:** `DayCycle::advance()` is called by `DayController@advance` (POST). `CatchUpSim` middleware auto-advances on page load if real days have elapsed since `last_advanced_at` (up to 14 catches).

### 6.8 Simulation — Staff Management

**What it does:** Hire staff with 2–4 personality traits (enforced conflict map), edit profiles, fire (soft-delete), reapply (new-identity reset). Happiness drifts daily; staff quit at ≤19 happiness (50% chance). Pay dock reduces salary and happiness; bonuses restore happiness.

**How it works:** Hire creates `staff` + `users` records + `staff_personalities` pivot. Fire sets `is_active = 0` + deactivates user. Reapply resets happiness/performance/honesty to defaults, generates new identity, re-activates. `SocialEngine` computes daily happiness drift from traits, relationships, and salary deviations.

### 6.9 Simulation — Confrontation & Interrogation

**What it does:** Staff confrontations follow a lifecycle: reporter accuses → auto-investigation → DM-style interrogation (chip questions: where were you, log check, witness, reporter credibility) → manager verdict (upheld / dismissed / penalized / reporter_penalized). Verdicts create Penalty/Bonus records and adjust happiness.

**How it works:** `ConfrontationService` manages the lifecycle. `InterrogationEngine` generates chip-based questions and a conclude narrative. Verdicts: upheld (accused −15 happiness), dismissed (−5), penalized (creates Penalty record), reporter_penalized (accused +5, reporter −12 + warning). Interrogation runs in a modal with chip selection and auto-conclude.

### 6.10 Simulation — Snitch System & Crew Chat

**What it does:** Staff can vent in crew chat; a snitch roll (0.06 base + personality modifier) generates `SnitchReport` entries. Stewards escalate snitch reports into confrontations. Crew chat supports DM threads, speech/thought bubbles, chip reactions, and JSON-based polling.

**How it works:** `CrewChatEngine` generates daily chatter. `SnitchReport` → steward review at `/steward/snitch` → escalate to confrontation or dismiss. Chat polling via `GET /caretaker/crew/messages` returns JSON.

### 6.11 Simulation — Inventory & Purchase Bills

**What it does:** Inventory items have quantity, max, condition, and reorder threshold. Daily decay reduces stock. Accident damage maps type → item. Caretaker restock creates a pending purchase bill; manager accepts (pays via SSL Commerz) or rejects (reverts stock, fines consumed portion). Manager's own restocks auto-approve.

**How it works:** `InventoryService` handles CRUD, adjust, restock, daily decay, and damage. `PurchaseBillService` manages the bill lifecycle. Bills are stored in `inventory_purchases` (status: pending/approved/rejected) with polymorphic `payments` record for accepted bills. Dev mode simulates payment without real gateway.

### 6.12 Simulation — Complaints & Compensation

**What it does:** Complaints arise from auto-generation (10%/6% per booking), visitor reports, or steward escalation. Manager resolves with compensation: free_game (works), priority_queue (partial — position 0), refund/discount/apology (inert — validated and stored but no effect).

**How it works:** `ComplaintController@resolve` stores `compensation_type` on the complaint. `free_game` sets `compensation_claimed` on the booking. `priority_queue` sets position 0 but reputation sort can override.

### 6.13 Simulation — Visitor Booking & Queue

**What it does:** Visitors book lanes via a calendar + lane rack UI. Duplicate guard prevents double-booking. Conflicts push into a waiting queue. Queue position is displayed in real-time. Promotion happens on cancel or day-advance.

**How it works:** `BookingController@create` shows available lanes. `store` uses row-locked capacity check. Conflicts create `booking_queues` entries. `DayCycle::advance` promotes queue by reputation DESC.

### 6.14 Bowling Mini-Game

**What it does:** Top-down HTML5 Canvas bowling lane. Player aims with angle, adjusts power, and releases. Ball follows curve physics (hook). Pins have velocity + collision detection. Standard bowling scoring including 10th frame bonus. Leaderboard shows top-10 + personal best.

**How it works:** `game.js` manages Canvas rendering (`drawLane`, `PIN_SPOTS`, `drawPin`), ball physics (aim, hook, power tiers, MAX burst), and pin collision. `ScoringEngine` validates frames server-side; `ScoreController@store` cross-checks total. Leaderboard at `/game/leaderboard` queries top-10 + session personal best.

### 6.15 Design System & Controls

**What it does:** "Oil Alley" retro bowling theme with CSS custom properties, dark navy/teal palette, neon accents. Custom v4 controls: `.lc` calendar, `.br-wrap` lane rack, `.pin-check` toggle, `.fold-select` dropdown, `.stepper` number input, `.lane-range` slider.

**How it works:** `design-system.css` defines `:root` variables and component classes. `controls.js` binds custom controls. `fold-select.js` and `stepper.js` handle custom select/number inputs. `Module Dock` (`sim/partials/module-dock.blade.php`) provides per-role navigation rail with day chip, Next Day button, and Bad-Day toggle. Responsive breakpoints at 900px collapse to compact sidebar.

## 7. Theme & Customization

### 7.1 Oil Alley Design Language

The interface evokes a retro bowling alley — dark backgrounds (navy `#0f172a`, slate `#1e293b`), neon teal (`#0d9488`) and gold (`#f59e0b`) accents, warm amber lighting tones. Cards use subtle glass-morphism (backdrop-blur + semi-transparent backgrounds). Typography uses **Figtree** (primary sans-serif via Tailwind config).

### 7.2 CSS Custom Properties

The design system uses `:root` CSS variables for consistent theming across all components:

- **Colour palette:** navy/slate backgrounds, teal primary, gold accent, rose danger, emerald success
- **Spacing:** consistent scale via Tailwind defaults
- **Typography:** Figtree as primary, system fallbacks
- **Shadows:** layered box-shadows for depth (card, elevated, floating)
- **Border radius:** consistent rounding across cards, buttons, inputs

### 7.3 v4 Custom Controls

| Control | Class | Purpose |
|---------|-------|---------|
| Calendar | `.lc` | Date picker with month navigation, today highlight |
| Lane Rack | `.br-wrap` | Grid of lane buttons, colour-coded by status |
| Pin Check | `.pin-check` | Toggle styled as bowling pin |
| Fold Select | `.fold-select` | Custom dropdown replacing native `<select>` |
| Stepper | `.stepper` | Number input with +/- buttons |
| Lane Range | `.lane-range` | Dual-thumb range slider for lane selection |
| Bound Row | `.br-wrap` | Form row binding control to label |

### 7.4 Responsive Design

- Desktop: Module Dock left rail + main content grid
- Mobile (< 900px): Compact sidebar with collapsible sections, full-width content
- No `zoom:` hacks — uses `transform: scale()` where needed
- `prefers-reduced-motion` respected on animation-heavy pages

### 7.5 Module Dock

Per-role navigation rail (`.module-dock`) with:
- Day chip (current simulation day)
- Next Day button
- Bad-Day toggle
- Role-specific links (33 verified routes across 4 roles)
- Dashboard link with role-specific icon

## 8. Notes

- The `APP_ENV` defaults to `production` in `config/app.php`; set to `local` for development.
- SSL Commerz runs in sandbox mode (`SSLCZ_SANDBOX=true` in `.env`) — no real money moves.
- Groq LLM integration is wired but disabled (`config('services.groq.enabled', false)`). NPC dialogue is rule-based with fallback.
- The simulation only advances on manual "Next Day" clicks — no auto/scheduled advancement.
- 4 seeded league fixtures — no runtime fixture generation, so league play ends after they resolve.
- 3 of 5 complaint compensation options (refund, discount, apology) are validated and stored but have no runtime effect.
- 156 tests / 771 assertions cover the simulation, scoring, payments, and booking logic.
- All SQL queries use Eloquent ORM with parameterized bindings — no raw query concatenation.
- CSRF protection via `@csrf` on all forms; API endpoints use `throttle` middleware.
