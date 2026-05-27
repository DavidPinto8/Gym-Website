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
$trainerId = filter_input(INPUT_POST, 'trainer_id', FILTER_VALIDATE_INT);
$capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
$durationMin = filter_input(INPUT_POST, 'duration_min', FILTER_VALIDATE_INT);
$startTime = trim($_POST['start_time'] ?? '');
$location = trim($_POST['location'] ?? '');

$validTypes = ['yoga', 'hiit', 'strength', 'cardio', 'other'];

if (
    $name === '' ||
    !in_array($type, $validTypes, true) ||
    !$capacity ||
    $capacity < 1 ||
    !$durationMin ||
    $durationMin < 15 ||
    $startTime === '' ||
    $location === ''
) {
    header('Location: ../admin.php?error=invalid_class_input');
    exit();
}

$timestamp = strtotime($startTime);

if ($timestamp === false) {
    header('Location: ../admin.php?error=invalid_class_date');
    exit();
}

$formattedStartTime = date('Y-m-d\TH:i:s', $timestamp);
$db = getDatabaseConnection();

try {
    if ($trainerId) {
        $stmt = $db->prepare("SELECT id FROM trainers WHERE id = ?");
        $stmt->execute([$trainerId]);

        if (!$stmt->fetch()) {
            header('Location: ../admin.php?error=trainer_not_found');
            exit();
        }
    } else {
        $trainerId = null;
    }

    $stmt = $db->prepare("
        INSERT INTO classes (
            name,
            type,
            trainer_id,
            capacity,
            start_time,
            duration_min,
            location
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $name,
        $type,
        $trainerId,
        $capacity,
        $formattedStartTime,
        $durationMin,
        $location
    ]);

    header('Location: ../admin.php?success=class_created');
    exit();
} catch (Exception $e) {
    header('Location: ../admin.php?error=create_class_failed');
    exit();
}