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
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comment = trim($_POST['comment'] ?? '');

if (!$classId || !$rating || $rating < 1 || $rating > 5 || $comment === '') {
    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=Please provide a valid rating and comment.');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT id
    FROM members
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=Only members can leave reviews.');
    exit();
}

$memberId = $member['id'];

$stmt = $db->prepare("
    SELECT start_time
    FROM classes
    WHERE id = ?
");
$stmt->execute([$classId]);
$class = $stmt->fetch();

if (!$class) {
    header('Location: ../schedule.php?error=Class not found.');
    exit();
}

if (strtotime($class['start_time']) > time()) {
    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=You can only review classes after they happen.');
    exit();
}

$stmt = $db->prepare("
    SELECT status, attended
    FROM enrollments
    WHERE member_id = ? AND class_id = ?
");
$stmt->execute([$memberId, $classId]);
$enrollment = $stmt->fetch();

if (!$enrollment || (int)$enrollment['attended'] !== 1) {
    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=You can only review classes you attended.');
    exit();
}

$stmt = $db->prepare("
    SELECT id
    FROM reviews
    WHERE member_id = ? AND class_id = ?
");
$stmt->execute([$memberId, $classId]);
$existingReview = $stmt->fetch();

if ($existingReview) {
    $stmt = $db->prepare("
        UPDATE reviews
        SET rating = ?, comment = ?, created_at = datetime('now')
        WHERE id = ?
    ");
    $stmt->execute([$rating, $comment, $existingReview['id']]);
} else {
    $stmt = $db->prepare("
        INSERT INTO reviews (member_id, class_id, rating, comment)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$memberId, $classId, $rating, $comment]);
}

header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&success=Review saved successfully.');
exit();