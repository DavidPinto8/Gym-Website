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
DROP TABLE IF EXISTS notifications;

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
    payment_method    TEXT,
    card_last4        TEXT,
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

CREATE TABLE notifications (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NOT NULL,
    message    TEXT    NOT NULL,
    link       TEXT,
    is_read    INTEGER NOT NULL DEFAULT 0 CHECK (is_read IN (0, 1)),
    created_at TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
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

INSERT INTO members (user_id,plan_id,phone,streak,favorite_flow,primary_goal,next_billing_date,payment_method,card_last4) 
VALUES (2,3,'+351912345678',45,'strength','muscle','2026-05-24','visa','4242');

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
('Deep Stretch Yin', 'yoga', 7, 20, '2026-05-01T19:30:00', 'Studio 1'),
('Weekend Warrior HIIT', 'hiit', 3, 20, '2026-05-02T10:00:00', 'Studio 2'),
('Saturday Power Lift', 'strength', 2, 12, '2026-05-02T14:30:00', 'Main Floor'),
('Sunday Zen Yoga', 'yoga', 1, 25, '2026-05-03T11:00:00', 'Studio 1');

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
('Pull-up & Dip Station', 'functional', 'Functional Zone', 4, 3),
('Assault AirBike', 'cardio', 'Cardio Zone', 8, 3),
('Spinning Bike Spinner', 'cardio', 'Cycle Room', 25, 18),
('SkiErg Concept2', 'cardio', 'Functional Zone', 3, 1),
('Olympic Incline Bench', 'weights', 'Main Floor', 4, 2),
('Preacher Curl Bench', 'weights', 'Free Weights', 2, 2),
('Deadlift Platform', 'weights', 'Main Floor', 4, 1),
('Bumper Plates Set (1000kg total)', 'weights', 'Main Floor', 1, 1),
('Leg Extension Machine', 'machines', 'Machine Zone', 3, 3),
('Lying Leg Curl', 'machines', 'Machine Zone', 2, 1),
('Lat Pulldown Machine', 'machines', 'Machine Zone', 4, 2),
('Seated Cable Row', 'machines', 'Machine Zone', 3, 1),
('Pec Deck / Rear Delt', 'machines', 'Machine Zone', 2, 0),
('Shoulder Press Machine', 'machines', 'Machine Zone', 2, 2),
('TRX Suspension Trainer', 'functional', 'Functional Zone', 12, 8),
('Medicine Ball Rack (2-10kg)', 'functional', 'Functional Zone', 10, 9),
('Plyo Box Set', 'functional', 'Functional Zone', 6, 4),
('Battle Rope Station', 'functional', 'Functional Zone', 4, 2);

-- ==========================================
-- EXTENDED SEED DATA FOR DEMO(MEMBERS & INTERACTIONS)
-- ==========================================

-- Additional members to register (all passwords are 'password')
INSERT INTO users (name, username, email, password, role) VALUES
('Elena Rostova', 'elena_r', 'elena@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Marcus Aurelius', 'stoic_lift', 'stoic@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Serena Williams', 'serena_w', 'serena@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Tony Parker', 'tony_p', 'tony@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Ethan Carter', 'ethan_c', 'ethan@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Clara Oswald', 'clara_o', 'clara@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Sophia Bennett', 'sophia_b', 'sophia@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Daniel Brooks', 'daniel_b', 'daniel@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Lucas Reed', 'lucas_r', 'lucas@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Ryan Foster', 'ryan_f', 'ryan@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Nathan Walker', 'nathan_w', 'nathan@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Liam Turner', 'liam_t', 'liam@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Connor Hayes', 'connor_h', 'connor@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member'),
('Amelia Scott', 'amelia_s', 'amelia@flowfitness.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

-- Members profile details mapping
INSERT INTO members (user_id, plan_id, phone, streak, favorite_flow, primary_goal, next_billing_date, payment_method, card_last4) VALUES 
(11, 3, '+351912345679', 52, 'cardio', 'endurance', '2026-06-15', 'mastercard', '5555'),
(12, 2, '+351912345680', 12, 'strength', 'strength', '2026-06-20', 'visa', '1234'),
(13, 3, '+351912345681', 38, 'hiit', 'conditioning', '2026-06-18', 'visa', '9876'),
(14, 1, '+351912345682', 5, 'cardio', 'cardio', '2026-06-12', 'mastercard', '4321'),
(15, 3, '+351912345683', 120, 'hiit', 'agility', '2026-06-25', 'visa', '0007'),
(16, 1, '+351912345684', 8, 'yoga', 'flexibility', '2026-06-05', 'paypal', null),
(17, 3, '+351912345685', 99, 'strength', 'muscle', '2026-06-22', 'visa', '1111'),
(18, 3, '+351912345686', 15, 'strength', 'muscle', '2026-06-28', 'visa', '1938'),
(19, 1, '+351912345687', 21, 'yoga', 'flexibility', '2026-06-01', 'visa', '2002'),
(20, 3, '+351912345688', 88, 'cardio', 'speed', '2026-06-19', 'visa', '1940'),
(21, 2, '+351912345689', 30, 'strength', 'power', '2026-06-14', 'mastercard', '5959'),
(22, 3, '+351912345690', 42, 'cardio', 'endurance', '2026-06-21', 'visa', '0003'),
(23, 3, '+351912345691', 75, 'hiit', 'precision', '2026-06-11', 'visa', '1010'),
(24, 3, '+351912345692', 50, 'yoga', 'flexibility', '2026-06-24', 'mastercard', '2021');

-- Enrollments across classes (capacity & waitlist setup)
-- Class 1 (Sunrise Vinyasa, Yoga): 6 enrolled, 5 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 1, 'enrolled', 1),
(2, 1, 'enrolled', 1),
(7, 1, 'enrolled', 1),
(8, 1, 'enrolled', 1),
(10, 1, 'enrolled', 1),
(15, 1, 'enrolled', 0);

-- Class 2 (Big Lift Monday, Strength, capacity 12): 12 enrolled, 3 waitlisted
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 2, 'enrolled', 1),
(2, 2, 'enrolled', 1),
(3, 2, 'enrolled', 1),
(4, 2, 'enrolled', 1),
(6, 2, 'enrolled', 1),
(8, 2, 'enrolled', 1),
(9, 2, 'enrolled', 1),
(11, 2, 'enrolled', 0),
(12, 2, 'enrolled', 0),
(13, 2, 'enrolled', 0),
(14, 2, 'enrolled', 0),
(15, 2, 'enrolled', 0),
(5, 2, 'waitlist', 0),
(7, 2, 'waitlist', 0),
(10, 2, 'waitlist', 0);

