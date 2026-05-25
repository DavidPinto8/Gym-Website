<?php

session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../login.php?error=Invalid security token.');
    exit();
}

require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../login.php?error=invalid');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);

$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: ../login.php?error=invalid');
    exit();
}

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['name'] = $user['name'];
$_SESSION['role'] = $user['role'];
$_SESSION['photo'] = $user['photo'] ?? null;

if ($user['role'] === 'admin') {
    header('Location: ../admin.php');
    exit();
}

if ($user['role'] === 'trainer') {
    header('Location: ../trainer_dashboard.php');
    exit();
}

header('Location: ../profile.php');
exit();