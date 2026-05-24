# Flow Fitness (ltw14g08)

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

## Features

**All users:**
- [x] Register a new account.
- [x] Log in and out.
- [x] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [x] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [x] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [x] View trainer profiles, including their specializations and the classes they teach.
- [x] Check the current availability of equipment in the main training area.
- [x] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [x] Manage their public profile, including bio, specializations, and certifications.
- [x] View the roster of members enrolled in their classes.
- [x] Track and manage their assigned class schedule.

**Admins:**
- [x] Manage members and trainers (create, update, and deactivate accounts).
- [x] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [x] Manage equipment in the main training area (add, update availability status, and remove items).
- [x] Elevate a user to admin status.
- [x] Oversee and ensure the smooth operation of the entire system.

---

## Additional Features Implemented

### Booking & Class Experience
- [x] Automatic waitlist system when classes are full.
- [x] Automatic promotion from waitlist to enrolled status after cancellations.
- [x] Detailed class pages with schedules, trainer information and ratings.
- [x] Attendance tracking system managed by trainers.
- [x] Class participation validation before allowing reviews.
- [x] Average class ratings display.

### Trainer Features
- [x] Trainer dashboard.
- [x] Attendance management per class.
- [x] Trainer schedule overview.

### Reports & Feedback
- [x] Equipment issue reporting.
- [x] Class issue reporting.
- [x] Trainer issue reporting.
- [x] Report status tracking.
- [x] Admin responses to reports.

### Equipment
- [x] Live equipment availability updates using AJAX.

### Security
- [x] Password hashing (`password_hash()`).
- [x] CSRF protection.
- [x] SQL Injection prevention using PDO prepared statements.
- [x] XSS mitigation using `htmlspecialchars()`.
- [x] Session authentication.
- [x] Role-based authorization.

### UX Improvements
- [x] AJAX schedule filtering.
- [x] AJAX equipment updates.
- [x] Responsive layouts.
- [x] Profile photo upload support.

---

## Membership Plans

Available plans:

- Starter
- Pro
- Elite

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
username: sarah_j
password: password
``` 

All seeded accounts use:

```
password
```

---

## Team

Group ltw14g08

- David Pinto — up202404233@up.pt
- João Lage — up202406458@up.pt

---

## Notes

Members can only review classes they have actually attended.

The system includes AJAX interactions, waitlist handling, trainer attendance tracking, issue reporting workflows and secure authentication mechanisms.