PRAGMA foreign_keys = ON;

DROP TABLE IF EXISTS reports;
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
    attended    INTEGER NOT NULL DEFAULT 0 CHECK (attended IN (0, 1)),
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

CREATE TABLE reports (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    member_id      INTEGER NOT NULL,
    category       TEXT    NOT NULL CHECK (category IN ('equipment', 'class', 'trainer', 'other')),
    subject        TEXT    NOT NULL,
    description    TEXT    NOT NULL,
    status         TEXT    NOT NULL DEFAULT 'open' CHECK (status IN ('open', 'in_progress', 'resolved')),
    admin_response TEXT,
    member_response TEXT,
    closed_by_member INTEGER NOT NULL DEFAULT 0 CHECK (closed_by_member IN (0, 1)),
    created_at     TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at     TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE
);

-- ==========================================
-- SEED DATA 
-- All passwords are "password"
-- ==========================================

INSERT INTO plans (name, price) VALUES ('Starter', 30.0), ('Pro', 45.0), ('Elite', 70.0);

-- Admin
INSERT INTO users (name, username, email, password, role) 
VALUES ('System Admin', 'admin', 'admin@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- VIP Member (Jalen Brunson)
INSERT INTO users (name, username, email, password, role, photo) 
VALUES ('Jalen Brunson', 'jbrunson_11', 'jalen@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'assets/avatar-jalen.jpg');

INSERT INTO members (user_id, plan_id, phone, streak, favorite_flow, primary_goal, next_billing_date) 
VALUES (2, 2, '+351912345678', 45, 'strength', 'muscle', '2026-05-24');

INSERT INTO users (name, username, email, password, role, photo) VALUES 
('Sarah Jenkins', 'sarah_j', 'sarah.j@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'sarah_jenkins.png'),
('Marcus Thorne', 'm_thorne', 'marcus.t@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'marcus_thorne.png'),
('Chloe Whitaker', 'chloe_fit', 'chloe.w@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'chloe_whitaker.png'),
('David Miller', 'dave_coach', 'david.m@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'david_miller.png'),
('Rachel Adams', 'rachel_a', 'rachel.a@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'rachel_adams.png'),
('James Sterling', 'j_sterling', 'james.s@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'james_sterling.png'),
('Emma Collins', 'emma_c', 'emma.c@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'emma_collins.png'),
('Leon Brooks', 'leon_b', 'leon.b@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', 'leon_brooks.png');

INSERT INTO trainers (user_id, bio, specializations, certifications) VALUES 
(3, 'Master of mindfulness and flexibility.', 'Yoga,Mobility,Meditation', 'E-RYT 500, Prenatal Yoga'),
(4, 'Former pro athlete focused on raw power.', 'Strength,Powerlifting', 'CSCS, USAW Level 2'),
(5, 'High energy coach for maximum results.', 'HIIT,Conditioning', 'NASM-CPT, Precision Nutrition'),
(6, 'Spinning specialist and marathon runner.', 'Cardio,Spinning,Endurance', 'Schwinn Certified, Ironman Coach'),
(7, 'Expert in core stability and alignment.', 'Pilates,Core,Rehab', 'Stott Pilates, NASM-CES'),
(8, 'Olympic lifting and structural balance.', 'Strength,Olympic Lifting', 'CrossFit Level 3, British Weightlifting'),
(9, 'Recovery and functional movement specialist.', 'Mobility,Functional', 'FMS Level 2, Stick Mobility'),
(10, 'Boxing and high-intensity combat sports.', 'Boxing,HIIT,Agility', 'USA Boxing Coach, WKA Pro');

INSERT INTO classes (name, type, trainer_id, capacity, start_time, location) VALUES 
('Sunrise Vinyasa', 'yoga', 1, 20, '2026-04-27T08:00:00', 'Studio 1'),
('Big Lift Monday', 'strength', 2, 12, '2026-04-27T13:00:00', 'Main Floor'),
('Afterburn HIIT', 'hiit', 3, 25, '2026-04-27T19:00:00', 'Studio 2'),
('Velocity Sprint', 'cardio', 4, 20, '2026-04-28T09:00:00', 'Cycle Room'),
('Core Control', 'other', 5, 18, '2026-04-28T12:30:00', 'Studio 1'),
('Heavy Volume', 'strength', 6, 12, '2026-04-28T18:30:00', 'Weight Area'),
('Metabolic Reset', 'hiit', 8, 20, '2026-04-29T07:30:00', 'Studio 2'),
('Road Warrior', 'cardio', 4, 15, '2026-04-29T11:00:00', 'Cardio Zone'),
('Total Strength', 'strength', 2, 10, '2026-04-29T20:00:00', 'Main Floor'),
('Flow & Breath', 'yoga', 1, 15, '2026-04-30T08:30:00', 'Studio 1'),
('Box & Burn', 'hiit', 8, 22, '2026-04-30T12:00:00', 'Main Floor'),
('Functional Flow', 'other', 7, 20, '2026-04-30T17:30:00', 'Studio 2'),
('Snatch Clinic', 'strength', 6, 8, '2026-05-01T10:00:00', 'Main Floor'),
('Friday Finisher', 'hiit', 3, 25, '2026-05-01T16:00:00', 'Main Floor'),
('Deep Stretch Yin', 'yoga', 7, 20, '2026-05-01T19:30:00', 'Studio 1');

INSERT INTO equipment (name, type, location, total_units, available_units) VALUES 
('Treadmill Pro', 'cardio', 'Cardio Zone', 15, 4),          
('Elliptical Cross', 'cardio', 'Cardio Zone', 10, 8),       
('Concept2 Rower', 'cardio', 'Cardio Zone', 6, 1),          
('StairMaster', 'cardio', 'Cardio Zone', 4, 0),             
('Squat Rack', 'weights', 'Main Floor', 6, 2),              
('Olympic Flat Bench', 'weights', 'Main Floor', 5, 1),      
('Dumbbell Rack (5-40kg)', 'weights', 'Free Weights', 10, 8),
('Kettlebell Station', 'weights', 'Functional Zone', 15, 12),
('Leg Press 45°', 'machines', 'Machine Zone', 3, 1),        
('Cable Crossover', 'machines', 'Machine Zone', 4, 2),      
('Smith Machine', 'machines', 'Machine Zone', 2, 0),        
('Pull-up & Dip Station', 'functional', 'Functional Zone', 4, 3);