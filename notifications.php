<?php

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/ui_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT
        id,
        message,
        link,
        is_read,
        created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY is_read ASC, created_at DESC
");

$stmt->execute([$_SESSION['user_id']]);

$notifications = $stmt->fetchAll();

$allowedNotificationLinks = [
    'reports.php',
    'admin_reports.php'
];

$pageTitle = 'Notifications | Flow Fitness';
$currentPage = 'notifications';
$extraCSS = 'reports.css';

include __DIR__ . '/templates/header.php';
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
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                        >

                        <button type="submit" class="btn-outline">
                            Mark all as read
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <?php render_alerts(); ?>

            <div class="reports-list">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                            $notificationLink = null;

                            if (
                                !empty($notification['link']) &&
                                in_array($notification['link'], $allowedNotificationLinks, true)
                            ) {
                                $notificationLink = $notification['link'];
                            }
                        ?>

                        <article class="report-item notification-item <?php echo (int) $notification['is_read'] === 0 ? 'notification-unread' : ''; ?>">
                            <div class="report-item-header">
                                <div>
                                    <span class="report-category">
                                        <?php echo (int) $notification['is_read'] === 0 ? 'New' : 'Read'; ?>
                                    </span>

                                    <h2>
                                        <?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h2>
                                </div>

                                <span class="muted-text">
                                    <?php echo htmlspecialchars(date('F j, Y H:i', strtotime($notification['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </div>

                            <?php if ($notificationLink !== null): ?>
                                <a
                                    href="<?php echo htmlspecialchars($notificationLink, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="btn-primary"
                                >
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

<?php include __DIR__ . '/templates/footer.php'; ?>