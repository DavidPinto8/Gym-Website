<?php
session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../trainer_dashboard.php?error=Invalid CSRF security token.');
    exit();
}

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'trainer') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../trainer_dashboard.php?error=Invalid request.');
    exit();
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);
$attendedMembers = $_POST['attended_members'] ?? [];

if (!$classId) {
    header('Location: ../trainer_dashboard.php?error=Invalid class.');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT trainers.id AS trainer_id
    FROM trainers
    WHERE trainers.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$trainer = $stmt->fetch();

if (!$trainer) {
    header('Location: ../trainer_dashboard.php?error=Trainer profile not found.');
    exit();
}

$stmt = $db->prepare("
    SELECT id
    FROM classes
    WHERE id = ? AND trainer_id = ?
");
$stmt->execute([$classId, $trainer['trainer_id']]);
$class = $stmt->fetch();

if (!$class) {
    header('Location: ../trainer_dashboard.php?error=You can only manage attendance for your own classes.');
    exit();
}

$db->beginTransaction();

$stmt = $db->prepare("
    UPDATE enrollments
    SET attended = 0
    WHERE class_id = ? AND status = 'enrolled'
");
$stmt->execute([$classId]);

if (!empty($attendedMembers) && is_array($attendedMembers)) {
    $stmt = $db->prepare("
        UPDATE enrollments
        SET attended = 1
        WHERE class_id = ? AND member_id = ? AND status = 'enrolled'
    ");

    foreach ($attendedMembers as $memberId) {
        $memberId = filter_var($memberId, FILTER_VALIDATE_INT);

        if ($memberId) {
            $stmt->execute([$classId, $memberId]);
        }
    }
}

$db->commit();

header('Location: ../trainer_dashboard.php?success=Attendance updated successfully.');
exit();