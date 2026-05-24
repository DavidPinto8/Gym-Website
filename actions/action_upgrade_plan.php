<?php

session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php');
    exit();
}

if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
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
    $stmt = $db->prepare('
        UPDATE members
        SET plan_id = ?
        WHERE user_id = ?
    ');

    $stmt->execute([$planId, $userId]);

    header('Location: ../profile.php?success=plan_updated#membership-details');
    exit();
} catch (Exception $e) {
    header('Location: ../profile.php?error=invalid_plan#membership-details');
    exit();
}