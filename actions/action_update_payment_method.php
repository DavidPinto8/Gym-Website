<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../billing.php');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../billing.php?error=invalid_csrf');
    exit();
}

$paymentMethod = $_POST['payment_method'] ?? '';
$cardLast4 = trim($_POST['card_last4'] ?? '');

$validMethods = [
    'visa',
    'mastercard',
    'paypal',
    'apple_pay',
    'google_pay'
];

if (
    !in_array($paymentMethod, $validMethods, true) ||
    !preg_match('/^[0-9]{4}$/', $cardLast4)
) {
    header('Location: ../billing.php?error=invalid_input');
    exit();
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        UPDATE members
        SET payment_method = ?,
            card_last4 = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $paymentMethod,
        $cardLast4,
        $_SESSION['user_id']
    ]);

    header('Location: ../billing.php?success=payment_updated');
    exit();
} catch (Exception $e) {
    header('Location: ../billing.php?error=update_failed');
    exit();
}