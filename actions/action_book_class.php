<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../schedule.php');
require_authenticated_user();
require_valid_csrf_token('../schedule.php');

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);

if ($classId === false || $classId === null || $classId <= 0) {
    action_redirect('../schedule.php', [
        'error' => 'Invalid class.'
    ]);
}

$db = getDatabaseConnection();

$member = get_current_member($db);

if (!$member) {
    action_redirect('../schedule.php', [
        'error' => 'Only members can book classes.'
    ]);
}

$memberId = $member['id'];

$stmt = $db->prepare("
    SELECT capacity
    FROM classes
    WHERE id = ?
");

$stmt->execute([$classId]);

$class = $stmt->fetch();

if (!$class) {
    action_redirect('../schedule.php', [
        'error' => 'Class not found.'
    ]);
}

$stmt = $db->prepare("
    SELECT status
    FROM enrollments
    WHERE member_id = ? AND class_id = ?
");

$stmt->execute([$memberId, $classId]);

$existingEnrollment = $stmt->fetch();

if (
    $existingEnrollment &&
    in_array($existingEnrollment['status'], ['enrolled', 'waitlist'], true)
) {
    action_redirect('../schedule.php', [
        'error' => 'You already have a booking for this class.'
    ]);
}

$stmt = $db->prepare("
    SELECT COUNT(*)
    FROM enrollments
    WHERE class_id = ? AND status = 'enrolled'
");

$stmt->execute([$classId]);

$enrolledCount = (int) $stmt->fetchColumn();
$capacity = (int) $class['capacity'];

$newStatus = $enrolledCount < $capacity ? 'enrolled' : 'waitlist';

if ($existingEnrollment && $existingEnrollment['status'] === 'cancelled') {
    $stmt = $db->prepare("
        UPDATE enrollments
        SET status = ?, enrolled_at = datetime('now')
        WHERE member_id = ? AND class_id = ?
    ");

    $stmt->execute([$newStatus, $memberId, $classId]);
} else {
    $stmt = $db->prepare("
        INSERT INTO enrollments (member_id, class_id, status)
        VALUES (?, ?, ?)
    ");

    $stmt->execute([$memberId, $classId, $newStatus]);
}

$message = $newStatus === 'enrolled'
    ? 'Class booked successfully.'
    : 'Class is full. You were added to the waitlist.';

action_redirect('../schedule.php', [
    'success' => $message
]);