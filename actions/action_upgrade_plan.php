<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../profile.php');
require_user_role('member');
require_valid_csrf_token('../profile.php?error=invalid_plan#membership-details');

$planId = filter_input(INPUT_POST, 'plan_id', FILTER_VALIDATE_INT);

if ($planId === false || $planId === null || $planId <= 0) {
    action_redirect('../profile.php?error=invalid_plan#membership-details');
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT id
        FROM plans
        WHERE id = ?
    ");

    $stmt->execute([$planId]);

    if (!$stmt->fetch()) {
        action_redirect('../profile.php?error=invalid_plan#membership-details');
    }

    $stmt = $db->prepare("
        SELECT payment_method
        FROM members
        WHERE user_id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $member = $stmt->fetch();

    if (!$member) {
        action_redirect('../profile.php?error=invalid_plan#membership-details');
    }

    if (empty($member['payment_method'])) {
        action_redirect('../billing.php', [
            'error' => 'missing_payment_method'
        ]);
    }

    $nextBillingDate = date('Y-m-d', strtotime('+1 month'));

    $stmt = $db->prepare("
        UPDATE members
        SET plan_id = ?,
            next_billing_date = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $planId,
        $nextBillingDate,
        $_SESSION['user_id']
    ]);

    action_redirect('../profile.php?success=plan_updated#membership-details');
} catch (Throwable $exception) {
    action_redirect('../profile.php?error=invalid_plan#membership-details');
}