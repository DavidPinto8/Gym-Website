# Flow Fitness (ltw14g08)

Gym management platform built with **PHP**, **SQLite**, **PDO**, **JavaScript**, **AJAX/JSON**, **HTML** and **CSS**.

Flow Fitness is a premium gym management platform designed to simplify the experience of members, trainers and administrators.

The platform combines class scheduling, membership management, equipment tracking and customer support in a single system.

---

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.
- [x] Receive in-app notifications for bookings, reports and platform activity.
- [x] View a personalized gym access QR pass.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.
- [x] Edit previously submitted reviews.
- [x] Join and leave class waitlists when a class is full.
- [x] Manage their membership plan and payment method.
- [x] Submit support reports and reply to admin responses.
- [x] Set workout preferences (training style and fitness goal) and track workout streaks.

**Trainers:**
- [x] Manage their public profile, including bio, specializations, and certifications.
- [x] View the roster of members enrolled in their classes.
- [x] Track and manage their assigned class schedule.
- [x] Mark member attendance per session (required to unlock class reviews).

**Admins:**
- [x] Manage members and trainers (create, update, and deactivate accounts).
- [x] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [x] Elevate a user to admin status.
- [x] Oversee and ensure the smooth operation of the entire system.
- [x] View platform analytics and moderate support reports.

**Extra:**
- [x] Membership plans and simulated billing.
- [x] Class waitlists with automatic promotion.
- [x] Attendance-locked reviews.
- [x] Internal notification system.
- [x] Support reports with two-way conversations.
- [x] Admin analytics dashboard.

---

## Running

### 1. Setup Database

```bash
sqlite3 database/database.db < database/database.sql
```

### 2. Start Server

```bash
php -S localhost:9000
```

### 3. Open Website

```text
http://localhost:9000
```

---

## Credentials

All demo accounts use the password `password`.

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `password` |
| Member | `jbrunson_11` | `password` |
| Trainer | `chloe_fit` | `password` |

Recommended for the demo:
- `admin` — full access to user management, class catalog, equipment, reports and analytics.
- `jbrunson_11` — realistic member activity: bookings, membership info and all member workflows.
- `chloe_fit` — trainer with assigned classes, editable profile and class roster.

---

## Automated Tests

```bash
php tests/run_tests.php
```

Covers authentication boundaries, CSRF protection, XSS mitigation, SQL injection prevention and core business logic.

---

## Security

- **SQL Injection** — PDO prepared statements with parameterized queries throughout.
- **XSS** — user-generated output escaped with `htmlspecialchars`.
- **CSRF** — all state-changing POST requests validate a CSRF token.
- **Passwords** — stored with secure hashing.
- **Access control** — role and session checks on all restricted pages and actions.
- **Uploads** — avatar upload validates file size, MIME type, image integrity; filenames are randomized.
- **Admin safety** — system blocks deletion of the last admin or the active session's admin.

---

## Extra Features

- **Membership plans** — Starter (€30), Pro (€45) and Elite (€70) tiers with upgrade/downgrade flow.
- **Simulated billing** — next billing date calculated and displayed; payment method management included.
- **Waitlists** — full classes allow members to queue; cancellations automatically promote the next in line.
- **Attendance-locked reviews** — members can only review a class after a trainer marks them as attended.
- **Support reports** — two-way conversation threads between members and admins.
- **Notifications** — events like bookings, report replies and waitlist promotions trigger internal notifications.
- **Admin analytics** — dashboard panels for occupancy, popular disciplines and community spotlight.
- **Dynamic avatars** — users without a photo get an initials-based generated avatar.
- **Workout streaks** — member streaks tracked and displayed on the profile.

---

## Technology

- [x] HTML — semantic page structure.
- [x] CSS — responsive layouts, mobile-first.
- [x] PHP — server-side rendering and backend logic.
- [x] JavaScript — client-side interaction.
- [x] AJAX/JSON — asynchronous schedule and equipment updates.
- [x] SQLite + PDO — database layer.
- [x] No Laravel, Bootstrap, jQuery or other restricted frameworks.

---

## Notes

- Classes use a recurring weekly scheduling model.
- Billing is simulated for academic demonstration.
- Equipment availability updates without page reload via AJAX.
- Demo data is seeded for a smooth presentation.

---

## Team

Group `ltw14g08`

- João Lage — up202406458
- David Pinto — up202404233