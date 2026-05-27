<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php?error=invalid_request');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../admin.php?error=invalid_csrf');
    exit();
}

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$location = trim($_POST['location'] ?? '');
$totalUnits = filter_input(INPUT_POST, 'total_units', FILTER_VALIDATE_INT);
$availableUnits = filter_input(INPUT_POST, 'available_units', FILTER_VALIDATE_INT);

if (
    $name === '' ||
    $type === '' ||
    $location === '' ||
    !$totalUnits ||
    $totalUnits < 1 ||
    $availableUnits === false ||
    $availableUnits < 0 ||
    $availableUnits > $totalUnits
) {
    header('Location: ../admin.php?error=invalid_equipment_input');
    exit();
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

    header('Location: ../admin.php?success=equipment_created');
    exit();
} catch (Exception $e) {
    header('Location: ../admin.php?error=create_equipment_failed');
    exit();
}