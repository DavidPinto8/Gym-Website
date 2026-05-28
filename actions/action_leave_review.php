<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../schedule.php');
require_authenticated_user();
require_valid_csrf_token('../schedule.php');

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$comment = trim($_POST['comment'] ?? '');

if ($classId === false || $classId === null || $classId <= 0) {
    action_redirect('../schedule.php', [
        'error' => 'invalid_class'
    ]);
}

$classDetailsPath = '../class_details.php';

if (
    $rating === false ||
    $rating === null ||
    $rating < 1 ||
    $rating > 5 ||
    $comment === ''
) {
    action_redirect($classDetailsPath, [
        'id' => $classId,
        'error' => 'invalid_input'
    ]);
}

$db = getDatabaseConnection();

try {
    $member = get_current_member($db);

    if (!$member) {
        action_redirect($classDetailsPath, [
            'id' => $classId,
            'error' => 'not_a_member'
        ]);
    }

    $memberId = (int) $member['id'];

    $stmt = $db->prepare("
        SELECT start_time, duration_min
        FROM classes
        WHERE id = ?
    ");

    $stmt->execute([$classId]);

    $class = $stmt->fetch();

    if (!$class) {
        action_redirect('../schedule.php', [
            'error' => 'class_not_found'
        ]);
    }

    $classStartTime = strtotime($class['start_time']);

    if ($classStartTime === false) {
        action_redirect($classDetailsPath, [
            'id' => $classId,
            'error' => 'invalid_class_date'
        ]);
    }

    $classEndTime = $classStartTime + ((int) $class['duration_min'] * 60);

    if ($classEndTime > time()) {
        action_redirect($classDetailsPath, [
            'id' => $classId,
            'error' => 'class_not_ended'
        ]);
    }

    $stmt = $db->prepare("
        SELECT attended
        FROM enrollments
        WHERE member_id = ? AND class_id = ?
    ");

    $stmt->execute([$memberId, $classId]);

    $enrollment = $stmt->fetch();

    if (!$enrollment || (int) $enrollment['attended'] !== 1) {
        action_redirect($classDetailsPath, [
            'id' => $classId,
            'error' => 'no_attendance'
        ]);
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

        $stmt->execute([
            $rating,
            $comment,
            $existingReview['id']
        ]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO reviews (
                member_id,
                class_id,
                rating,
                comment
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $memberId,
            $classId,
            $rating,
            $comment
        ]);
    }

    action_redirect($classDetailsPath, [
        'id' => $classId,
        'success' => 'review_saved'
    ]);
} catch (Throwable $exception) {
    action_redirect($classDetailsPath, [
        'id' => $classId,
        'error' => 'update_failed'
    ]);
}