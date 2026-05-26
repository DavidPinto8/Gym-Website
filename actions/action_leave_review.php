<?php
session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../schedule.php?error=invalid_csrf');
    exit();
}

require_once '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../schedule.php?error=invalid_request');
    exit();
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comment = trim($_POST['comment'] ?? '');

if (!$classId || !$rating || $rating < 1 || $rating > 5 || $comment === '') {
    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=invalid_input');
    exit();
}

$db = getDatabaseConnection();

try{
    $stmt = $db->prepare("
        SELECT id
        FROM members
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch();

    if (!$member) {
        header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=not_a_member');
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
        header('Location: ../schedule.php?error=class_not_found');
        exit();
    }

    if (strtotime($class['start_time']) > time()) {
        header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=class_not_ended');
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
        header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=no_attendance');
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

    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&success=review_saved');
    exit();
} catch (Exception $e) {
    header('Location: ../class_details.php?id=' . urlencode((string)$classId) . '&error=update_failed');
    exit();
}