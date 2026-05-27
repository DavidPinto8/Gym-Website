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
        'error' => 'You are already an admin.'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT role
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$userId]);

    $user = $stmt->fetch();

    if (!$user) {
        action_redirect('../admin.php', [
            'error' => 'User not found.'
        ]);
    }

    if ($user['role'] === 'admin') {
        action_redirect('../admin.php', [
            'error' => 'User is already an admin.'
        ]);
    }

    $stmt = $db->prepare("
        UPDATE users
        SET role = 'admin'
        WHERE id = ?
    ");

    $stmt->execute([$userId]);

    action_redirect('../admin.php', [
        'success' => 'User promoted to admin.'
    ]);
} catch (Throwable $exception) {
    action_redirect('../admin.php', [
        'error' => 'Failed to promote user.'
    ]);
}