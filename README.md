# Flow Fitness (ltw14g08)

Premium gym management platform built with PHP, SQLite and AJAX, focused on class scheduling, membership management, trainer workflows and equipment tracking.

---

## How to Run

### Setup Database

```bash
sqlite3 database/database.db < database/database.sql
```

### Start Server

```bash
php -S localhost:9000
```

Open:

```
http://localhost:9000
```

---

## Automated Tests

Run backend sanity tests:

```bash
php tests/run_tests.php
```

---

## Features

### Authentication & Accounts

**All users:**

- [x] Register account
- [x] Login / Logout
- [x] Password hashing
- [x] Profile management
- [x] Username updates
- [x] Password updates
- [x] Profile photo upload
- [x] QR-code gym access pass
- [x] Membership billing information
- [x] Membership plan upgrades

---

### Membership & Billing

- [x] Starter / Pro / Elite plans
- [x] Mock billing system
- [x] Payment method management
- [x] Membership renewal tracking
- [x] Next billing date display

---

### Classes & Booking

**Members can:**

- [x] Browse available classes
- [x] Filter by:
  - Type
  - Trainer
  - Day
- [x] View detailed class pages
- [x] View trainer photos
- [x] Book classes
- [x] Cancel bookings
- [x] Join waitlists automatically
- [x] Leave waitlists
- [x] Track waitlist position
- [x] Leave reviews after attendance
- [x] View ratings and review counts
- [x] Weekly recurring schedule system

---

### Trainer Features

- [x] Public trainer profiles
- [x] Trainer dashboard
- [x] Attendance tracking
- [x] Schedule overview
- [x] Enrolled member roster
- [x] Bio management
- [x] Certifications management
- [x] Specializations management

---

### Equipment Management

- [x] Real-time equipment availability
- [x] AJAX equipment updates
- [x] Search equipment
- [x] Filter by equipment category
- [x] Equipment issue reporting
- [x] Equipment CRUD management (Admin)

---

### Reporting System

Members can report:

- [x] Equipment issues
- [x] Class issues
- [x] Trainer issues
- [x] Other issues

Admin tools:

- [x] Report response system
- [x] Status tracking
- [x] Open / In Progress / Resolved workflow
- [x] Member follow-up responses
- [x] Member-side ticket closing
- [x] Notification system

---

### Admin Features

- [x] User management
- [x] Trainer management
- [x] Promote users to admin
- [x] Delete accounts safely
- [x] Prevent deletion of last admin
- [x] Class catalog CRUD
- [x] Equipment CRUD
- [x] Report moderation
- [x] System oversight dashboard

---

### Security

- [x] CSRF protection
- [x] PDO prepared statements
- [x] SQL Injection prevention
- [x] XSS mitigation
- [x] Session authentication
- [x] Role authorization
- [x] Input validation
- [x] Backend API sanity tests

---

### UX Enhancements

- [x] Responsive layouts
- [x] AJAX filtering
- [x] AJAX equipment updates
- [x] Trainer avatars
- [x] Dashboard redesign
- [x] Billing experience
- [x] Membership visualization improvements
- [x] Weekly recurring class generation

---

## Membership Plans

| Plan | Monthly Price |
|-------|---------------|
| Starter | €30 |
| Pro | €45 |
| Elite | €70 |

---

## Running Credentials

### Admin

```
username: admin
password: password
```

### Demo Member

```
username: jbrunson_11
password: password
```

### Demo Trainer

```
username: chloe_fit
password: password
```

All seeded accounts use:

```
password
```

---

## Notes

- Classes operate using a recurring weekly scheduling model.
- Members can only review classes they have attended.
- Billing functionality is simulated for academic demonstration purposes.
- Equipment availability updates dynamically using AJAX.
- Trainer and admin dashboards provide role-specific workflows.

---

## Team

Group ltw14g08

- João Lage — up202406458
- David Pinto — up202404233