<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../billing.php');
require_user_role('member');
require_valid_csrf_token('../billing.php');

$paymentMethod = trim($_POST['payment_method'] ?? '');
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
    action_redirect('../billing.php', [
        'error' => 'invalid_input'
    ]);
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

    if ($stmt->rowCount() === 0) {
        $stmt = $db->prepare("
            SELECT id
            FROM members
            WHERE user_id = ?
        ");

        $stmt->execute([$_SESSION['user_id']]);

        if (!$stmt->fetch()) {
            action_redirect('../billing.php', [
                'error' => 'member_not_found'
            ]);
        }
    }

    action_redirect('../billing.php', [
        'success' => 'payment_updated'
    ]);
} catch (Throwable $exception) {
    action_redirect('../billing.php', [
        'error' => 'update_failed'
    ]);
}