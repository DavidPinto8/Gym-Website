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

$equipmentId = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
$availableUnits = filter_input(INPUT_POST, 'available_units', FILTER_VALIDATE_INT);

if (!$equipmentId || $availableUnits === false || $availableUnits < 0) {
    header('Location: ../admin.php?error=invalid_input');
    exit();
}

$db = getDatabaseConnection();


try{
    $stmt = $db->prepare("SELECT total_units FROM equipment WHERE id = ?");
    $stmt->execute([$equipmentId]);
    $equipment = $stmt->fetch();

    if (!$equipment) {
        header('Location: ../admin.php?error=equipment_not_found');
        exit();
    }

    if ($availableUnits > (int)$equipment['total_units']) {
        header('Location: ../admin.php?error=units_exceeded');
        exit();
    }

    $stmt = $db->prepare("UPDATE equipment SET available_units = ? WHERE id = ?");
    $stmt->execute([$availableUnits, $equipmentId]);

    header('Location: ../admin.php?success=equipment_updated');
    exit();
} catch(Exception $e){
    header('Location: ../admin.php?error=update_failed');
    exit();
}