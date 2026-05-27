<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin.php');
require_user_role('admin');
require_valid_csrf_token('../admin.php');

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
$durationMin = filter_input(INPUT_POST, 'duration_min', FILTER_VALIDATE_INT);
$startTime = trim($_POST['start_time'] ?? '');
$location = trim($_POST['location'] ?? '');

$trainerIdInput = trim($_POST['trainer_id'] ?? '');
$trainerId = null;

$validTypes = ['yoga', 'hiit', 'strength', 'cardio', 'other'];

if ($trainerIdInput !== '') {
    $validatedTrainerId = filter_var($trainerIdInput, FILTER_VALIDATE_INT);

    if ($validatedTrainerId === false || $validatedTrainerId <= 0) {
        action_redirect('../admin.php', [
            'error' => 'invalid_class_input'
        ]);
    }

    $trainerId = $validatedTrainerId;
}

if (
    $name === '' ||
    !in_array($type, $validTypes, true) ||
    $capacity === false ||
    $capacity === null ||
    $capacity < 1 ||
    $durationMin === false ||
    $durationMin === null ||
    $durationMin < 15 ||
    $startTime === '' ||
    $location === ''
) {
    action_redirect('../admin.php', [
        'error' => 'invalid_class_input'
    ]);
}

$date = DateTime::createFromFormat('Y-m-d\TH:i', $startTime);

if (!$date || $date->format('Y-m-d\TH:i') !== $startTime) {
    $date = DateTime::createFromFormat('Y-m-d\TH:i:s', $startTime);
}

if (
    !$date ||
    !in_array(
        $date->format(strlen($startTime) === 16 ? 'Y-m-d\TH:i' : 'Y-m-d\TH:i:s'),
        [$startTime],
        true
    )
) {
    action_redirect('../admin.php', [
        'error' => 'invalid_class_date'
    ]);
}

$formattedStartTime = $date->format('Y-m-d\TH:i:s');

$db = getDatabaseConnection();

try {
    if ($trainerId !== null) {
        $stmt = $db->prepare("
            SELECT id
            FROM trainers
            WHERE id = ?
        ");

        $stmt->execute([$trainerId]);

        if (!$stmt->fetch()) {
            action_redirect('../admin.php', [
                'error' => 'trainer_not_found'
            ]);
        }
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

    action_redirect('../admin.php', [
        'success' => 'class_created'
    ]);
} catch (Throwable $exception) {
    action_redirect('../admin.php', [
        'error' => 'create_class_failed'
    ]);
}