<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT *
    FROM notifications
    WHERE user_id = ?
    ORDER BY is_read ASC, created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

$pageTitle = "Notifications | Flow Fitness";
$currentPage = "notifications";
$extraCSS = "reports.css";

include 'templates/header.php';
?>

<main class="reports-page">
    <section class="container reports-layout">
        <div class="reports-main">
            <div class="section-header notifications-header">
                <div>
                    <h1>Notifications</h1>
                    <p>Recent updates about reports and support responses.</p>
                </div>

                <?php if (count($notifications) > 0): ?>
                    <form action="actions/action_mark_notifications_read.php" method="POST">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                        >

                        <button type="submit" class="btn-outline">
                            Mark all as read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="schedule-alert success-alert">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $_GET['success'])); ?>
                </div>
            <?php endif; ?>

            <div class="reports-list">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <article class="report-item notification-item <?php echo (int)$notification['is_read'] === 0 ? 'notification-unread' : ''; ?>">
                            <div class="report-item-header">
                                <div>
                                    <span class="report-category">
                                        <?php echo (int)$notification['is_read'] === 0 ? 'New' : 'Read'; ?>
                                    </span>

                                    <h2><?php echo htmlspecialchars($notification['message']); ?></h2>
                                </div>

                                <span class="muted-text">
                                    <?php echo htmlspecialchars(date('F j, Y H:i', strtotime($notification['created_at']))); ?>
                                </span>
                            </div>

                            <?php if (!empty($notification['link'])): ?>
                                <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="btn-primary">
                                    Open
                                </a>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="report-card">
                        <p class="muted-text">
                            You have no notifications yet.
                        </p>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'templates/footer.php'; ?>