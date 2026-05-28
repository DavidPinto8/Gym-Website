<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../register.php');
require_valid_csrf_token('../register.php');

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$cardLast4 = trim($_POST['card_last4'] ?? '');

$planId = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);

$validPaymentMethods = [
    'visa',
    'mastercard',
    'paypal',
    'apple_pay',
    'google_pay'
];

if (
    $name === '' ||
    $username === '' ||
    $email === '' ||
    $password === '' ||
    $planId === false ||
    $planId === null ||
    $planId <= 0 ||
    $paymentMethod === '' ||
    $cardLast4 === ''
) {
    action_redirect('../register.php', [
        'error' => 'empty_fields'
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    action_redirect('../register.php', [
        'error' => 'invalid_email'
    ]);
}

if (strlen($password) < 6) {
    action_redirect('../register.php', [
        'error' => 'short_password'
    ]);
}

if (
    !in_array($paymentMethod, $validPaymentMethods, true) ||
    !preg_match('/^[0-9]{4}$/', $cardLast4)
) {
    action_redirect('../register.php', [
        'error' => 'invalid_payment'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT id
        FROM users
        WHERE username = ? OR email = ?
    ");

    $stmt->execute([
        $username,
        $email
    ]);

    if ($stmt->fetch()) {
        action_redirect('../register.php', [
            'error' => 'user_exists'
        ]);
    }

    $stmt = $db->prepare("
        SELECT id
        FROM plans
        WHERE id = ?
    ");

    $stmt->execute([$planId]);

    if (!$stmt->fetch()) {
        action_redirect('../register.php', [
            'error' => 'invalid_plan'
        ]);
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($hashedPassword === false) {
        action_redirect('../register.php', [
            'error' => 'db_fail'
        ]);
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO users (
            name,
            username,
            email,
            password,
            role
        )
        VALUES (?, ?, ?, ?, 'member')
    ");

    $stmt->execute([
        $name,
        $username,
        $email,
        $hashedPassword
    ]);

    $userId = (int) $db->lastInsertId();
    $nextBillingDate = date('Y-m-d', strtotime('+1 month'));

    $stmt = $db->prepare("
        INSERT INTO members (
            user_id,
            plan_id,
            streak,
            next_billing_date,
            payment_method,
            card_last4
        )
        VALUES (?, ?, 0, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $planId,
        $nextBillingDate,
        $paymentMethod,
        $cardLast4
    ]);

    $db->commit();

    session_regenerate_id(true);

    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = 'member';
    $_SESSION['photo'] = null;

    action_redirect('../profile.php', [
        'new_user' => 'true'
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../register.php', [
        'error' => 'db_fail'
    ]);
}