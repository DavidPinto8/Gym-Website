<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin.php');
require_user_role('admin');
require_valid_csrf_token('../admin.php');

$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
$availableUnits = filter_input(INPUT_POST, 'available_units', FILTER_VALIDATE_INT);

if (
    $equipmentId === false ||
    $equipmentId === null ||
    $equipmentId <= 0 ||
    $availableUnits === false ||
    $availableUnits === null ||
    $availableUnits < 0
) {
    action_redirect('../admin.php', [
        'error' => 'invalid_input'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT total_units
        FROM equipment
        WHERE id = ?
    ");

    $stmt->execute([$equipmentId]);

    $equipment = $stmt->fetch();

    if (!$equipment) {
        action_redirect('../admin.php', [
            'error' => 'equipment_not_found'
        ]);
    }

    if ($availableUnits > (int) $equipment['total_units']) {
        action_redirect('../admin.php', [
            'error' => 'units_exceeded'
        ]);
    }

    $stmt = $db->prepare("
        UPDATE equipment
        SET available_units = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $availableUnits,
        $equipmentId
    ]);

    action_redirect('../admin.php', [
        'success' => 'equipment_updated'
    ]);
} catch (Throwable $exception) {
    action_redirect('../admin.php', [
        'error' => 'update_failed'
    ]);
}