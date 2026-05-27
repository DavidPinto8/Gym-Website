<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin.php');
require_user_role('admin');
require_valid_csrf_token('../admin.php');

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);

if ($classId === false || $classId === null || $classId <= 0) {
    action_redirect('../admin.php', [
        'error' => 'invalid_class'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT id
        FROM classes
        WHERE id = ?
    ");

    $stmt->execute([$classId]);

    if (!$stmt->fetch()) {
        action_redirect('../admin.php', [
            'error' => 'class_not_found'
        ]);
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        DELETE FROM reviews
        WHERE class_id = ?
    ");

    $stmt->execute([$classId]);

    $stmt = $db->prepare("
        DELETE FROM enrollments
        WHERE class_id = ?
    ");

    $stmt->execute([$classId]);

    $stmt = $db->prepare("
        DELETE FROM classes
        WHERE id = ?
    ");

    $stmt->execute([$classId]);

    $db->commit();

    action_redirect('../admin.php', [
        'success' => 'class_deleted'
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../admin.php', [
        'error' => 'delete_class_failed'
    ]);
}