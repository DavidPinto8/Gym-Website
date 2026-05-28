<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'database/db.php';
require_once 'includes/avatar.php';
require_once 'includes/class_occurrence.php';
require_once 'includes/ui_helpers.php';

$db = getDatabaseConnection();

$classId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$classId) {
    header('Location: schedule.php?error=Invalid class.');
    exit();
}

$memberId = null;

if (isset($_SESSION['user_id'])) {
    $stmtMember = $db->prepare("
        SELECT id
        FROM members
        WHERE user_id = ?
    ");
    $stmtMember->execute([$_SESSION['user_id']]);
    $member = $stmtMember->fetch();

    if ($member) {
        $memberId = $member['id'];
    }
}

$stmt = $db->prepare("
    SELECT
        classes.*,
        users.name AS trainer_name,
        users.photo AS trainer_photo,
        trainers.bio AS trainer_bio,
        trainers.specializations,
        trainers.certifications,

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

    WHERE classes.id = :class_id

    GROUP BY classes.id
");

$stmt->execute([
    ':member_id' => $memberId,
    ':class_id' => $classId
]);

$class = $stmt->fetch();

if (!$class) {
    header('Location: schedule.php?error=Class not found.');
    exit();
}

$class['original_start_time'] = $class['start_time'];
$class['start_time'] = getNextWeeklyOccurrence($class['start_time']);

$stmt = $db->prepare("
    SELECT
        reviews.*,
        users.name AS member_name
    FROM reviews

    JOIN members
        ON reviews.member_id = members.id

    JOIN users
        ON members.user_id = users.id

    WHERE reviews.class_id = ?

    ORDER BY reviews.created_at DESC
");

$stmt->execute([$classId]);
$reviews = $stmt->fetchAll();

$userReview = null;

if ($memberId) {
    $stmt = $db->prepare("
        SELECT *
        FROM reviews
        WHERE class_id = ? AND member_id = ?
    ");
    $stmt->execute([$classId, $memberId]);
    $userReview = $stmt->fetch();
}

$timestamp = strtotime($class['start_time']);
$dayFormatted = date('l, F j, Y', $timestamp);
$timeFormatted = date('H:i', $timestamp);
$spotsLeft = (int)$class['capacity'] - (int)$class['enrolled_count'];
$isFull = $spotsLeft <= 0;
$bookingStatus = $class['user_booking_status'];

$classAlreadyHappened = true;
$userAttended = false;

if ($memberId) {
    $stmt = $db->prepare("
        SELECT attended
        FROM enrollments
        WHERE member_id = ? AND class_id = ?
    ");
    $stmt->execute([$memberId, $classId]);
    $attendance = $stmt->fetch();

    $userAttended = $attendance && (int)$attendance['attended'] === 1;
}

$canReview = $memberId && $userAttended && $classAlreadyHappened;

$trainerAvatar = getUserAvatar(
    $class['trainer_name'] ?? 'Trainer',
    $class['trainer_photo'] ?? null,
    220
);

$specializations = array_filter(array_map('trim', explode(',', $class['specializations'] ?? '')));

$pageTitle = htmlspecialchars($class['name']) . " | Flow Fitness";
$currentPage = 'schedule';
$extraCSS = 'class_details.css';

include 'templates/header.php';
?>

<main class="class-details-page">
    <section class="class-hero <?php echo htmlspecialchars($class['type']); ?>">
        <div class="class-hero-overlay"></div>

        <div class="container class-hero-content">
            <span class="class-tag">
                <?php echo ucfirst(htmlspecialchars($class['type'])); ?>
            </span>

            <h1><?php echo htmlspecialchars($class['name']); ?></h1>

            <p>
                <?php echo htmlspecialchars($dayFormatted); ?> •
                <?php echo htmlspecialchars($timeFormatted); ?> •
                <?php echo htmlspecialchars($class['duration_min']); ?> min
            </p>
        </div>
    </section>

    <section class="container class-details-layout">
        <div class="class-main">
            <?php render_alerts(); ?>

            <article class="details-card">
                <h2>About This Class</h2>

                <p>
                    This <?php echo htmlspecialchars($class['type']); ?> session is designed to help members train with focus,
                    rhythm, and intensity. Expect a structured workout led by a qualified trainer, with clear coaching and
                    a strong group atmosphere.
                </p>

                <div class="details-grid">
                    <div>
                        <strong>Trainer</strong>
                        <span><?php echo htmlspecialchars($class['trainer_name'] ?? 'TBA'); ?></span>
                    </div>

                    <div>
                        <strong>Location</strong>
                        <span><?php echo htmlspecialchars($class['location']); ?></span>
                    </div>

                    <div>
                        <strong>Capacity</strong>
                        <span><?php echo htmlspecialchars($class['enrolled_count']); ?>/<?php echo htmlspecialchars($class['capacity']); ?> booked</span>
                    </div>

                    <div>
                        <strong>Rating</strong>
                        <span>
                            <?php if ((int)$class['review_count'] > 0): ?>
                                ★ <?php echo htmlspecialchars($class['average_rating']); ?>/5
                                (<?php echo htmlspecialchars($class['review_count']); ?> reviews)
                            <?php else: ?>
                                No reviews yet
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </article>

            <article class="details-card trainer-detail-card">
                <div class="trainer-detail-layout">
                    <img
                        src="<?php echo htmlspecialchars($trainerAvatar); ?>"
                        alt="<?php echo htmlspecialchars($class['trainer_name'] ?? 'Trainer'); ?>"
                        class="trainer-detail-avatar"
                    >

                    <div>
                        <span class="trainer-detail-kicker">Your Trainer</span>

                        <h2><?php echo htmlspecialchars($class['trainer_name'] ?? 'TBA'); ?></h2>

                        <?php if (!empty($class['trainer_bio'])): ?>
                            <p class="trainer-detail-bio">
                                <?php echo htmlspecialchars($class['trainer_bio']); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (count($specializations) > 0): ?>
                            <div class="trainer-specializations">
                                <?php foreach ($specializations as $specialization): ?>
                                    <span>
                                        <?php echo htmlspecialchars($specialization); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($class['certifications'])): ?>
                            <p class="trainer-certifications">
                                <strong>Certifications:</strong>
                                <?php echo htmlspecialchars($class['certifications']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </article>

            <article class="details-card">
                <h2>Member Reviews</h2>

                <?php if (count($reviews) > 0): ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <article class="review-card">
                                <div class="review-header">
                                    <strong><?php echo htmlspecialchars($review['member_name']); ?></strong>
                                    <span>★ <?php echo htmlspecialchars($review['rating']); ?>/5</span>
                                </div>

                                <?php if (!empty($review['comment'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>

                                <small>
                                    <?php echo htmlspecialchars(date('F j, Y', strtotime($review['created_at']))); ?>
                                </small>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted-text">
                        No reviews yet. Be the first to leave feedback after attending this class.
                    </p>
                <?php endif; ?>
            </article>

            <?php if ($canReview): ?>
                <article class="details-card">
                    <h2><?php echo $userReview ? 'Update Your Review' : 'Leave a Review'; ?></h2>

                    <form action="actions/action_leave_review.php" method="POST" class="review-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">

                        <div class="form-group">
                            <label for="rating">Rating</label>

                            <select id="rating" name="rating" required>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $userReview && (int)$userReview['rating'] === $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> stars
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="comment">Comment</label>

                            <textarea id="comment" name="comment" rows="5" required><?php echo htmlspecialchars($userReview['comment'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-primary">
                            Save Review
                        </button>
                    </form>
                </article>
            <?php elseif (isset($_SESSION['user_id']) && $memberId): ?>
                <article class="details-card">
                    <p class="muted-text">
                        You can review this class after attending it.
                    </p>
                </article>
            <?php endif; ?>
        </div>

        <aside class="booking-panel">
            <h2>Book This Class</h2>

            <p>
                <strong><?php echo htmlspecialchars($spotsLeft); ?></strong>
                spots left
            </p>

            <?php if (isset($_SESSION['user_id']) && $memberId): ?>
                <?php if ($bookingStatus === 'enrolled'): ?>
                    <p class="booking-status success">You are enrolled in this class.</p>

                    <form action="actions/action_cancel_booking.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">

                        <button type="submit" class="btn-outline btn-block cancel-btn">
                            Cancel Booking
                        </button>
                    </form>
                <?php elseif ($bookingStatus === 'waitlist'): ?>
                    <p class="booking-status waitlist">You are currently on the waitlist.</p>

                    <form action="actions/action_cancel_booking.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['id']); ?>">

                        <button type="submit" class="btn-outline btn-block waitlist-btn">
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
                <a href="login.php" class="btn-primary btn-block">
                    Login to Book
                </a>
            <?php endif; ?>

            <a href="schedule.php" class="back-link">
                ← Back to schedule
            </a>
        </aside>
    </section>
</main>

<?php include 'templates/footer.php'; ?>