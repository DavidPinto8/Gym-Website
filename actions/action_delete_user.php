<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin.php');
require_user_role('admin');
require_valid_csrf_token('../admin.php');

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

if ($userId === false || $userId === null || $userId <= 0) {
    action_redirect('../admin.php', [
        'error' => 'Invalid user.'
    ]);
}

if ($userId === (int) $_SESSION['user_id']) {
    action_redirect('../admin.php', [
        'error' => 'You cannot delete your own account.'
    ]);
}

$db = getDatabaseConnection();

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        SELECT role
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch();

    if (!$user) {
        $db->rollBack();

        action_redirect('../admin.php', [
            'error' => 'User not found.'
        ]);
    }

    if ($user['role'] === 'admin') {
        $stmt = $db->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'admin'
        ");

        $adminCount = (int) $stmt->fetchColumn();

        if ($adminCount <= 1) {
            $db->rollBack();

            action_redirect('../admin.php', [
                'error' => 'You cannot delete the last admin.'
            ]);
        }
    }

    $stmt = $db->prepare("
        DELETE FROM users
        WHERE id = ?
    ");

    $stmt->execute([$userId]);

    $db->commit();

    action_redirect('../admin.php', [
        'success' => 'User deleted successfully.'
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../admin.php', [
        'error' => 'Failed to delete user.'
    ]);
}