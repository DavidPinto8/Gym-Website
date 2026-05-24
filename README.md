# Flow Fitness (ltw14g08)

## How to View the HTML Prototype

For this phase of the project, the frontend is built with static HTML and CSS. You can view the website using either of the following methods:

**Method 1: Direct File Open**
1. Unzip the project folder.
2. Navigate to the root directory.
3. Double-click the `index.html` file to open it in your default web browser.
4. From the Home page, you can use the navigation bar to access the other pages (`schedule.html`, `profile.html`, etc.).

**Method 2: Using a Local PHP Server**
- If your environment requires serving the files, open your terminal in the root folder of the project and run:
```bash
php -S localhost:9000
```
Then, open your web browser and navigate to `http://localhost:9000`.

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

**Extra:**
- [x] Membership plans (Starter, Pro, Elite pricing tiers and features integration).

## Running (Backend Phase - WIP)

Once the backend and database are fully implemented in the next phases, the setup will be:

    sqlite3 database/database.db < database/database.sql
    php -S localhost:9000

## Credentials

- To be defined in the backend phase...

## Work done by

- David Pinto - up202404233@up.pt
- João Lage - up202406458@up.pt