<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php?error=invalid_request');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../admin.php?error=invalid_csrf');
    exit();
}

$classId = filter_input(INPUT_POST, 'class_id', FILTER_VALIDATE_INT);

if (!$classId) {
    header('Location: ../admin.php?error=invalid_class');
    exit();
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("SELECT id FROM classes WHERE id = ?");
    $stmt->execute([$classId]);

    if (!$stmt->fetch()) {
        header('Location: ../admin.php?error=class_not_found');
        exit();
    }

    $db->beginTransaction();

    $stmt = $db->prepare("DELETE FROM reviews WHERE class_id = ?");
    $stmt->execute([$classId]);

    $stmt = $db->prepare("DELETE FROM enrollments WHERE class_id = ?");
    $stmt->execute([$classId]);

    $stmt = $db->prepare("DELETE FROM classes WHERE id = ?");
    $stmt->execute([$classId]);

    $db->commit();

    header('Location: ../admin.php?success=class_deleted');
    exit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    header('Location: ../admin.php?error=delete_class_failed');
    exit();
}