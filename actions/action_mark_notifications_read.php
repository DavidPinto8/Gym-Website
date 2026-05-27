<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../notifications.php');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../notifications.php?error=invalid_csrf');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);

header('Location: ../notifications.php?success=notifications_read');
exit();