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
        'error' => 'Only members can cancel bookings.'
    ]);
}

$memberId = $member['id'];

$stmt = $db->prepare("
    SELECT status
    FROM enrollments
    WHERE member_id = ? AND class_id = ?
");

$stmt->execute([$memberId, $classId]);

$enrollment = $stmt->fetch();

if (!$enrollment || $enrollment['status'] === 'cancelled') {
    action_redirect('../schedule.php', [
        'error' => 'No active booking found.'
    ]);
}

$wasEnrolled = $enrollment['status'] === 'enrolled';

$stmt = $db->prepare("
    UPDATE enrollments
    SET status = 'cancelled'
    WHERE member_id = ? AND class_id = ?
");

$stmt->execute([$memberId, $classId]);

if ($wasEnrolled) {
    $stmt = $db->prepare("
        SELECT id
        FROM enrollments
        WHERE class_id = ? AND status = 'waitlist'
        ORDER BY enrolled_at ASC
        LIMIT 1
    ");

    $stmt->execute([$classId]);

    $nextWaitlist = $stmt->fetch();

    if ($nextWaitlist) {
        $stmt = $db->prepare("
            UPDATE enrollments
            SET status = 'enrolled'
            WHERE id = ?
        ");

        $stmt->execute([$nextWaitlist['id']]);
    }
}

action_redirect('../schedule.php', [
    'success' => 'Booking cancelled successfully.'
]);