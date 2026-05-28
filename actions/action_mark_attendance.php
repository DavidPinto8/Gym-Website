<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../trainer_dashboard.php');
require_user_role('trainer');
require_valid_csrf_token('../trainer_dashboard.php');

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
$attendedMembers = $_POST['attended_members'] ?? [];

if ($classId === false || $classId === null || $classId <= 0) {
    action_redirect('../trainer_dashboard.php', [
        'error' => 'Invalid class.'
    ]);
}

if (!is_array($attendedMembers)) {
    action_redirect('../trainer_dashboard.php', [
        'error' => 'Invalid attendance data.'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT id
        FROM trainers
        WHERE user_id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $trainer = $stmt->fetch();

    if (!$trainer) {
        action_redirect('../trainer_dashboard.php', [
            'error' => 'Trainer profile not found.'
        ]);
    }

    $trainerId = (int) $trainer['id'];

    $stmt = $db->prepare("
        SELECT id
        FROM classes
        WHERE id = ? AND trainer_id = ?
    ");

    $stmt->execute([$classId, $trainerId]);

    if (!$stmt->fetch()) {
        action_redirect('../trainer_dashboard.php', [
            'error' => 'You can only manage attendance for your own classes.'
        ]);
    }

    $validAttendedMemberIds = [];

    foreach ($attendedMembers as $memberId) {
        if (is_array($memberId)) {
            continue;
        }

        $validatedMemberId = filter_var($memberId, FILTER_VALIDATE_INT);

        if ($validatedMemberId !== false && $validatedMemberId > 0) {
            $validAttendedMemberIds[] = $validatedMemberId;
        }
    }

    $validAttendedMemberIds = array_unique($validAttendedMemberIds);

    $db->beginTransaction();

    $stmt = $db->prepare("
        UPDATE enrollments
        SET attended = 0
        WHERE class_id = ? AND status = 'enrolled'
    ");

    $stmt->execute([$classId]);

    if (!empty($validAttendedMemberIds)) {
        $stmt = $db->prepare("
            UPDATE enrollments
            SET attended = 1
            WHERE class_id = ? AND member_id = ? AND status = 'enrolled'
        ");

        foreach ($validAttendedMemberIds as $memberId) {
            $stmt->execute([$classId, $memberId]);
        }
    }

    $db->commit();

    action_redirect('../trainer_dashboard.php', [
        'success' => 'Attendance updated successfully.'
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../trainer_dashboard.php', [
        'error' => 'Failed to update attendance.'
    ]);
}