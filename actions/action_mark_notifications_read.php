<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../notifications.php');
require_authenticated_user();
require_valid_csrf_token('../notifications.php');

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ? AND is_read = 0
    ");

    $stmt->execute([$_SESSION['user_id']]);

    action_redirect('../notifications.php', [
        'success' => 'notifications_read'
    ]);
} catch (Throwable $exception) {
    action_redirect('../notifications.php', [
        'error' => 'notifications_update_failed'
    ]);
}