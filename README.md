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

- [x] **Registration & Sign-up**: Register new member accounts with plan selection and payment setup.
- [x] **Session Authentication**: Secure login/logout workflows with password verification.
- [x] **Secure Password Hashing**: Passwords stored securely using bcrypt hashing algorithms.
- [x] **Profile Management**: Update member details (Full Name, Username, etc.).
- [x] **Workout Preferences**: Set and update favorite training style (HIIT, Strength, Mobility) and primary fitness goal.
- [x] **Workout Streak Tracking**: Dynamically tracks and displays active member streaks (visualized with a 🔥 badge).
- [x] **Secure Avatar Upload API**: 
  - Restricts uploads to POST requests, validating active sessions and CSRF tokens.
  - Limits file size to 2MB and enforces JPEG/PNG file types via server-side `finfo` MIME inspection.
  - Validates image integrity with `getimagesize`.
  - Cryptographically renames upload files using `random_bytes` to prevent traversal and directory attacks.
- [x] **Gym Access Pass**: Dynamic, user-specific Gym Entry QR codes generated via external API for facility check-in.

---

### Membership & Billing

- [x] **Membership Plans**: Tiered plans including **Starter** (€30), **Pro** (€45), and **Elite** (€70).
- [x] **Upgrade/Downgrade Flow**: Upgrade or downgrade plans directly from the profile dashboard.
- [x] **Payment Validation Requirement**: Enforces that members have a valid payment method on file before upgrading plans, prompting redirection if missing.
- [x] **Mock Billing System**: Simulated billing system tracking active subscriptions and calculating billing cycles.
- [x] **Payment Method Management**: Update payment information dynamically, validating provider type (Visa, Mastercard, PayPal, Apple Pay, Google Pay) and card credentials (regex-enforced 4-digit card reference).
- [x] **Next Billing Date Display**: Automatically calculates and displays the next renewal date (+1 month cycle).

---

### Classes & Booking

- [x] **Weekly Recurring Schedule**: Class schedule operates on a recurring weekly model, dynamically calculating next class times and displaying them chronologically.
- [x] **Interactive Schedule Filtering**: Filter classes in real-time by category (Yoga, HIIT, Strength, Cardio, Other), trainer, and day of the week.
- [x] **Real-time Capacity Tracking**: Interactive display of class capacities showing active bookings (e.g., `12/20` slots filled).
- [x] **Detailed Class Pages**: Inspect class type, trainer bio/specializations, location, duration, and reviews.
- [x] **Automated Booking & Waitlists**:
  - Book or cancel class enrollments.
  - Automatic waitlist placement when class capacities are full, with live queue position tracking.
  - **Waitlist Auto-Promotion**: Cancelling a booking instantly promotes the next member in the waitlist queue to `enrolled` status.
  - Leave waitlist queues at any time.
- [x] **Review and Rating System**:
  - Members can leave a rating (1 to 5 stars) and comments.
  - Enforced attendance: Members can *only* review classes they have physically attended (verified via the trainer roster).
  - **Interactive Review Editing**: Members can update or edit their previously submitted reviews.
  - Live average ratings and review counts calculated on the fly.

---

### Trainer Features

- [x] **Public Trainer Profiles**: Showcases trainer bios, specializations, credentials, and photos.
- [x] **Trainer Dashboard**:
  - **Self-Editing Profiles**: Trainers can update their bio, certifications, and specializations from their dashboard.
  - **Class Rosters**: View upcoming classes and the roster of enrolled students (with user details and booking status).
  - **Attendance Tracking**: Mark member attendance (attended vs absent) for class sessions and save status using database transactions.

---

### Equipment Management

- [x] **Real-time Equipment Availability**: Search and view equipment list status in real-time.
- [x] **Live AJAX Searching & Filtering**: Search equipment by name and filter by category (cardio, weights, machines, functional) without reloading the page.
- [x] **Visual Status Indicators**: Progress bars illustrating available units vs total units, color-coded by urgency (Green: >60% available, Orange: 26%-60% available, Red: <=25% critical capacity).
- [x] **Issue Reporting**: Directly report equipment defects or issues, pre-filling the equipment name and category in the support ticket.
- [x] **Admin CRUD Management**: Full administrative control to add, edit total/available units, and delete equipment.

---

### Reporting System

- [x] **Member Support Tickets**: Submit issue reports categorized under equipment, class, trainer, or other, with a custom subject and description.
- [x] **Interactive Conversation Threads**: 
  - Admins can post updates, change status (Open, In Progress, Resolved), and leave textual feedback.
  - Members can post follow-up replies (which moves status back to "In Progress") and close solved tickets directly from their panel.
- [x] **Internal Notifications**:
  - Notifies admins when members submit reports, post replies, or close tickets.
  - Notifies members when admins reply, update ticket status, or mark issues as resolved.
  - **Unread Notification Badge**: Header navbar displays an unread notification count badge in real-time.
  - **Notification Center**: Dashboard displaying notifications chronologically with quick links to related reports and a "Mark all as read" button.

---

### Admin Features

- [x] **System Oversight Dashboard**: High-level statistics cards displaying Total Members, Total Trainers, Total Classes, and Critical Equipment counts.
- [x] **Live Analytics Panels**:
  - **Gym Occupancy Status**: Displays average class slot utilization rate across the gym via dynamic progress indicators.
  - **Popular Disciplines**: Ordered ranking of gym training methodologies based on total active bookings.
  - **Community Spotlight**: Highlights the community member holding the highest active workout streak.
- [x] **User Management**: Unified table listing users, roles, and membership plans. Admin capability to promote users to admin and delete accounts safely.
- [x] **Account Deletion Constraints**: Prevents deleting the logged-in admin or deleting the last admin to preserve system accessibility.
- [x] **Class Catalog CRUD**: Create and schedule new class slots (assigning trainer, capacity, time, duration, and location) or delete them safely (cleaning up associated bookings and reviews).
- [x] **Equipment CRUD**: Complete workflow to add new gym equipment assets, update unit availability, or delete items.
- [x] **Report Moderation**: Read, track, and resolve member reports via inline responses.

---

### Security & Robustness

- [x] **CSRF Protection**: All state-modifying requests (form submissions, API actions, and logout) are validated against secure session tokens to block Cross-Site Request Forgery.
- [x] **SQL Injection Prevention**: All database queries are fully parameterized and executed using PDO prepared statements.
- [x] **Stored XSS Mitigation**: Strictly escapes all user-generated content using `htmlspecialchars` before outputting as HTML to prevent Cross-Site Scripting.
- [x] **Session Authentication & Role Authorization**: Hardened role verification checks (Admin, Trainer, Member) on every restricted PHP page and backend endpoint.
- [x] **Rejection of Unsafe HTTP Methods**: Prevents sensitive actions (like logging out) through GET requests, requiring POST submissions.
- [x] **Automated Security Tests**: Includes a backend sanity test script covering 20 automated security checks, from auth boundaries to XSS and CSRF validation.

---

### UX & Interface Enhancements

- [x] **Responsive Layouts**: Fully responsive pages built with CSS Flexbox/Grid, optimized for desktop and mobile devices.
- [x] **Aesthetic Styling System**: Curated typography (Inter and Oswald Google Fonts), smooth transitions, glassmorphic accents, and dark/gold premium theme styling.
- [x] **Confirmation Dialogues**: JavaScript confirmation dialogs before critical deletion actions (users, classes, equipment).

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