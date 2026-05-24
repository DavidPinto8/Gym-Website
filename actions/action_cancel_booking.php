<?php
session_start();
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../schedule.php?error=Invalid CSRF security token.');
    exit();
}
require_once '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../schedule.php?error=Invalid request.');
    exit();
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);

if (!$classId) {
    header('Location: ../schedule.php?error=Invalid class.');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("SELECT id FROM members WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: ../schedule.php?error=Only members can cancel bookings.');
    exit();
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
    header('Location: ../schedule.php?error=No active booking found.');
    exit();
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

header('Location: ../schedule.php?success=Booking cancelled successfully.');
exit();