-- Class 3 (Afterburn HIIT, HIIT): 9 enrolled, 5 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 3, 'enrolled', 1),
(2, 3, 'enrolled', 1),
(4, 3, 'enrolled', 1),
(5, 3, 'enrolled', 1),
(6, 3, 'enrolled', 1),
(11, 3, 'enrolled', 0),
(13, 3, 'enrolled', 0),
(14, 3, 'enrolled', 0),
(15, 3, 'enrolled', 0);

-- Class 4 (Velocity Sprint, Cardio): 5 enrolled, 3 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(2, 4, 'enrolled', 1),
(4, 4, 'enrolled', 1),
(5, 4, 'enrolled', 1),
(11, 4, 'enrolled', 0),
(13, 4, 'enrolled', 0);

-- Class 5 (Core Control, Other): 4 enrolled, 2 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(6, 5, 'enrolled', 1),
(7, 5, 'enrolled', 1),
(10, 5, 'enrolled', 0),
(15, 5, 'enrolled', 0);

-- Class 6 (Heavy Volume, Strength): 4 enrolled, 3 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 6, 'enrolled', 1),
(3, 6, 'enrolled', 1),
(8, 6, 'enrolled', 1),
(12, 6, 'enrolled', 0);

-- Class 7 (Metabolic Reset, HIIT): 5 enrolled, 2 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(2, 7, 'enrolled', 1),
(4, 7, 'enrolled', 1),
(9, 7, 'enrolled', 0),
(11, 7, 'enrolled', 0),
(14, 7, 'enrolled', 0);

-- Class 8 (Road Warrior, Cardio): 4 enrolled, 0 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(2, 8, 'enrolled', 0),
(5, 8, 'enrolled', 0),
(11, 8, 'enrolled', 0),
(13, 8, 'enrolled', 0);

