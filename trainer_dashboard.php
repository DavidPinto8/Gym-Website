<?php

session_start();

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/avatar.php';
require_once __DIR__ . '/includes/class_occurrence.php';
require_once __DIR__ . '/includes/ui_helpers.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'trainer') {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT
        users.id,
        users.name,
        users.photo,
        trainers.bio,
        trainers.specializations,
        trainers.certifications,
        trainers.id AS trainer_id
    FROM users
    JOIN trainers ON users.id = trainers.user_id
    WHERE users.id = ?
");

$stmt->execute([$_SESSION['user_id']]);

$trainer = $stmt->fetch();

if (!$trainer) {
    header('Location: login.php');
    exit();
}

$stmt = $db->prepare("
    SELECT
        classes.*,
        (
            SELECT COUNT(*)
            FROM enrollments
            WHERE class_id = classes.id
              AND status = 'enrolled'
        ) AS enrolled_count
    FROM classes
    WHERE trainer_id = ?
    ORDER BY start_time ASC
");

$stmt->execute([$trainer['trainer_id']]);

$trainerClasses = applyNextWeeklyOccurrences($stmt->fetchAll());



$pageTitle = 'Trainer Dashboard | Flow Fitness';
$currentPage = 'trainer_dashboard';
$extraCSS = 'profile.css';

include __DIR__ . '/templates/header.php';
?>

<main class="profile-page bg-light">
    <div class="container profile-layout">
        <aside class="profile-sidebar card">
            <div class="profile-summary text-center">
                <?php
                    $profileAvatar = getUserAvatar(
                        $trainer['name'],
                        $trainer['photo'] ?? null,
                        180
                    );
                ?>

                <img
                    src="<?php echo htmlspecialchars($profileAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?>"
                >

                <h3><?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <span class="badge-gold">Instructor</span>
            </div>

            <nav class="profile-nav">
                <ul>
                    <li>
                        <a href="#my-classes" class="active">My Schedule &amp; Roster</a>
                    </li>

                    <li>
                        <a href="#trainer-profile">Public Profile</a>
                    </li>

                    <li>
                        <form action="actions/action_logout.php" method="POST">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <button type="submit" class="text-danger">
                                Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <section class="profile-content">
            <?php render_alerts(); ?>

            <section id="my-classes" class="card form-container">
                <h2>My Upcoming Classes</h2>
                <p class="text-muted">
                    Manage your schedule, view class rosters and mark attendance.
                </p>

                <div class="trainer-schedule-grid">
                    <?php if (count($trainerClasses) > 0): ?>
                        <?php foreach ($trainerClasses as $class): ?>
                            <?php
                                $stmt = $db->prepare("
                                    SELECT
                                        members.id AS member_id,
                                        users.name,
                                        enrollments.attended
                                    FROM enrollments
                                    JOIN members ON enrollments.member_id = members.id
                                    JOIN users ON members.user_id = users.id
                                    WHERE enrollments.class_id = ?
                                      AND enrollments.status = 'enrolled'
                                    ORDER BY users.name ASC
                                ");

                                $stmt->execute([$class['id']]);

                                $roster = $stmt->fetchAll();
                                $rosterId = 'roster-' . $class['id'];
                            ?>

                            <article class="stat-card card trainer-class-card">
                                <h3 class="trainer-class-title">
                                    <?php echo htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>

                                <p class="text-muted trainer-class-time">
                                    <?php echo htmlspecialchars(date('M d, Y - H:i', strtotime($class['start_time'])), ENT_QUOTES, 'UTF-8'); ?>
                                    |
                                    <?php echo htmlspecialchars($class['location'], ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                                <button
                                    type="button"
                                    class="btn-outline btn-block trainer-roster-btn js-toggle-roster"
                                    data-roster-target="<?php echo htmlspecialchars($rosterId, ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    View Roster
                                    (<?php echo htmlspecialchars((string) $class['enrolled_count'], ENT_QUOTES, 'UTF-8'); ?>/<?php echo htmlspecialchars((string) $class['capacity'], ENT_QUOTES, 'UTF-8'); ?>)
                                </button>

                                <div
                                    id="<?php echo htmlspecialchars($rosterId, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="class-roster-list roster-hidden"
                                >
                                    <?php if (count($roster) > 0): ?>
                                        <form
                                            action="actions/action_mark_attendance.php"
                                            method="POST"
                                            class="attendance-form"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="class_id"
                                                value="<?php echo htmlspecialchars((string) $class['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                            >

                                            <ul>
                                                <?php foreach ($roster as $student): ?>
                                                    <li>
                                                        <label class="attendance-row">
                                                            <input
                                                                type="checkbox"
                                                                name="attended_members[]"
                                                                value="<?php echo htmlspecialchars((string) $student['member_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                <?php echo (int) $student['attended'] === 1 ? 'checked' : ''; ?>
                                                            >

                                                            <span class="student-name">
                                                                <?php echo htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </span>

                                                            <?php if ((int) $student['attended'] === 1): ?>
                                                                <span class="attendance-badge">Attended</span>
                                                            <?php endif; ?>
                                                        </label>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>

                                            <button
                                                type="submit"
                                                class="btn-primary btn-block attendance-save-btn"
                                            >
                                                Save Attendance
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <p class="text-muted text-center empty-roster-message">
                                            No members enrolled yet.
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted empty-dashboard-message">
                            You have no upcoming classes assigned at the moment.
                        </p>
                    <?php endif; ?>
                </div>
            </section>

            <section id="trainer-profile" class="card form-container">
                <h2>Public Profile Settings</h2>
                <p class="text-muted">
                    Update how members see you on the Trainers page.
                </p>

                <form
                    action="actions/action_update_trainer.php"
                    method="POST"
                    class="profile-form"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <fieldset>
                        <legend>Professional Info</legend>

                        <div class="form-group">
                            <label for="bio">Biography</label>

                            <textarea
                                id="bio"
                                name="bio"
                                rows="4"
                                class="custom-select form-textarea"
                                required
                            ><?php echo htmlspecialchars($trainer['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="specializations">Specializations</label>

                            <input
                                type="text"
                                id="specializations"
                                name="specializations"
                                value="<?php echo htmlspecialchars($trainer['specializations'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Example: Yoga, Mobility, Strength"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="certifications">Certifications</label>

                            <input
                                type="text"
                                id="certifications"
                                name="certifications"
                                value="<?php echo htmlspecialchars($trainer['certifications'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="Example: NASM CPT, Yoga Alliance RYT"
                                required
                            >
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            Update Public Profile
                        </button>
                    </div>
                </form>
            </section>
        </section>
    </div>
</main>

<script src="js/trainer_dashboard.js"></script>

<?php include __DIR__ . '/templates/footer.php'; ?>