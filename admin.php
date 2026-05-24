<?php
session_start();
require_once 'database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$successMessage = $_GET['success'] ?? null;
$errorMessage = $_GET['error'] ?? null;

$db = getDatabaseConnection();

$totalMembers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
$totalTrainers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'trainer'")->fetchColumn();
$totalClasses = $db->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$totalEquipment = $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();


$occupancyQuery = $db->query("
    SELECT 
        CASE 
            WHEN SUM(capacity) > 0 
            THEN ROUND((COUNT(enrollments.id) * 100.0) / SUM(capacity), 1)
            ELSE 0 
        END
    FROM classes
    LEFT JOIN enrollments 
        ON classes.id = enrollments.class_id 
        AND enrollments.status = 'enrolled'
");
$avgOccupancy = $occupancyQuery->fetchColumn();


$popularityQuery = $db->query("
    SELECT 
        classes.type, 
        COUNT(enrollments.id) as total_enrollments
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
    WHERE total_units > 0 AND ((available_units * 100.0) / total_units) <= 25.0
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
        classes.id,
        classes.name,
        classes.type,
        classes.capacity,
        classes.start_time,
        classes.location,
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

$pageTitle = "Admin Dashboard | Flow Fitness";
$currentPage = 'admin';
$extraCSS = 'admin.css';

include 'templates/header.php';
?>

<main class="admin-page bg-light">
    <div class="container">
        <section class="admin-hero">
            <div>
                <span class="admin-eyebrow">Flow Control</span>
                <h1>Admin Dashboard</h1>
                <p class="text-muted">Manage users, monitor classes, and oversee gym equipment.</p>
            </div>
            <a href="actions/action_logout.php" class="btn-outline-danger admin-logout-btn">
                Log Out
            </a>
        </section>

        <?php if ($successMessage): ?>
            <div class="admin-alert success-alert">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="admin-alert error-alert">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <section class="admin-stats-grid">
            <article class="admin-stat-card card">
                <span class="stat-label">Members</span>
                <strong><?php echo htmlspecialchars($totalMembers); ?></strong>
            </article>

            <article class="admin-stat-card card">
                <span class="stat-label">Trainers</span>
                <strong><?php echo htmlspecialchars($totalTrainers); ?></strong>
            </article>

            <article class="admin-stat-card card">
                <span class="stat-label">Classes</span>
                <strong><?php echo htmlspecialchars($totalClasses); ?></strong>
            </article>

            <article class="admin-stat-card card <?php echo $criticalEquipmentCount > 0 ? 'alert-border-danger' : ''; ?>">
                <span class="stat-label">Critical Equipments</span>
                <strong class="<?php echo $criticalEquipmentCount > 0 ? 'text-danger-focused' : ''; ?>">
                    <?php echo htmlspecialchars($criticalEquipmentCount); ?>
                </strong>
            </article>
        </section>

        <section class="analytics-row-grid">
            <article class="analytics-panel card">
                <h3>Gym Occupancy Status</h3>
                <p class="text-muted">Average class slot utilization rate across the facilities.</p>
                <div class="occupancy-display">
                    <span class="occupancy-percentage"><?php echo htmlspecialchars($avgOccupancy); ?>%</span>
                    <div class="occupancy-bar-bg">
                        <div class="occupancy-bar-fill" style="width: <?php echo htmlspecialchars($avgOccupancy); ?>%;"></div>
                    </div>
                </div>
            </article>

            <article class="analytics-panel card">
                <h3>Popular Disciplines</h3>
                <p class="text-muted">Top training methodologies by volume of active bookings.</p>
                <ul class="analytics-discipline-list">
                    <?php if (count($disciplinePopularity) > 0): ?>
                        <?php foreach ($disciplinePopularity as $discipline): ?>
                            <li>
                                <span class="discipline-name text-capitalize"><?php echo htmlspecialchars($discipline['type']); ?></span>
                                <span class="discipline-count badge-gold"><?php echo htmlspecialchars($discipline['total_enrollments']); ?> bookings</span>
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
                        <h4><?php echo htmlspecialchars($topStreakMember['name']); ?></h4>
                        <p class="streak-score">🔥 <?php echo htmlspecialchars($topStreakMember['streak']); ?> <small>Days Active</small></p>
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
                    <p class="text-muted">Overview of registered members, trainers, and admins.</p>
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
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['plan_name'] ?? '—'); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form action="actions/action_promote_user.php" method="POST">
                                                <input 
                                                    type="hidden" 
                                                    name="csrf_token" 
                                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                                                >
                                                <input 
                                                    type="hidden" 
                                                    name="user_id" 
                                                    value="<?php echo htmlspecialchars($user['id']); ?>"
                                                >
                                                <button type="submit" class="admin-action-btn promote-btn">
                                                    Promote
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                                            <form 
                                                action="actions/action_delete_user.php" 
                                                method="POST" 
                                                onsubmit="return confirm('Are you sure you want to delete this user?');"
                                            >
                                                <input 
                                                    type="hidden" 
                                                    name="csrf_token" 
                                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                                                >
                                                <input 
                                                    type="hidden" 
                                                    name="user_id" 
                                                    value="<?php echo htmlspecialchars($user['id']); ?>"
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
                    <h2>Class Monitor</h2>
                    <p class="text-muted">Current class capacity and trainer assignments.</p>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Type</th>
                            <th>Trainer</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Capacity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <?php
                                $date = date('M d, Y H:i', strtotime($class['start_time']));
                                $capacityText = $class['enrolled_count'] . '/' . $class['capacity'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['name']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($class['type'])); ?></td>
                                <td><?php echo htmlspecialchars($class['trainer_name'] ?? 'TBA'); ?></td>
                                <td><?php echo htmlspecialchars($date); ?></td>
                                <td><?php echo htmlspecialchars($class['location']); ?></td>
                                <td><?php echo htmlspecialchars($capacityText); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section card">
            <div class="admin-section-header">
                <div>
                    <h2>Equipment Status</h2>
                    <p class="text-muted">Live snapshot of available equipment units.</p>
                </div>
            </div>

            <div class="equipment-admin-grid">
                <?php foreach ($equipment as $item): ?>
                    <?php
                        $percentage = $item['total_units'] > 0 
                            ? round(($item['available_units'] / $item['total_units']) * 100) 
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
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['location']); ?></p>
                            </div>

                            <span class="equipment-type">
                                <?php echo htmlspecialchars($item['type']); ?>
                            </span>
                        </div>

                        <div class="equipment-progress">
                            <div 
                                class="equipment-progress-fill <?php echo htmlspecialchars($statusClass); ?>" 
                                style="width: <?php echo htmlspecialchars($percentage); ?>%;"
                            ></div>
                        </div>

                        <form action="actions/action_update_equipment.php" method="POST" class="equipment-update-form">
                            <input 
                                type="hidden" 
                                name="csrf_token" 
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                            >

                            <input 
                                type="hidden" 
                                name="equipment_id" 
                                value="<?php echo htmlspecialchars($item['id']); ?>"
                            >

                            <label>
                                Available units
                                <span><?php echo htmlspecialchars($item['total_units']); ?> total</span>
                            </label>

                            <div class="equipment-update-controls">
                                <input 
                                    type="number" 
                                    name="available_units" 
                                    min="0" 
                                    max="<?php echo htmlspecialchars($item['total_units']); ?>" 
                                    value="<?php echo htmlspecialchars($item['available_units']); ?>"
                                    required
                                >

                                <button type="submit" class="admin-action-btn promote-btn">
                                    Update
                                </button>
                            </div>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php include 'templates/footer.php'; ?>