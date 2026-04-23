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
- [ ] Register a new account.
- [ ] Log in and out.
- [ ] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [ ] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [ ] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [ ] View trainer profiles, including their specializations and the classes they teach.
- [ ] Check the current availability of equipment in the main training area.
- [ ] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [ ] Manage their public profile, including bio, specializations, and certifications.
- [ ] View the roster of members enrolled in their classes.
- [ ] Track and manage their assigned class schedule.

**Admins:**
- [ ] Manage members and trainers (create, update, and deactivate accounts).
- [ ] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [ ] Manage equipment in the main training area (add, update availability status, and remove items).
- [ ] Elevate a user to admin status.
- [ ] Oversee and ensure the smooth operation of the entire system.

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