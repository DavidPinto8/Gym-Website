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

if (!$equipmentId) {
    header('Location: ../admin.php?error=invalid_equipment');
    exit();
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("SELECT id FROM equipment WHERE id = ?");
    $stmt->execute([$equipmentId]);

    if (!$stmt->fetch()) {
        header('Location: ../admin.php?error=equipment_not_found');
        exit();
    }

    $stmt = $db->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$equipmentId]);

    header('Location: ../admin.php?success=equipment_deleted');
    exit();
} catch (Exception $e) {
    header('Location: ../admin.php?error=delete_equipment_failed');
    exit();
}