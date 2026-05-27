<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin.php');
require_user_role('admin');
require_valid_csrf_token('../admin.php');

$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);

if ($equipmentId === false || $equipmentId === null || $equipmentId <= 0) {
    action_redirect('../admin.php', [
        'error' => 'invalid_equipment'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT id
        FROM equipment
        WHERE id = ?
    ");

    $stmt->execute([$equipmentId]);

    if (!$stmt->fetch()) {
        action_redirect('../admin.php', [
            'error' => 'equipment_not_found'
        ]);
    }

    $stmt = $db->prepare("
        DELETE FROM equipment
        WHERE id = ?
    ");

    $stmt->execute([$equipmentId]);

    action_redirect('../admin.php', [
        'success' => 'equipment_deleted'
    ]);
} catch (Throwable $exception) {
    action_redirect('../admin.php', [
        'error' => 'delete_equipment_failed'
    ]);
}