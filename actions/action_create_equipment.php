<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin.php');
require_user_role('admin');
require_valid_csrf_token('../admin.php');

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$location = trim($_POST['location'] ?? '');

$totalUnits = filter_input(INPUT_POST, 'total_units', FILTER_VALIDATE_INT);
$availableUnits = filter_input(INPUT_POST, 'available_units', FILTER_VALIDATE_INT);

if (
    $name === '' ||
    $type === '' ||
    $location === '' ||
    $totalUnits === false ||
    $totalUnits === null ||
    $totalUnits < 1 ||
    $availableUnits === false ||
    $availableUnits === null ||
    $availableUnits < 0 ||
    $availableUnits > $totalUnits
) {
    action_redirect('../admin.php', [
        'error' => 'invalid_equipment_input'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        INSERT INTO equipment (
            name,
            type,
            location,
            total_units,
            available_units
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $type,
        $location,
        $totalUnits,
        $availableUnits
    ]);

    action_redirect('../admin.php', [
        'success' => 'equipment_created'
    ]);
} catch (Throwable $exception) {
    action_redirect('../admin.php', [
        'error' => 'create_equipment_failed'
    ]);
}