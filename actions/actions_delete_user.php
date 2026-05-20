<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php?error=Invalid request.');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../admin.php?error=Invalid security token.');
    exit();
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

if (!$userId) {
    header('Location: ../admin.php?error=Invalid user.');
    exit();
}

if ($userId === (int)$_SESSION['user_id']) {
    header('Location: ../admin.php?error=You cannot delete your own account.');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: ../admin.php?error=User not found.');
    exit();
}

if ($user['role'] === 'admin') {
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = (int)$stmt->fetchColumn();

    if ($adminCount <= 1) {
        header('Location: ../admin.php?error=You cannot delete the last admin.');
        exit();
    }
}

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$userId]);

header('Location: ../admin.php?success=User deleted successfully.');
exit();