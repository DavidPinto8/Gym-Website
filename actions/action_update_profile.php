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

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../profile.php?error=invalid_csrf#account-settings');
    exit();
}

$userId = $_SESSION['user_id'];

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$favoriteFlow = $_POST['fav-class'] ?? '';
$primaryGoal = $_POST['fitness-goal'] ?? '';

$validFavoriteFlows = ['hiit', 'mobility', 'strength'];
$validPrimaryGoals = ['endurance', 'muscle', 'weightloss'];

if ($name === '' || $username === '') {
    header('Location: ../profile.php?error=empty_fields#account-settings');
    exit();
}

if (!in_array($favoriteFlow, $validFavoriteFlows, true)) {
    $favoriteFlow = '';
}

if (!in_array($primaryGoal, $validPrimaryGoals, true)) {
    $primaryGoal = '';
}

$db = getDatabaseConnection();

$stmt = $db->prepare('
    SELECT id
    FROM users
    WHERE username = ? AND id != ?
');
$stmt->execute([$username, $userId]);

if ($stmt->fetch()) {
    header('Location: ../profile.php?error=username_taken#account-settings');
    exit();
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare('
        UPDATE users
        SET name = ?, username = ?
        WHERE id = ?
    ');
    $stmt->execute([$name, $username, $userId]);

    $stmt = $db->prepare('
        UPDATE members
        SET favorite_flow = ?, primary_goal = ?
        WHERE user_id = ?
    ');
    $stmt->execute([$favoriteFlow, $primaryGoal, $userId]);

    $_SESSION['name'] = $name;
    $_SESSION['username'] = $username;

    $db->commit();

    header('Location: ../profile.php?success=profile_updated#account-settings');
    exit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    header('Location: ../profile.php?error=update_failed#account-settings');
    exit();
}