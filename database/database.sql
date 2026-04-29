PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS trainers;
DROP TABLE IF EXISTS members;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS plans;

CREATE TABLE plans (
    id    INTEGER PRIMARY KEY AUTOINCREMENT,
    name  TEXT NOT NULL UNIQUE,
    price REAL NOT NULL
);

CREATE TABLE users (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    username     TEXT    NOT NULL UNIQUE,
    email        TEXT    NOT NULL UNIQUE,
    password     TEXT    NOT NULL,
    role         TEXT    NOT NULL DEFAULT 'member' CHECK (role IN ('member', 'trainer', 'admin')),
    photo        TEXT,
    created_at   TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE members (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id           INTEGER NOT NULL UNIQUE,
    plan_id           INTEGER,
    phone             TEXT,
    streak            INTEGER DEFAULT 0,
    favorite_flow     TEXT,
    primary_goal      TEXT,
    next_billing_date TEXT,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans (id) ON DELETE SET NULL
);

CREATE TABLE trainers (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL UNIQUE,
    bio             TEXT,
    specializations TEXT,
    certifications  TEXT,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

CREATE TABLE classes (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    type         TEXT    NOT NULL CHECK (type IN ('yoga', 'hiit', 'strength', 'cardio', 'other')),
    trainer_id   INTEGER,
    capacity     INTEGER NOT NULL,
    start_time   TEXT    NOT NULL,
    duration_min INTEGER NOT NULL DEFAULT 60,
    location     TEXT    NOT NULL,
    FOREIGN KEY (trainer_id) REFERENCES trainers (id) ON DELETE SET NULL
);

CREATE TABLE enrollments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL,
    class_id    INTEGER NOT NULL,
    enrolled_at TEXT    NOT NULL DEFAULT (datetime('now')),
    status      TEXT    NOT NULL DEFAULT 'enrolled' CHECK (status IN ('enrolled', 'cancelled', 'waitlist')),
    UNIQUE (member_id, class_id),               
    FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)  REFERENCES classes (id) ON DELETE CASCADE
);

CREATE TABLE equipment (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,
    type            TEXT    NOT NULL,
    location        TEXT    NOT NULL,
    total_units     INTEGER NOT NULL,
    available_units INTEGER NOT NULL,
    CHECK (available_units >= 0),
    CHECK (available_units <= total_units)
);

CREATE TABLE reviews (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id   INTEGER NOT NULL,
    class_id    INTEGER NOT NULL,
    rating      INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    UNIQUE (member_id, class_id),               
    FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE,
    FOREIGN KEY (class_id)  REFERENCES classes (id) ON DELETE CASCADE
);

-- DADOS INICIAIS (SEED DATA) - Passwords são a palavra "password" hasheada
INSERT INTO plans (name, price) VALUES ('Starter', 30.0), ('Pro', 45.0), ('Elite', 70.0);

INSERT INTO users (name, username, email, password, role, photo) 
VALUES ('Jalen Brunson', 'jbrunson_11', 'jalen@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'assets/avatar-jalen.jpg');

INSERT INTO members (user_id, plan_id, phone, streak, favorite_flow, primary_goal, next_billing_date) 
VALUES (1, 2, '+351912345678', 45, 'strength', 'muscle', '2026-05-24');

INSERT INTO users (name, username, email, password, role) VALUES 
('Anna Costa', 'anna_c', 'anna@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('Mike Ferreira', 'mike_f', 'mike@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer'),
('John Silva', 'john_s', 'john@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer');

INSERT INTO trainers (user_id, bio, specializations, certifications) VALUES 
(2, 'Yoga master.', 'Yoga,Mobility', 'RYT 200'),
(3, 'Powerlifting specialist.', 'Strength,Power', 'CrossFit L2'),
(4, 'High intensity coach.', 'HIIT,Cardio', 'NASM CPT');

INSERT INTO classes (name, type, trainer_id, capacity, start_time, location) VALUES 
('Morning Flow', 'yoga', 1, 20, '2026-04-27T08:00:00', 'Studio 1'),
('Power Build', 'strength', 2, 15, '2026-04-27T13:00:00', 'Main Floor'),
('Fat Burn', 'hiit', 3, 25, '2026-04-27T19:00:00', 'Studio 2'),
('Cycle Sprint', 'cardio', 1, 20, '2026-04-28T09:00:00', 'Cycle Room'),
('Vinyasa Zen', 'yoga', 1, 18, '2026-04-28T12:30:00', 'Studio 1'),
('Hypertrophy', 'strength', 2, 12, '2026-04-28T18:30:00', 'Weight Area'),
('Tabata Core', 'hiit', 3, 20, '2026-04-29T07:30:00', 'Studio 2'),
('Endurance Run', 'cardio', 1, 15, '2026-04-29T11:00:00', 'Cardio Zone'),
('Deadlift Clinic', 'strength', 2, 10, '2026-04-29T20:00:00', 'Main Floor'),
('Ashtanga Intro', 'yoga', 1, 15, '2026-04-30T08:30:00', 'Studio 1'),
('Full Body Blast', 'hiit', 3, 22, '2026-04-30T12:00:00', 'Main Floor'),
('Step Rhythm', 'cardio', 3, 20, '2026-04-30T17:30:00', 'Studio 2'),
('Olympic Lifting', 'strength', 2, 8, '2026-05-01T10:00:00', 'Main Floor'),
('Metcon Friday', 'hiit', 3, 25, '2026-05-01T16:00:00', 'Main Floor'),
('Restorative Yin', 'yoga', 1, 20, '2026-05-01T19:30:00', 'Studio 1');

INSERT INTO equipment (name, type, location, total_units, available_units) VALUES 
('Treadmill Pro', 'cardio', 'Cardio Zone', 10, 8),
('Squat Rack', 'weights', 'Main Floor', 5, 2);