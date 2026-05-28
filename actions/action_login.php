<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../login.php');
require_valid_csrf_token('../login.php');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    action_redirect('../login.php', [
        'error' => 'invalid'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT id, username, name, role, photo, password
        FROM users
        WHERE username = ?
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        action_redirect('../login.php', [
            'error' => 'invalid'
        ]);
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['photo'] = $user['photo'] ?? null;

    if ($user['role'] === 'admin') {
        action_redirect('../admin.php');
    }

    if ($user['role'] === 'trainer') {
        action_redirect('../trainer_dashboard.php');
    }

    action_redirect('../profile.php');
} catch (Throwable $exception) {
    action_redirect('../login.php', [
        'error' => 'login_failed'
    ]);
}