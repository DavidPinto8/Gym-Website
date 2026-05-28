<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'database/db.php';
require_once 'includes/avatar.php';

$db = getDatabaseConnection();

$memberId = null;

if (isset($_SESSION['user_id'])) {
    $stmt_member = $db->prepare("
        SELECT id
        FROM members
        WHERE user_id = ?
    ");
    $stmt_member->execute([$_SESSION['user_id']]);
    $member = $stmt_member->fetch();

    if ($member) {
        $memberId = $member['id'];
    }
}

$stmtTrainers = $db->query("
    SELECT
        trainers.id,
        users.name
    FROM trainers
    JOIN users ON trainers.user_id = users.id
    ORDER BY users.name ASC
");
$trainers = $stmtTrainers->fetchAll();

$stmt = $db->prepare("
    SELECT
        classes.*,
        users.name AS trainer_name,
        users.photo AS trainer_photo,

        COUNT(
            DISTINCT CASE
                WHEN enrollments.status = 'enrolled'
                THEN enrollments.id
            END
        ) AS enrolled_count,

        MAX(
            CASE
                WHEN enrollments.member_id = :member_id
                THEN enrollments.status
            END
        ) AS user_booking_status,

        (
            SELECT COUNT(*) + 1
            FROM enrollments AS e2
            WHERE e2.class_id = classes.id
              AND e2.status = 'waitlist'
              AND e2.id < (
                  SELECT id
                  FROM enrollments AS e3
                  WHERE e3.class_id = classes.id
                    AND e3.member_id = :member_id_sub
                    AND e3.status = 'waitlist'
                  LIMIT 1
              )
        ) AS waitlist_position,

        ROUND(AVG(reviews.rating), 1) AS average_rating,
        COUNT(DISTINCT reviews.id) AS review_count

    FROM classes

    LEFT JOIN trainers
        ON classes.trainer_id = trainers.id

    LEFT JOIN users
        ON trainers.user_id = users.id

    LEFT JOIN enrollments
        ON classes.id = enrollments.class_id

    LEFT JOIN reviews
        ON classes.id = reviews.class_id

    GROUP BY classes.id

    ORDER BY classes.start_time ASC
");

$stmt->execute([
    ':member_id' => $memberId,
    ':member_id_sub' => $memberId
]);

$classes = $stmt->fetchAll();

$pageTitle = "Classes | Flow Fitness";
$currentPage = 'schedule';
$extraCSS = 'schedule.css';

include 'templates/header.php';
?>

<main class="schedule-page">
    <div class="container schedule-layout">
        <aside class="filters">
            <span class="filters-eyebrow">Training Planner</span>
            <h2 class="filters-title">Filter Classes</h2>

            <form class="filter-form">
                <div class="form-group">
                    <label for="class-type">Class Type</label>

                    <select id="class-type">
                        <option value="all">All Disciplines</option>
                        <option value="yoga">Yoga</option>
                        <option value="hiit">HIIT</option>
                        <option value="strength">Strength</option>
                        <option value="cardio">Cardio</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="trainer-select">Trainer</label>

                    <select id="trainer-select">
                        <option value="all">All Trainers</option>

                        <?php foreach ($trainers as $trainer): ?>
                            <option value="<?php echo htmlspecialchars($trainer['id']); ?>">
                                <?php echo htmlspecialchars($trainer['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="day-select">Day of Week</label>

                    <select id="day-select">
                        <option value="all">All Days</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary btn-block">
                    Apply Filters
                </button>
            </form>
        </aside>

        <section class="classes-section">
            <div class="section-header">
                <span class="section-kicker">Flow Classes</span>
                <h2>Class Schedule</h2>
                <p>High performance sessions led by specialized trainers.</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="schedule-alert success-alert">
                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $_GET['success']))); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="schedule-alert error-alert">
                    <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $_GET['error']))); ?>
                </div>
            <?php endif; ?>

            <div class="classes-grid" id="classes-grid">
                <?php if (count($classes) > 0): ?>
                    <?php foreach ($classes as $class): ?>
                        <?php
                            $timestamp = strtotime($class['start_time']);
                            $dayOfWeek = date('l', $timestamp);
                            $timeFormatted = date('H:i', $timestamp);
                            $spotsLeft = (int)$class['capacity'] - (int)$class['enrolled_count'];
                            $isFull = $spotsLeft <= 0;
                            $bookingStatus = $class['user_booking_status'];
                            $trainerAvatar = getUserAvatar(
                                $class['trainer_name'] ?? 'Trainer',
                                $class['trainer_photo'] ?? null,
                                96
                            );
                        ?>

                        <article
                            class="class-card <?php echo htmlspecialchars($class['type']); ?> <?php echo $bookingStatus === 'enrolled' ? 'enrolled' : ($bookingStatus === 'waitlist' ? 'waitlisted' : ''); ?>"
                            data-type="<?php echo htmlspecialchars($class['type']); ?>"
                            data-day="<?php echo strtolower($dayOfWeek); ?>"
                        >
                            <div class="card-overlay"></div>

                            <div class="card-content">
                                <span class="class-tag">
                                    <?php echo ucfirst(htmlspecialchars($class['type'])); ?>
                                </span>

                                <h3>
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </h3>

                                <p class="class-info">
                                    <?php echo htmlspecialchars($dayOfWeek . ' • ' . $timeFormatted); ?>
                                </p>

                                <div class="class-trainer-card">
                                    <img
                                        src="<?php echo htmlspecialchars($trainerAvatar); ?>"
                                        alt="<?php echo htmlspecialchars($class['trainer_name'] ?? 'Trainer'); ?>"
                                    >

                                    <span>
                                        <small>Trainer</small>
                                        <?php echo htmlspecialchars($class['trainer_name'] ?? 'TBA'); ?>
                                    </span>
                                </div>

                                <p class="class-capacity">
                                    <?php echo htmlspecialchars($class['enrolled_count']); ?>/<?php echo htmlspecialchars($class['capacity']); ?> spots booked
                                </p>

                                <?php if ((int)$class['review_count'] > 0): ?>
                                    <p class="class-rating">
                                        ★ <?php echo htmlspecialchars(number_format((float)$class['average_rating'], 1)); ?>
                                        (<?php echo htmlspecialchars($class['review_count']); ?> reviews)
                                    </p>
                                <?php else: ?>
                                    <p class="class-rating">No reviews yet</p>
                                <?php endif; ?>

                                <a href="class_details.php?id=<?php echo htmlspecialchars($class['id']); ?>" class="btn-outline btn-block details-btn">
                                    View Details
                                </a>

                                <?php if (isset($_SESSION['user_id']) && $memberId): ?>
                                    <?php if ($bookingStatus === 'enrolled'): ?>
                                        <form action="actions/action_cancel_booking.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">

                                            <button type="submit" class="btn-outline btn-block cancel-btn">
                                                Cancel Booking
                                            </button>
                                        </form>
                                    <?php elseif ($bookingStatus === 'waitlist'): ?>
                                        <div class="waitlist-position-badge">
                                            <span>
                                                🔥 Position <strong>#<?php echo htmlspecialchars($class['waitlist_position'] ?? '1'); ?></strong> on Waitlist
                                            </span>
                                        </div>

                                        <form action="actions/action_cancel_booking.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">

                                            <button type="submit" class="btn-outline btn-block leave-waitlist-btn">
                                                Leave Waitlist
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="actions/action_book_class.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">

                                            <button type="submit" class="btn-primary btn-block">
                                                <?php echo $isFull ? 'Join Waitlist' : 'Book Spot'; ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php elseif (isset($_SESSION['user_id'])): ?>
                                    <p class="member-warning">
                                        Only members can book classes.
                                    </p>
                                <?php else: ?>
                                    <a href="login.php" class="btn-outline btn-block text-center login-book-btn">
                                        Login to Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-classes-message">
                        No classes scheduled at the moment.
                    </p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<div
    id="schedule-config"
    data-is-logged-in="<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
    data-user-role="<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>"
    data-csrf-token="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
    hidden
></div>

<script src="js/schedule.js"></script>

<?php include 'templates/footer.php'; ?>