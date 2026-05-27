<?php

session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../profile.php?error=invalid_plan#membership-details');
    exit();
}

$userId = $_SESSION['user_id'];
$planId = (int) ($_POST['plan_id'] ?? 0);

$validPlans = [1, 2, 3];

if (!in_array($planId, $validPlans, true)) {
    header('Location: ../profile.php?error=invalid_plan#membership-details');
    exit();
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT payment_method
        FROM members
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $member = $stmt->fetch();

    if (!$member) {
        header('Location: ../profile.php?error=invalid_plan#membership-details');
        exit();
    }

    if (empty($member['payment_method'])) {
        header('Location: ../billing.php?error=missing_payment_method');
        exit();
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
        $userId
    ]);

    header('Location: ../profile.php?success=plan_updated#membership-details');
    exit();
} catch (Exception $e) {
    header('Location: ../profile.php?error=invalid_plan#membership-details');
    exit();
}