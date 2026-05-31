<?php

session_start();

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/class_occurrence.php';
require_once __DIR__ . '/includes/ui_helpers.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}



$db = getDatabaseConnection();

$totalMembers = $db->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'member'
")->fetchColumn();

$totalTrainers = $db->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'trainer'
")->fetchColumn();

$totalClasses = $db->query("
    SELECT COUNT(*)
    FROM classes
")->fetchColumn();

$occupancyQuery = $db->query("
    SELECT
        CASE
            WHEN (SELECT SUM(capacity) FROM classes) > 0
            THEN ROUND((SELECT COUNT(*) FROM enrollments WHERE status = 'enrolled') * 100.0 / (SELECT SUM(capacity) FROM classes), 1)
            ELSE 0
        END
");

$avgOccupancy = $occupancyQuery->fetchColumn();

$popularityQuery = $db->query("
    SELECT
        classes.type,
        COUNT(enrollments.id) AS total_enrollments
    FROM classes
    LEFT JOIN enrollments
        ON classes.id = enrollments.class_id
        AND enrollments.status = 'enrolled'
    GROUP BY classes.type
    ORDER BY total_enrollments DESC
");

$disciplinePopularity = $popularityQuery->fetchAll();

$topStreakQuery = $db->query("
    SELECT users.name, members.streak
    FROM members
    JOIN users ON members.user_id = users.id
    ORDER BY members.streak DESC
    LIMIT 1
");

$topStreakMember = $topStreakQuery->fetch();

$criticalEquipmentCount = $db->query("
    SELECT COUNT(*)
    FROM equipment
    WHERE total_units > 0
      AND ((available_units * 100.0) / total_units) <= 25.0
")->fetchColumn();

$stmt = $db->query("
    SELECT
        users.id,
        users.name,
        users.username,
        users.email,
        users.role,
        users.created_at,
        plans.name AS plan_name
    FROM users
    LEFT JOIN members ON users.id = members.user_id
    LEFT JOIN plans ON members.plan_id = plans.id
    ORDER BY users.created_at DESC
");

$users = $stmt->fetchAll();

$stmt = $db->query("
    SELECT
        trainers.id,
        users.name
    FROM trainers
    JOIN users ON trainers.user_id = users.id
    ORDER BY users.name ASC
");

$trainers = $stmt->fetchAll();

$stmt = $db->query("
    SELECT
        classes.id,
        classes.name,
        classes.type,
        classes.capacity,
        classes.start_time,
        classes.duration_min,
        classes.location,
        classes.trainer_id,
        users.name AS trainer_name,
        COUNT(enrollments.id) AS enrolled_count
    FROM classes
    LEFT JOIN trainers ON classes.trainer_id = trainers.id
    LEFT JOIN users ON trainers.user_id = users.id
    LEFT JOIN enrollments
        ON classes.id = enrollments.class_id
        AND enrollments.status = 'enrolled'
    GROUP BY classes.id
    ORDER BY classes.start_time ASC
");

$classes = $stmt->fetchAll();

$stmt = $db->query("
    SELECT *
    FROM equipment
    ORDER BY type ASC, name ASC
");

$equipment = $stmt->fetchAll();
$classes = applyNextWeeklyOccurrences($classes);

$pageTitle = 'Admin Dashboard | Flow Fitness';
$currentPage = 'admin';
$extraCSS = 'admin.css';

include __DIR__ . '/templates/header.php';
?>

<main class="admin-page bg-light">
    <div class="container">
        <section class="admin-hero">
            <div>
                <span class="admin-eyebrow">Flow Control</span>
                <h1>Admin Dashboard</h1>
                <p class="text-muted">
                    Manage users, class catalog, equipment and gym-wide operations.
                </p>
            </div>

            <form action="actions/action_logout.php" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                >

                <button type="submit" class="btn-outline-danger admin-logout-btn">
                    Log Out
                </button>
            </form>
        </section>

        <?php render_alerts(null, 'admin-alert'); ?>

        <section class="admin-stats-grid">
            <article class="admin-stat-card card">
                <span class="stat-label">Members</span>
                <strong><?php echo htmlspecialchars((string) $totalMembers, ENT_QUOTES, 'UTF-8'); ?></strong>
            </article>

            <article class="admin-stat-card card">
                <span class="stat-label">Trainers</span>
                <strong><?php echo htmlspecialchars((string) $totalTrainers, ENT_QUOTES, 'UTF-8'); ?></strong>
            </article>

            <article class="admin-stat-card card">
                <span class="stat-label">Classes</span>
                <strong><?php echo htmlspecialchars((string) $totalClasses, ENT_QUOTES, 'UTF-8'); ?></strong>
            </article>

            <article class="admin-stat-card card <?php echo (int) $criticalEquipmentCount > 0 ? 'alert-border-danger' : ''; ?>">
                <span class="stat-label">Critical Equipment</span>
                <strong class="<?php echo (int) $criticalEquipmentCount > 0 ? 'text-danger-focused' : ''; ?>">
                    <?php echo htmlspecialchars((string) $criticalEquipmentCount, ENT_QUOTES, 'UTF-8'); ?>
                </strong>
            </article>
        </section>

        <section class="analytics-row-grid">
            <article class="analytics-panel card">
                <h3>Gym Occupancy Status</h3>
                <p class="text-muted">Average class slot utilization rate across the facilities.</p>

                <div class="occupancy-display">
                    <span class="occupancy-percentage">
                        <?php echo htmlspecialchars((string) $avgOccupancy, ENT_QUOTES, 'UTF-8'); ?>%
                    </span>

                    <div class="occupancy-bar-bg">
                        <div
                            class="occupancy-bar-fill js-progress-fill"
                            data-progress="<?php echo htmlspecialchars((string) $avgOccupancy, ENT_QUOTES, 'UTF-8'); ?>"
                        ></div>
                    </div>
                </div>
            </article>

            <article class="analytics-panel card">
                <h3>Popular Disciplines</h3>
                <p class="text-muted">Top training methodologies by active bookings.</p>

                <ul class="analytics-discipline-list">
                    <?php if (count($disciplinePopularity) > 0): ?>
                        <?php foreach ($disciplinePopularity as $discipline): ?>
                            <li>
                                <span class="discipline-name text-capitalize">
                                    <?php echo htmlspecialchars($discipline['type'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <span class="discipline-count badge-gold">
                                    <?php echo htmlspecialchars((string) $discipline['total_enrollments'], ENT_QUOTES, 'UTF-8'); ?> bookings
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="text-muted">No active registrations recorded.</li>
                    <?php endif; ?>
                </ul>
            </article>

            <article class="analytics-panel card">
                <h3>Community Spotlight</h3>
                <p class="text-muted">Current highest workout streak in the ecosystem.</p>

                <?php if ($topStreakMember): ?>
                    <div class="spotlight-content">
                        <h4><?php echo htmlspecialchars($topStreakMember['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                        <p class="streak-score">
                            🔥 <?php echo htmlspecialchars((string) $topStreakMember['streak'], ENT_QUOTES, 'UTF-8'); ?>
                            <small>Days Active</small>
                        </p>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No active member data found.</p>
                <?php endif; ?>
            </article>
        </section>

        <section class="admin-section card">
            <div class="admin-section-header">
                <div>
                    <h2>Users</h2>
                    <p class="text-muted">Overview of registered members, trainers and admins.</p>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Plan</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['plan_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form action="actions/action_promote_user.php" method="POST">
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="user_id"
                                                    value="<?php echo htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >

                                                <button type="submit" class="admin-action-btn promote-btn">
                                                    Promote
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                                            <form
                                                action="actions/action_delete_user.php"
                                                method="POST"
                                                class="js-confirm-form"
                                                data-confirm-message="Are you sure you want to delete this user?"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="user_id"
                                                    value="<?php echo htmlspecialchars((string) $user['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                                >

                                                <button type="submit" class="admin-action-btn delete-btn">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section card">
            <div class="admin-section-header">
                <div>
                    <h2>Class Catalog Management</h2>
                    <p class="text-muted">Create new classes and remove outdated sessions.</p>
                </div>
            </div>

            <form action="actions/action_create_class.php" method="POST" class="admin-management-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                >

                <div class="admin-form-grid">
                    <div class="form-group">
                        <label for="class-name">Class name</label>
                        <input
                            type="text"
                            id="class-name"
                            name="name"
                            placeholder="Example: Power Yoga"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="class-type">Type</label>
                        <select id="class-type" name="type" required>
                            <option value="yoga">Yoga</option>
                            <option value="hiit">HIIT</option>
                            <option value="strength">Strength</option>
                            <option value="cardio">Cardio</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class-trainer">Trainer</label>
                        <select id="class-trainer" name="trainer_id">
                            <option value="">No trainer assigned</option>

                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?php echo htmlspecialchars((string) $trainer['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class-capacity">Capacity</label>
                        <input
                            type="number"
                            id="class-capacity"
                            name="capacity"
                            min="1"
                            value="20"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="class-start-time">Start time</label>
                        <input
                            type="datetime-local"
                            id="class-start-time"
                            name="start_time"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="class-duration">Duration</label>
                        <input
                            type="number"
                            id="class-duration"
                            name="duration_min"
                            min="15"
                            step="5"
                            value="60"
                            required
                        >
                    </div>

                    <div class="form-group admin-form-wide">
                        <label for="class-location">Location</label>
                        <input
                            type="text"
                            id="class-location"
                            name="location"
                            placeholder="Example: Studio 1"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-primary admin-submit-btn">
                    Add Class
                </button>
            </form>

            <div class="admin-table-wrapper admin-management-table">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <?php
                                $date = date('M d, Y H:i', strtotime($class['start_time']));
                                $capacityText = $class['enrolled_count'] . '/' . $class['capacity'];
                            ?>

                            <tr>
                                <td><?php echo htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($class['type']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($class['trainer_name'] ?? 'TBA', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string) $class['duration_min'], ENT_QUOTES, 'UTF-8'); ?> min</td>
                                <td><?php echo htmlspecialchars($class['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($capacityText, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <form
                                        action="actions/action_delete_class.php"
                                        method="POST"
                                        class="js-confirm-form"
                                        data-confirm-message="Delete this class and its enrollments/reviews?"
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

                                        <button type="submit" class="admin-action-btn delete-btn">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section card">
            <div class="admin-section-header">
                <div>
                    <h2>Equipment Management</h2>
                    <p class="text-muted">Add, update and remove equipment from the main training areas.</p>
                </div>
            </div>

            <form action="actions/action_create_equipment.php" method="POST" class="admin-management-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                >

                <div class="admin-form-grid">
                    <div class="form-group">
                        <label for="equipment-name">Equipment name</label>
                        <input
                            type="text"
                            id="equipment-name"
                            name="name"
                            placeholder="Example: Assault Bike"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="equipment-type">Type</label>
                        <input
                            type="text"
                            id="equipment-type"
                            name="type"
                            placeholder="Example: cardio"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="equipment-location">Location</label>
                        <input
                            type="text"
                            id="equipment-location"
                            name="location"
                            placeholder="Example: Cardio Zone"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="equipment-total">Total units</label>
                        <input
                            type="number"
                            id="equipment-total"
                            name="total_units"
                            min="1"
                            value="1"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="equipment-available">Available units</label>
                        <input
                            type="number"
                            id="equipment-available"
                            name="available_units"
                            min="0"
                            value="1"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-primary admin-submit-btn">
                    Add Equipment
                </button>
            </form>

            <div class="equipment-admin-grid">
                <?php foreach ($equipment as $item): ?>
                    <?php
                        $percentage = (int) $item['total_units'] > 0
                            ? round(((int) $item['available_units'] / (int) $item['total_units']) * 100)
                            : 0;

                        $statusClass = 'status-good';

                        if ($percentage <= 25) {
                            $statusClass = 'status-danger';
                        } elseif ($percentage <= 60) {
                            $statusClass = 'status-warning';
                        }
                    ?>

                    <article class="equipment-admin-card">
                        <div class="equipment-admin-top">
                            <div>
                                <h3><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars($item['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>

                            <span class="equipment-type">
                                <?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <div class="equipment-progress">
                            <div
                                class="equipment-progress-fill js-progress-fill <?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"
                                data-progress="<?php echo htmlspecialchars((string) $percentage, ENT_QUOTES, 'UTF-8'); ?>"
                            ></div>
                        </div>

                        <form action="actions/action_update_equipment.php" method="POST" class="equipment-update-form">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <input
                                type="hidden"
                                name="equipment_id"
                                value="<?php echo htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <label>
                                Available units
                                <span><?php echo htmlspecialchars((string) $item['total_units'], ENT_QUOTES, 'UTF-8'); ?> total</span>
                            </label>

                            <div class="equipment-update-controls">
                                <input
                                    type="number"
                                    name="available_units"
                                    min="0"
                                    max="<?php echo htmlspecialchars((string) $item['total_units'], ENT_QUOTES, 'UTF-8'); ?>"
                                    value="<?php echo htmlspecialchars((string) $item['available_units'], ENT_QUOTES, 'UTF-8'); ?>"
                                    required
                                >

                                <button type="submit" class="admin-action-btn promote-btn">
                                    Update
                                </button>
                            </div>
                        </form>

                        <form
                            action="actions/action_delete_equipment.php"
                            method="POST"
                            class="equipment-delete-form js-confirm-form"
                            data-confirm-message="Delete this equipment item?"
                        >
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <input
                                type="hidden"
                                name="equipment_id"
                                value="<?php echo htmlspecialchars((string) $item['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <button type="submit" class="admin-action-btn delete-btn">
                                Delete Equipment
                            </button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<script src="js/admin.js"></script>

<?php include __DIR__ . '/templates/footer.php'; ?>