-- Class 9 (Total Strength, Strength, capacity 10): 10 enrolled (Fully Booked!), 2 waitlisted
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 9, 'enrolled', 1),
(2, 9, 'enrolled', 1),
(3, 9, 'enrolled', 1),
(4, 9, 'enrolled', 1),
(5, 9, 'enrolled', 1),
(6, 9, 'enrolled', 1),
(7, 9, 'enrolled', 1),
(8, 9, 'enrolled', 1),
(9, 9, 'enrolled', 1),
(10, 9, 'enrolled', 1),
(11, 9, 'waitlist', 0),
(12, 9, 'waitlist', 0);

-- Class 10 (Flow & Breath, Yoga): 4 enrolled, 3 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 10, 'enrolled', 1),
(7, 10, 'enrolled', 1),
(10, 10, 'enrolled', 1),
(15, 10, 'enrolled', 0);

-- Class 11 (Box & Burn, HIIT): 4 enrolled, 0 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(4, 11, 'enrolled', 0),
(5, 11, 'enrolled', 0),
(11, 11, 'enrolled', 0),
(14, 11, 'enrolled', 0);

-- Class 12 (Functional Flow, Other): 4 enrolled, 2 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(2, 12, 'enrolled', 1),
(6, 12, 'enrolled', 1),
(13, 12, 'enrolled', 0),
(15, 12, 'enrolled', 0);

-- Class 13 (Snatch Clinic, Strength, capacity 8): 8 enrolled, 3 waitlisted
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 13, 'enrolled', 0),
(2, 13, 'enrolled', 0),
(3, 13, 'enrolled', 0),
(4, 13, 'enrolled', 0),
(5, 13, 'enrolled', 0),
(6, 13, 'enrolled', 0),
(7, 13, 'enrolled', 0),
(8, 13, 'enrolled', 0),
(9, 13, 'waitlist', 0),
(10, 13, 'waitlist', 0),
(15, 13, 'waitlist', 0);

-- Class 14 (Friday Finisher, HIIT, capacity 25): 12 enrolled
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 14, 'enrolled', 0),
(2, 14, 'enrolled', 0),
(3, 14, 'enrolled', 0),
(4, 14, 'enrolled', 0),
(5, 14, 'enrolled', 0),
(6, 14, 'enrolled', 0),
(7, 14, 'enrolled', 0),
(8, 14, 'enrolled', 0),
(11, 14, 'enrolled', 0),
(13, 14, 'enrolled', 0),
(14, 14, 'enrolled', 0),
(15, 14, 'enrolled', 0);

-- Class 15 (Deep Stretch Yin, Yoga): 4 enrolled, 0 attended
INSERT INTO enrollments (member_id, class_id, status, attended) VALUES 
(1, 15, 'enrolled', 0),
(7, 15, 'enrolled', 0),
(10, 15, 'enrolled', 0),
(15, 15, 'enrolled', 0),

-- Class 16 (Weekend Warrior HIIT, Saturday): 5 enrolled, 0 attended
(2, 16, 'enrolled', 0),
(4, 16, 'enrolled', 0),
(5, 16, 'enrolled', 0),
(11, 16, 'enrolled', 0),
(13, 16, 'enrolled', 0),

-- Class 17 (Saturday Power Lift, Saturday): 6 enrolled, 0 attended
(1, 17, 'enrolled', 0),
(3, 17, 'enrolled', 0),
(6, 17, 'enrolled', 0),
(8, 17, 'enrolled', 0),
(9, 17, 'enrolled', 0),
(12, 17, 'enrolled', 0),

-- Class 18 (Sunday Zen Yoga, Sunday): 4 enrolled, 0 attended
(1, 18, 'enrolled', 0),
(7, 18, 'enrolled', 0),
(10, 18, 'enrolled', 0),
(15, 18, 'enrolled', 0);

