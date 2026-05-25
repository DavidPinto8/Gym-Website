<?php

session_start();

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    header('Location: ../register.php?error=Invalid security token.');
    exit();
}

require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $plan_id = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
    
    if ($name === '' || $username === '' || $email === '' || $password === '' || $plan_id === 0) {
        header('Location: ../register.php?error=empty_fields');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../register.php?error=invalid_email');
        exit();
    }

    if (strlen($password) < 6) {
        header('Location: ../register.php?error=short_password');
        exit();
    }

    $db = getDatabaseConnection();

    $stmt_check = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt_check->execute([$username, $email]);
    if ($stmt_check->fetch()) {
        header('Location: ../register.php?error=user_exists');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $db->beginTransaction();

        $stmt_user = $db->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, 'member')");
        $stmt_user->execute([$name, $username, $email, $hashed_password]);
        
        $user_id = $db->lastInsertId();

        $next_billing = date('Y-m-d', strtotime('+1 month'));

        $stmt_member = $db->prepare("INSERT INTO members (user_id, plan_id, streak, next_billing_date) VALUES (?, ?, 0, ?)");
        $stmt_member->execute([$user_id, $plan_id, $next_billing]);

        $db->commit();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $name;
        $_SESSION['role'] = 'member';

        header('Location: ../profile.php?new_user=true');
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        header('Location: ../register.php?error=db_fail');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
?>