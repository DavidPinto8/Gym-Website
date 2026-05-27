<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../profile.php?error=update_failed');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../profile.php?error=update_failed');
    exit();
}

$formType = $_POST['form_type'] ?? 'profile';
$db = getDatabaseConnection();

if ($formType === 'password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        header('Location: ../profile.php?error=empty_fields#password-settings');
        exit();
    }

    if (strlen($newPassword) < 6) {
        header('Location: ../profile.php?error=short_password#password-settings');
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        header('Location: ../profile.php?error=password_mismatch#password-settings');
        exit();
    }

    $stmt = $db->prepare("
        SELECT password
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        header('Location: ../profile.php?error=invalid_current_password#password-settings');
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $hashedPassword,
            $_SESSION['user_id']
        ]);

        header('Location: ../profile.php?success=password_updated#password-settings');
        exit();
    } catch (Exception $e) {
        header('Location: ../profile.php?error=update_failed#password-settings');
        exit();
    }
}

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$favoriteFlow = $_POST['fav-class'] ?? '';
$primaryGoal = $_POST['fitness-goal'] ?? '';

$validFlows = ['hiit', 'mobility', 'strength'];
$validGoals = ['endurance', 'muscle', 'weightloss'];

if (
    $name === '' ||
    $username === '' ||
    !in_array($favoriteFlow, $validFlows, true) ||
    !in_array($primaryGoal, $validGoals, true)
) {
    header('Location: ../profile.php?error=empty_fields#account-settings');
    exit();
}

try {
    $stmt = $db->prepare("
        SELECT id
        FROM users
        WHERE username = ?
          AND id != ?
    ");
    $stmt->execute([
        $username,
        $_SESSION['user_id']
    ]);

    if ($stmt->fetch()) {
        header('Location: ../profile.php?error=username_taken#account-settings');
        exit();
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        UPDATE users
        SET name = ?,
            username = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $name,
        $username,
        $_SESSION['user_id']
    ]);

    $stmt = $db->prepare("
        UPDATE members
        SET favorite_flow = ?,
            primary_goal = ?
        WHERE user_id = ?
    ");
    $stmt->execute([
        $favoriteFlow,
        $primaryGoal,
        $_SESSION['user_id']
    ]);

    $db->commit();

    $_SESSION['name'] = $name;
    $_SESSION['username'] = $username;

    header('Location: ../profile.php?success=profile_updated#account-settings');
    exit();
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    header('Location: ../profile.php?error=update_failed#account-settings');
    exit();
}