-- Class Reviews
INSERT INTO reviews (member_id, class_id, rating, comment) VALUES 
(1, 1, 5, 'Absolutely loved the morning energy! Sarah is an amazing instructor.'),
(2, 1, 4, 'Very calming and structured. Great way to start the day.'),
(7, 1, 5, 'Perfect flow. Studio 1 is really peaceful.'),
(8, 1, 4, 'Great alignment tips, though the room got a bit cold.'),
(10, 1, 5, 'First time trying yoga, loved the guidance.'),
(1, 2, 5, 'Incredible intensity and feedback on lifting posture.'),
(2, 2, 4, 'Good session, but the barbell collars were a bit loose.'),
(3, 2, 5, 'Excellent coaching on form and heavy lifts. Marcus knows his stuff!'),
(4, 2, 4, 'Great group energy. Really pushed my strength thresholds.'),
(6, 2, 5, 'Solid session. Reached a new PR on deadlifts.'),
(8, 2, 4, 'Intense workout, great atmosphere.'),
(2, 3, 5, 'Intense! Chloe keeps the motivation high the entire time.'),
(4, 3, 5, 'Incredible calorie burner. Highly recommend!'),
(6, 3, 5, 'Pushed my limits. Exactly what I needed.'),
(2, 4, 4, 'Great sprint intervals. David is a very energetic coach.'),
(6, 5, 4, 'Focus on core alignment was very helpful.'),
(1, 6, 5, 'Outstanding strength structure.'),
(3, 6, 5, 'Excellent strength training structure.'),
(8, 6, 5, 'Super helpful tips on powerlifting form.'),
(2, 7, 5, 'Tough session but felt amazing afterwards!'),
(1, 9, 5, 'Perfect strength building routine.'),
(2, 9, 4, 'Very tough but satisfying.'),
(3, 9, 5, 'Great coaching on structural balance.'),
(4, 9, 5, 'Highly recommend for heavy strength training.'),
(5, 9, 4, 'Intense but accessible if you scale the weights.'),
(6, 9, 5, 'Excellent pace and weight progression.'),
(7, 9, 5, 'Loved the programming! Perfect coaching.'),
(8, 9, 5, 'Awesome session.'),
(1, 10, 5, 'Very restorative. Loved it.'),
(7, 10, 4, 'Nice breathwork integration.'),
(2, 12, 4, 'Helpful mobility drills.');

-- Support Reports
INSERT INTO reports (member_id, category, subject, description, status, admin_response, member_response, closed_by_member, created_at, updated_at) VALUES 
(2, 'equipment', 'StairMaster screen is broken', 'The display screen on StairMaster #2 is flickering and doesn''t register heart rate input.', 'open', null, null, 0, '2026-05-28 09:15:00', '2026-05-28 09:15:00'),
(1, 'class', 'Class timing conflict', 'The Sunrise Vinyasa class overlaps slightly with my work calendar. Is it possible to introduce a 7:30 AM slot?', 'in_progress', 'We are currently reviewing the schedule for next season. Thanks for the feedback!', 'Thanks, please keep me posted if the schedule changes.', 0, '2026-05-20 14:30:00', '2026-05-22 10:00:00'),
(4, 'trainer', 'Special request for tennis training', 'Are any of the trainers qualified in specific athletic prep for tennis?', 'resolved', 'Hi Serena! Marcus Thorne has extensive experience training professional athletes in agility and power, including tennis players. You can check his bio.', 'Perfect, I will book a session with Marcus. Thank you!', 1, '2026-05-15 11:00:00', '2026-05-16 15:30:00'),
(6, 'equipment', 'Pec Deck machine cable is fraying', 'I noticed the cable on the Pec Deck machine has some metal threads starting to pull apart. Might be unsafe.', 'open', null, null, 0, '2026-05-29 16:00:00', '2026-05-29 16:00:00'),
(11, 'other', 'Missing water bottle', 'I left a black hydroflask on the cardio floor yesterday. Has anyone turned it in to lost and found?', 'resolved', 'Yes, Elena! A black water bottle was turned in to the front desk. You can pick it up anytime.', 'Found it, thank you!', 1, '2026-05-25 10:00:00', '2026-05-26 09:00:00');

-- Notifications
INSERT INTO notifications (user_id, message, link, is_read, created_at) VALUES 
(1, 'New support report submitted by Elena Rostova: StairMaster screen is broken', 'admin_reports.php', 0, '2026-05-28 09:15:00'),
(1, 'New support report submitted by Serena Williams: Special request for tennis training', 'admin_reports.php', 1, '2026-05-15 11:00:00'),
(2, 'Admin responded to your report: Class timing conflict', 'reports.php', 0, '2026-05-22 10:00:00'),
(13, 'Admin responded to your report: Special request for tennis training', 'reports.php', 1, '2026-05-16 15:30:00'),
(1, 'New support report submitted by Bruce Wayne: Pec Deck machine cable is fraying', 'admin_reports.php', 0, '2026-05-29 16:00:00');