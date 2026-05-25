<?php

session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../register.php?error=Invalid security token.');
    exit();
}

require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit();
}

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;

if ($name === '' || $username === '' || $email === '' || $password === '' || $planId === 0) {
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

$stmtCheck = $db->prepare("
    SELECT id
    FROM users
    WHERE username = ? OR email = ?
");
$stmtCheck->execute([$username, $email]);

if ($stmtCheck->fetch()) {
    header('Location: ../register.php?error=user_exists');
    exit();
}

$stmtPlan = $db->prepare("
    SELECT id
    FROM plans
    WHERE id = ?
");
$stmtPlan->execute([$planId]);

if (!$stmtPlan->fetch()) {
    header('Location: ../register.php?error=invalid_plan');
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $db->beginTransaction();

    $stmtUser = $db->prepare("
        INSERT INTO users (name, username, email, password, role)
        VALUES (?, ?, ?, ?, 'member')
    ");
    $stmtUser->execute([$name, $username, $email, $hashedPassword]);

    $userId = $db->lastInsertId();
    $nextBilling = date('Y-m-d', strtotime('+1 month'));

    $stmtMember = $db->prepare("
        INSERT INTO members (user_id, plan_id, streak, next_billing_date)
        VALUES (?, ?, 0, ?)
    ");
    $stmtMember->execute([$userId, $planId, $nextBilling]);

    $db->commit();

    session_regenerate_id(true);

    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = 'member';
    $_SESSION['photo'] = null;

    header('Location: ../profile.php?new_user=true');
    exit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    header('Location: ../register.php?error=db_fail');
    exit();
}