# CSE391 Final Project — Cloud Nine Bowling / The 10th Frame

Laravel-based website + management simulation game, built from scratch with minimal frontend frameworks (Tailwind utilities + custom CSS + vanilla JS). Ten-pin bowling private club.

## Confirmed Decisions

- **Sport:** Ten-pin bowling (not lawn bowls)
- **Project name:** 
  - Backend/internal: Cloud Nine Bowling
  - Frontend/display: The 10th Frame (temporary, will rebrand later)
- **Color palette:** Cloud Nine (Option 1)
  - Primary: Baby blue (#a8d8ea)
  - Backgrounds: Cloud white (#f5f8fa), Mist (#eef3f7)
  - Neutrals: Navy (#1a2a3a), Slate (#6b7a8d), Fog (#b0b8c1)
  - Accents: Gold (#d4a84c), Coral (#e86c6c)
  - Bowling-specific: Lane wood (#c9a86c), Pin white (#f8f6f0), Rubber (#3a3a3a)
- **Fonts:** 
  - Bowlby One SC (display headings)
  - Bungee (section headers, buttons)
  - Righteous (sub-headings, nav)
  - Manrope (body text, forms)
  - Share Tech Mono (scores, numbers)
- **Design depth:** Match Mayhem Mobility level (~2800 lines CSS), but completely unique to bowling. No element-for-element swaps from previous projects.
- **Design system file:** design-system.css (saved in project root)

---

## Tech Stack

- **Backend:** Laravel (Blade templating, Eloquent ORM, migrations, controllers)
- **Frontend:** Tailwind CSS (utility classes) + custom design-system.css, Vanilla JavaScript (no React/Vue/Alpine)
- **Mini-game:** HTML5 Canvas API (virtual bowling)
- **Database:** MySQL/MariaDB
- **Philosophy:** Build everything from scratch. No heavy frontend frameworks. Clean, hand-written code.

---

## Job Listing (Inspiration)

- **Platform:** Upwork
- **URL:** https://www.upwork.com/freelance-jobs/apply/Website-Redesign-for-Bowls-Club_~022077351675053091061/
- **Original brief:** Website redesign for Bodmin Bowls Club (Cornwall, UK) — lawn bowls club
- **Our adaptation:** Ten-pin bowling private club. Same club structure (fixtures, events, bar, members, touring teams), different sport. Designing from scratch as a management simulation game.

---

## Project Structure — Three Layers

### A. Public Website Layer
The "real" bowls club site. Anyone can browse. Standard club website with our 10 unique features.

### B. Simulation Layer
The game. Two perspectives that interact with each other:
- **Manager Mode:** Run the club. Manage staff, handle accidents, issue penalties/bonuses, maintain the facility.
- **Client/Visitor Mode:** Use the club. Book lanes, attend events, leave reviews, get affected by staff quality, potentially get banned.

### C. Mini-Game Layer
Virtual bowling. A standalone canvas-based bowling game accessible from the site. Just for fun.

### Day Cycle System (Mayhem Mobility style)
- **Sim Mode:** Manual "Next Day" button. Events trigger on each advance. Faster pacing for testing/demo.
- **Real-Time Mode:** Events happen on a timer. More realistic pacing. Like how Mayhem Mobility had simulated clock vs real time.

---

## Design Direction (Confirmed)

### Visual Language (Bowling-Specific, Not From Mayhem Mobility)

**Core elements:**
- Lane stripe dividers (horizontal lines with wood grain pattern)
- Pin silhouettes as bullet points/status indicators
- Bowling ball accents for team/player indicators
- Scorecard grids for data display
- Wood grain texture backgrounds
- Gutter curve borders on cards
- Clean, modern layouts

**What's included (if it makes sense for bowling):**
- Receipt/booking confirmation design (bowling ticket style)
- Settings gear (lane oil, shoe rental, music toggles)
- Doodles/animations (bowling-specific: ball rolling, pin scattering)
- Speech bubbles (for staff dialogue, confrontation system)

**What's NOT included (just because Mayhem had it):**
- Spotlight beam (not needed for bowling)
- Watermark text (not needed)
- Display tuning settings (not needed)
- Error burst animations (different interaction model)

**Design philosophy:** Each element must serve bowling's actual needs. No element-for-element swaps from previous projects. If it doesn't make sense for a bowling club, it doesn't go in.

### Color Palette (Cloud Nine)

```css
:root {
    /* Primary */
    --sky: #a8d8ea;
    --sky-light: #c5e8f5;
    --sky-dark: #7ab8d4;
    
    /* Backgrounds */
    --cloud: #f5f8fa;
    --mist: #eef3f7;
    
    /* Neutrals */
    --navy: #1a2a3a;
    --slate: #6b7a8d;
    --fog: #b0b8c1;
    
    /* Accents */
    --gold: #d4a84c;
    --gold-light: #e8c878;
    --coral: #e86c6c;
    --coral-light: #f09090;
    
    /* Bowling-Specific */
    --lane-wood: #c9a86c;
    --pin-white: #f8f6f0;
    --rubber: #3a3a3a;
}
```

### Typography

| Font | Use | Source |
|---|---|---|
| Bowlby One SC | Display headings | Google Fonts |
| Bungee | Section headers, buttons | Google Fonts |
| Righteous | Sub-headings, nav links | Google Fonts |
| Manrope | Body text, forms | Google Fonts |
| Share Tech Mono | Scores, numbers, stats | Google Fonts |

### CSS Architecture

- **File:** design-system.css (saved in project root)
- **Variables:** All colors, fonts, shadows as CSS custom properties
- **Components:** Reusable bowling-specific components (lane-stripe, pin-accent, ball-accent, scorecard, shoe-tag, etc.)
- **Animations:** Bowling-specific (ballRoll, pinScatter, strikeBurst, gutterWobble, scoreTick, shoeSwing)
- **Responsive:** Mobile-first approach
- **Accessibility:** prefers-reduced-motion support, focus-visible states
- **Print:** Print stylesheet included

### Easter Eggs (Bowling-Specific)

- Strike animation on successful form submission
- Scoreboard stats in LED-style counter
- 404 page: "Gutter Ball!" with illustration
- Loading state: bowling pin setup animation
- Hover on team cards: pin wobble animation

---

## A. PUBLIC WEBSITE FEATURES (1–10)

1. **Interactive SVG Facility Map** — Clickable illustrated map of the club grounds (lanes, pro shop, bar, arcade area, lounge, parking). Hover tooltips + click-to-expand info panels.

2. **Dynamic Fixture & Results Dashboard** — Filterable by league, team, date. Color-coded W/L/D indicators, animated tab switching, "next match" highlight card. Laravel Eloquent queries behind it.

3. **Lane Availability Widget** — Real-time display showing which lanes are open, occupied, or reserved. Visual lane map with status indicators. No weather dependency for indoor bowling.

4. **Touring Team Welcome Portal** — Booking request form, downloadable PDF welcome pack (generated from form input), nearby amenities directory, embedded directions.

5. **Social Event Hub with Countdown Timers** — Event cards with live JS countdowns, venue details, RSVP button that stores responses in DB and notifies the club secretary via Laravel Mailable.

6. **Live Bar Status Display** — Homepage widget showing OPEN / CLOSED with a live countdown to the next opening period. Driven by configurable hours stored in the database.

7. **Season Statistics Dashboard** — Win/loss bar charts, per-team performance cards, "Player Spotlight" rotation. Lightweight chart rendering, all data pulled from Eloquent models.

8. **Announcement Ticker** — Scrolling marquee for breaking news/urgent updates. CMS-editable via a simple admin CRUD panel.

9. **Club Events RSVP with Capacity Tracking** — Events have max capacity, users RSVP, system tracks attendance with a visual progress bar showing spots remaining. Database relations + AJAX updates.

10. **Animated Micro-interactions & Scroll Reveals** — Fade-in-on-scroll, hover lift effects, smooth page transitions, subtle parallax. Pure frontend polish layered on Blade templates.

---

## B. SIMULATION LAYER

### Feature 11: Manager Mode — Staff Management Sim

A management simulation game where you run the bowls club. Staff members have daily tasks, random events can go wrong, and your decisions affect morale and retention. Think "Theme Hospital meets a bowls club."

#### Staff System

- **3 role tiers (real people can be any of these):**
  - **Club Manager:** The end all be all. Full control. When not logged in, AI steward handles day-to-day.
  - **Steward:** The middle man. Handles day-to-day operations, client comms, schedule management, can request bans from admin. 2 total (1 is player when they log in, 1 is always AI).
  - **Caretaker:** The workers. Work in groups on different tasks (cleaning, bar, grounds, etc.). Player can join any group. AI fills in when player isn't controlling them. (TBD — tentative count, will finalize during development.)
- **Clients (Regular/Premium):** Use the club. Book lanes, attend events, leave reviews. Premium clients may get priority queue or discounts.
- **A real person can have any role** — Club Manager, Steward, or Caretaker depending on the session.
- **2 portraits per staff member:** happy state + disappointed state (placeholders until all features are done)
- **Happiness meter** per staff: 0–100 scale
- **Performance tracking** over time affects bonus eligibility

#### Speech Bubble UI

- **All staff dialogue, reports, and confrontation responses** appear as speech bubble tooltips
- **Video game feel** — like talking to NPCs in an RPG. Text appears in styled bubbles next to the character portrait
- **Bubble types:**
  - Speech bubble (normal dialogue, reports)
  - Thought bubble (internal thoughts, complaints the staff hasn't voiced yet)
  - Exclamation bubble (urgent alerts, accidents, critical events)
  - Question bubble (staff asking for time off, requesting something)
- **Click to dismiss, click to respond** — interactive dialogue flow

#### Dual-Perspective Mode

- **Manager Mode:** Oversee everything. Make decisions. Handle staff, complaints, bans, bonuses.
- **Worker Mode:** Join a shift crew. Maintain lanes, serve drinks, do the job. Experience what your staff goes through. Player-controlled work = ALWAYS successful unless you miss your real-time job (don't show up or don't complete in time).
- **When you're in one mode, AI runs the other.** Event log shows what happened while you were away in the other perspective.

#### Caretaker Social System (Worker Mode)

- **Relationship meter** with each caretaker — ranges from hostile to friendly
- **Benchmark dialogue responses** change based on relationship level:
  - Hostile: guarded, short, potentially hostile responses
  - Neutral: polite but reserved
  - Friendly: open, may share opinions
  - Trusted: will openly bitch about higher-ups to you
- **Snitch mechanic:**
  - If a caretaker trash-talks the boss/management in front of you, you can **snitch to the steward/admin**
  - Snitching = that caretaker gets fired + you get a bonus
  - BUT: if you say risky things yourself, OTHER caretakers can snitch on YOU
  - Creates a paranoia/trust dynamic — who do you vent to? Who's listening?
- **Reapply mechanic (humans only):**
  - Non-NPC humans who get fired can **reapply with different info** (new identity)
  - NPCs who get fired are gone — must be replaced through the hiring system
  - This gives real players a safety net but keeps the stakes real for AI staff

#### Random Accident System

- **Probability-based** — each staff member has a base accident chance per "day/shift"
- **Accident types tied to role:**
  - Caretaker (lane maintenance): lane oil machine breakdown, pin setter jam, lane surface damage
  - Caretaker (bar): accidental discounts given to non-entitled visitors, spilled drinks on equipment
  - Caretaker (cleaning): lane not cleaned properly, shoes not sanitized, pins dirty
  - Steward: schedule conflicts, double-booked lanes, missed escalations
- **Accident severity levels:** minor (inconvenience), moderate (revenue impact), major (member complaints)
- **"Bad Day" toggle:** when enabled, accident probability = 100% for all staff (testing/debugging mode)

#### Manager Actions

- **Club Manager powers:**
  - Ban clients (final decision, no appeal)
  - Fire staff
  - Approve/deny ban requests from stewards
  - Override any schedule
  - Handle all compensation claims
  - Issue penalties and bonuses to all staff
- **Steward powers:**
  - Change staff schedules (within their shift)
  - Talk to clients directly
  - Complain to admin about clients (request bans)
  - Issue written warnings to caretakers
  - Escalate issues to admin
- **Caretaker powers:**
  - View their schedule
  - Request off days
  - Mark appointments as completed (cleaning done, etc.)
  - Report issues to stewards
- **Penalties (issued by admin or steward):**
  - Pay dock (reduces staff salary for the period)
  - Extra hours (extends their next shift)
  - Written warning (tracked, too many = termination)
- **Bonuses (issued by admin):**
  - Performance bonus (reward consistent good performance)
  - Time off (reduce next shift hours)
  - Public recognition (boosts happiness more than money)
- **Consequences of poor management:**
  - Low happiness → increased accident probability
  - Very low happiness → staff quits (must hire replacement)
  - Multiple quits → club reputation drops → fewer visitors

#### Staff Happiness Mechanics

- **Base happiness:** starts at 70/100 on hire
- **Increases:** bonuses, good shifts, recognition, reasonable hours
- **Decreases:** penalties, overwork, accidents they caused, no recognition
- **Thresholds:**
  - 80–100: Happy (lowest accident chance, may receive random positive events)
  - 50–79: Content (normal accident chance)
  - 20–49: Unhappy (elevated accident chance, may complain to other staff)
  - 0–19: Critical (high chance of quitting next "day")

#### Staff Personality Traits

Each staff member has 1-2 personality traits that affect dialogue, behavior, and how they interact with the player and other NPCs.

- **Honest** — likely to confess when confronted, less likely to snitch falsely
- **Stoner/Chill** — laid back, low accident chance but also low productivity, hard to anger
- **Overtly Friendly & Enthusiastic** — high energy, boosts nearby staff happiness, but may annoy others
- **Creepy** — makes unsettling comments, other staff avoid them, higher chance of complaints from clients
- **Nerd** — works part-time, can't stop talking about their interests, high competence but socially awkward
- **Rude** — short with clients, higher complaint rate, but honest (won't snitch falsely)
- **Cliquey** — forms alliances with certain staff, doesn't like the player in particular, may snitch unfairly
- **Opportunistic** — will snitch on others to get ahead, loyal only to themselves, switches sides based on who's winning

#### Inventory Management System

- **Track club supplies across categories:**
  - **Bowling Equipment:** bowling balls (various weights, sold individually or in sets), pins (sets of 10), spare pins, pin spots, ball ramps (for accessibility), bowling shoes (rental stock, various sizes)
  - **Lane Maintenance:** lane oil machines, lane oil/conditioner, lane brushes, lane pads, pin sweepers, ball return mechanisms, lane repair kits
  - **Pro Shop Stock:** ball polish, finger grips, wrist supports, bowling bags, bowling gloves, custom ball drilling supplies, shoe accessories
  - **Arcade & Entertainment:** arcade cabinet supplies (tokens, prizes, replacement parts), pool cues, air hockey pucks, prize machine stock
  - **Bar & Food:** beer kegs, wine bottles, spirits, soft drinks, glassware (pints, cocktail, shot glasses), ice, napkins, coasters, nachos, hot dogs, popcorn, branded cups, condiments
  - **Cleaning:** lane cleaners, ball cleaners, pin cleaners, general cleaning supplies (mops, buckets, chemicals), vacuum supplies, paper towels
  - **Club Facilities:** furniture (chairs, tables, benches, VIP booths), kitchen equipment, first aid supplies, office supplies, sound system equipment
  - **Premium/Posh:** branded merchandise (polo shirts, caps, bowling bags, towels), trophies and league awards, welcome packs for touring teams, signage and displays, event decorations
- **Each item has:** name, category, quantity, condition (good/worn/broken), max capacity
- **Items can be damaged/broken by staff** through accidents (e.g., caretaker drops a bowling ball → cracked → inventory updated)
- **Low stock alerts** when quantity drops below threshold
- **Restock system** — admin can order new supplies (costs money, takes time to arrive)
- **Inventory affects operations:**
  - No clean bowling shoes → can't serve walk-ins → revenue loss
  - Cracked pins → lane unsafe → lane down → fewer bookings
  - Lane oil machine broken → lanes dry out → ball reaction affected → customer complaints
  - Bar stock empty → can't serve → revenue loss + bad reviews
- **Full CRUD** — admin can add, edit, remove items, adjust quantities

#### Grounded LLM System (Groq — Free, Minimal Calls)

The LLM is used sparingly for dialogue moments that benefit from personality. Core simulation runs on rules and probability with zero API calls.

**Uses Groq (free tier):**
- Customer ↔ Steward conversations (client complaint, booking inquiry, general chat)
- Caretaker ↔ Caretaker social dialogue (venting, snitching, relationship building)
- Confrontation responses (staff being accused — confess or BS)
- These are the only API calls, and they're grounded in DB context

**Does NOT use Groq (rule-based):**
- AI Admin decisions (ban approve/deny, schedule overrides, fires)
- AI Steward operations (shift management, escalations, daily reports)
- AI Caretaker behavior (accidents, happiness, task completion)
- NPC customer behavior (booking decisions, complaint filing, reviews)
- Snitch system logic (relationship checks, probability rolls)
- Inventory management (damage, restock, low alerts)
- All probability/accident calculations
- All happiness/performance math

**How Groq calls stay cheap:**
- Context string built from DB before each call (staff names, recent events, inventory status)
- Prompt constrains LLM to only reference real DB entities
- Short prompts, short responses (1-2 sentences each)
- Pre-written fallback strings if API is down or rate-limited
- Only fires on actual player interactions, not every game tick

#### Confrontation Mechanic

When a staff member reports something (via LLM dialogue), the manager can **confront** the involved party to verify.

**How it works:**
1. Steward reports: "Jake broke a mop during the morning shift"
2. Manager chooses to **confront Jake**
3. System queries the DB: is there an accident record for Jake + broken mop + today's shift?
4. **If YES (verifiable):**
   - Jake either **confesses** (based on honesty/personality stat) or **tries to BS**
   - If BS: manager can investigate further (check inventory, ask other staff)
   - If inventory confirms the mop is broken → Jake is caught lying → happiness drops, may get written warning
5. **If NO (not verifiable):**
   - Steward may have been wrong or exaggerating → steward's credibility drops
   - Or the incident wasn't logged properly → system gap

**This creates a trust/detective layer:**
- Not every report is accurate
- Staff have honesty stats that affect their likelihood of confessing vs lying
- Investigation costs time/attention but reveals the truth
- False accusations damage the manager-steward relationship

---

### Feature 12: Client/Visitor Mode

The other side of the simulation. Visitors interact with the club and are affected by staff quality. Bad staff = bad experience = bad reviews = less revenue.

#### Rink Reservation System with Waiting Queue

- **Book a lane** for a desired date/time slot
- **Queue system:** if your desired slot is taken, you join a waiting queue
- **Position in queue** is visible — "You're #2 in line for Lane 5, Saturday 2PM"
- **Auto-notify** when a spot opens (someone cancels or their session ends)
- **Staff-caused delays** can push back your reservation (caretaker took too long cleaning → your lane isn't ready → you get a notification + compensation offer)

#### Client Compensation System

- **If staff screws you over**, you're entitled to compensation
- **Types of compensation:**
  - Free session voucher (next booking is on the house)
  - Discount on membership renewal
  - Priority queue position for next booking
  - Formal apology from club management
- **Filed through a complaint system** — describe what went wrong, system matches it to the staff accident log
- **Manager can approve or deny** compensation claims

#### Reviews & Ratings System

- **Clients review the club** after visits — star rating + written review
- **Other users can rate reviews** — helpful/not helpful voting
- **Staff can review clients too** — was the visitor polite, respectful, caused issues?
- **Two-way reputation:**
  - Club reputation (average of visitor reviews) → affects how many new visitors come
  - Client reputation (average of staff reviews) → affects priority queue position, whether staff go the extra mile
- **Reviews are tied to specific events** — "Booked Lane 5 on Saturday, lane wasn't oiled properly, ball was hooking everywhere"

#### Staff-Client Interactions

- **Staff reviews of visitors** (mentioned above)
- **Staff can request bans** — mid-level staff can complain to admin about a client, requesting a ban
- **Only admin can actually ban** — staff propose, admin disposes. Admin reviews the request and decides.
- **No appeal for banned clients** — admin decision is final. Creates real weight to the ban system.
- **Mid-level staff mediate** between clients and lower staff — they handle complaints, escalate to admin when needed

---

### Feature 13: Virtual Bowling Mini-Game

A standalone HTML5 Canvas bowling game accessible from the site. Just for fun.

- **Top-down view** — more arcade-y feeling, like classic bowling games
- **Ball physics** — roll the ball, it curves, hits pins
- **Pin physics** — pins scatter, fall, score is calculated
- **Scoring** — standard bowling scoring (strikes, spares, frames)
- **Ties into the site** — maybe high scores are displayed on the club leaderboard, or bowling well earns you a small booking discount
- **Built from scratch** — vanilla JS + Canvas API, no game engine
- **Retro aesthetic** — pixel-inspired or arcade-style visuals matching the "Retro Lane" theme

---

## Database Schema (Full)

34 tables — mirrors `SCHEMA ER.md` (the ER diagram deliverable).

```
=== AUTH & SITE-WIDE ===

users
├── id, name, email (unique), email_verified_at
├── password, remember_token
├── role (visitor/caretaker/steward/admin)
├── avatar, phone, is_npc, is_active, google_id

=== PUBLIC WEBSITE ===

clubs
├── id, name, slug (unique), description, logo
├── total_lanes, pro_shop_open, arcade_open
├── bar_open_hours, bar_close_hours
├── address, phone, email, website

facility_zones
├── id, club_id (→ clubs)
├── name (unique), description
├── open_time, close_time (nullable)
├── facilities (JSON), map_key (unique)
├── sort_order, is_active

lanes
├── id, lane_number (unique)
├── status (open/occupied/maintenance/reserved)
├── current_booking_id (nullable)
├── last_maintained_at, oil_level (0-100)

leagues
├── id, name, season

teams
├── id, name, league_id (→ leagues)
├── wins, losses, draws

team_members
├── id, team_id (→ teams), name
├── average_score, created_at

fixtures
├── id, home_team_id, away_team_id (→ teams)
├── date, time, lane_id (nullable, → lanes), league_id (→ leagues)
├── home_score, away_score, status (upcoming/live/completed)

events
├── id, title, description, date, time, venue
├── max_capacity, current_rsvps

rsvps
├── id, event_id (→ events)
├── visitor_name, visitor_email
├── status (confirmed/cancelled)

touring_requests
├── id, team_name, home_club
├── arrival_date, player_count, message
├── status (pending/confirmed/declined), created_at

announcements
├── id, title, body, priority (normal/urgent)
├── is_active, published_at

=== SIMULATION LAYER ===

staff
├── id, user_id (→ users)
├── role (club_manager/steward/caretaker)
├── portrait_happy, portrait_disappointed
├── base_salary, current_salary
├── happiness (0-100), performance_score, honesty_score (0-100)
├── hire_date, is_active, warnings_count

personalities
├── id, name (unique), description

staff_personalities
├── id, staff_id (→ staff), personality_id (→ personalities)
├── UNIQUE(staff_id, personality_id)

staff_relationships
├── id, staff_a_id (→ staff), staff_b_id (→ staff)
├── level (hostile/neutral/friendly/trusted), score (-100 to 100)
├── UNIQUE(staff_a_id, staff_b_id)

staff_events
├── id, staff_id (→ staff)
├── event_type (accident/penalty/bonus/quit/hire)
├── severity, description, date, happiness_change

shifts
├── id, staff_id (→ staff), date, time_slot
├── lane_id (nullable, → lanes)
├── status (scheduled/in_progress/completed/cancelled)

accidents
├── id, staff_id (→ staff), shift_id (→ shifts)
├── type (lane_maintenance/pin_setter/delay/discount_error/schedule_conflict/cleaning_issue)
├── severity (minor/moderate/major)
├── description, resolved, resolution
├── affected_booking_id (nullable, → lane_bookings)

penalties
├── id, staff_id (→ staff)
├── type (pay_dock/extra_hours/written_warning)
├── reason, amount_or_hours, date, issued_by (nullable, → staff)

bonuses
├── id, staff_id (→ staff)
├── type (cash/time_off/recognition)
├── reason, amount_or_hours, date, issued_by (nullable, → staff)

confrontations
├── id, reporter_staff_id, accused_staff_id (→ staff)
├── incident_type, incident_description
├── db_verified, staff_response (confessed/bs/innocent)
├── investigation_result, manager_verdict (upheld/dismissed/penalized)
├── date, happiness_impacts (JSON)

inventories
├── id, name
├── category (cleaning/bowling/bar/maintenance)
├── quantity, max_quantity, condition (good/worn/broken)
├── reorder_threshold, cost_per_unit, last_restocked_at

inventory_events
├── id, inventory_id (→ inventories), staff_id (nullable, → staff)
├── event_type (damaged/lost/restocked/used)
├── quantity_change, description, date

club_configs
├── id, bad_day_mode
├── current_day, reputation (0-100)
├── total_revenue, total_expenses

=== CLIENT/VISITOR LAYER ===

visitors
├── id, user_id (nullable, → users)
├── name, email, phone, tier (regular/premium)
├── reputation_score (0-100)
├── is_banned, ban_reason
├── banned_by_admin_id (nullable, → users), banned_at

lane_bookings
├── id, visitor_id (→ visitors), lane_id (→ lanes)
├── date, time_slot, status (pending/confirmed/completed/cancelled)
├── queue_position (nullable)
├── compensation_claimed, compensation_type

booking_queues
├── id, booking_id (→ lane_bookings), visitor_id (→ visitors)
├── lane_id (→ lanes), date, time_slot, position
├── status (waiting/notified/expired)

ban_requests
├── id, visitor_id (→ visitors), requested_by_staff_id (→ staff)
├── reason, evidence, status (pending/approved/denied)
├── reviewed_by_admin_id (nullable, → users), reviewed_at, admin_notes

complaints
├── id, visitor_id (nullable, → visitors)
├── staff_id (nullable, → staff), raised_by_staff_id (nullable, → staff)
├── type (delay/poor_service/overcharge/ban_request/client_complaint)
├── description, status (open/investigating/resolved/dismissed)
├── resolution, compensation_type
├── resolved_by (nullable, → users), resolved_at

visitor_reviews
├── id, visitor_id (→ visitors), booking_id (→ lane_bookings)
├── rating (1-5), body
├── helpful_count, not_helpful_count

staff_reviews
├── id, staff_id (→ staff), visitor_id (→ visitors), booking_id (→ lane_bookings)
├── rating (1-5), body
├── was_polite, caused_issues

review_votes
├── id, review_id, review_type (visitor/staff)
├── voter_id, vote (helpful/not_helpful)

=== VIRTUAL BOWLING ===

bowling_scores
├── id, visitor_id (nullable, → visitors)
├── score, frames_data (JSON), played_at
├── is_high_score
```

### Feature → table coverage (public website)

1. Facility Map → `facility_zones`, `lanes`, `clubs`
2. Fixtures & Results → `leagues`, `teams`, `fixtures`
3. Lane Availability → `lanes`, `lane_bookings`
4. Touring Portal → `touring_requests` (amenities directory = static seed content)
5. Event Hub → `events`, `rsvps` (Mailable notification reads `rsvps`)
6. Bar Status → `clubs` (bar_open_hours / bar_close_hours)
7. Season Stats → `teams`, `team_members`, `fixtures`, `bowling_scores` (spotlight = avg score)
8. Announcement Ticker → `announcements` (admin CRUD)
9. RSVP Capacity → `events` (max_capacity / current_rsvps) + `rsvps`
10. Micro-interactions → no DB (frontend only)

---

## Key Decisions Still Pending

- [ ] Project name (needs rebrand — no longer a simple website redesign)
- [ ] Final color palette (see options below)
- [ ] Full design system — dark/light balance, how far to push neon glow
- [ ] Can visitors create accounts, or is it all session-based like Mayhem Mobility?

## Decisions Made

- [x] Role titles: Club Manager, Steward, Caretaker (3 tiers only)
- [x] Staff counts: 2 Stewards + TBD caretakers (18 tentative)
- [x] Caretaker groups are flexible — admin assigns any small group any task, and they can statistically mess them all up
- [x] A real person can have any role (Club Manager, Steward, or Caretaker)
- [x] AI fills in for player when not controlling that role
- [x] Player work = always successful unless you miss your real-time job
- [x] Ban system: only admin (Club Manager) can ban, stewards can only request
- [x] No ban appeals
- [x] Free LLM: Groq (Llama 3 / Mixtral)
- [x] LLM = flavor text only, grounded in real DB state
- [x] Confrontation mechanic for verifying staff reports
- [x] Inventory management system — full bowls club inventory (equipment, bar, cleaning, premium)
- [x] Dual-perspective mode (Manager + Worker)
- [x] Speech bubble tooltips for all dialogue/reports (video game feel)
- [x] Bubble types: speech, thought, exclamation, question
- [x] Caretaker social system — relationship meters, snitch mechanic, reapply for humans
- [x] Groq API only for: customer-steward chat, caretaker-caretaker social, confrontations
- [x] Everything else = rules + probability, zero API calls
- [x] Virtual bowling = top-down view (arcade-y)
- [x] Scoreboard = low priority, may not be included
- [x] Staff personality traits: honest, stoner/chill, overtly friendly, creepy, nerd, rude, cliquey, opportunistic

---

## Design Philosophy

**Middle ground between maximalist and minimalist:**

- Not a sterile template, not a gaudy theme park
- Color, typography, and a few deliberate design moments carry the personality
- Clean structure with character baked into choices, not plastered on top
- Easter eggs and micro-interactions reward attention without overwhelming
- "Enough personality to be memorable, clean enough to be taken seriously"

---

## Previous Work Reference (Design Style)

- **Assignment 1:** Pastel gothic scrapbook — binder rings, post-it note sidebars, bookmark ribbon, tape effects, pressed flowers, page flip animations, dark mode, custom fonts
- **Assignment 2:** Fortune, Stopwatch, Todo apps (simpler JS)
- **Assignment 3:** Mayhem Mobility — retro 60s/70s pop art comic book, Ben-Day dots, speech bursts, onomatopoeia, custom date picker, spotlight form validation, modal system. PHP/MySQL backend. Had both sim mode and real-time mode.

This project should match or exceed the creative ambition and technical quality of Assignment 3.

---

## Project Status

### Completed Decisions
- Sport: Ten-pin bowling (confirmed)
- Name: The 10th Frame (frontend) / Cloud Nine Bowling (backend)
- Palette: Cloud Nine (baby blue + warm gold + coral)
- Fonts: Bowlby One SC, Bungee, Righteous, Manrope, Share Tech Mono
- Design system: design-system.css saved in project root
- All lawn bowls references updated to ten-pin bowling

### Ready For
- Laravel project scaffolding
- Database migrations
- Blade template creation
- Core feature implementation
