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
    header('Location: ../schedule.php?error=Only members can book classes.');
    exit();
}

$memberId = $member['id'];

$stmt = $db->prepare("SELECT capacity FROM classes WHERE id = ?");
$stmt->execute([$classId]);
$class = $stmt->fetch();

if (!$class) {
    header('Location: ../schedule.php?error=Class not found.');
    exit();
}

$stmt = $db->prepare("
    SELECT status 
    FROM enrollments 
    WHERE member_id = ? AND class_id = ?
");
$stmt->execute([$memberId, $classId]);
$existingEnrollment = $stmt->fetch();

if ($existingEnrollment && in_array($existingEnrollment['status'], ['enrolled', 'waitlist'])) {
    header('Location: ../schedule.php?error=You already have a booking for this class.');
    exit();
}

$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM enrollments 
    WHERE class_id = ? AND status = 'enrolled'
");
$stmt->execute([$classId]);
$enrolledCount = (int)$stmt->fetchColumn();

$newStatus = $enrolledCount < (int)$class['capacity'] ? 'enrolled' : 'waitlist';

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

header('Location: ../schedule.php?success=' . urlencode($message));
exit();