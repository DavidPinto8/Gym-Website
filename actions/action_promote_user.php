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
    header('Location: ../admin.php?error=You are already an admin.');
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
    header('Location: ../admin.php?error=User is already an admin.');
    exit();
}

$stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
$stmt->execute([$userId]);

header('Location: ../admin.php?success=User promoted to admin.');
exit();