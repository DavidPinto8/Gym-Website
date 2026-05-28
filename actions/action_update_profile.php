<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../profile.php?error=update_failed');
require_user_role('member');
require_valid_csrf_token('../profile.php?error=update_failed');

$formType = $_POST['form_type'] ?? 'profile';

$db = getDatabaseConnection();

if ($formType === 'password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {
        action_redirect('../profile.php?error=empty_fields#password-settings');
    }

    if (strlen($newPassword) < 6) {
        action_redirect('../profile.php?error=short_password#password-settings');
    }

    if ($newPassword !== $confirmPassword) {
        action_redirect('../profile.php?error=password_mismatch#password-settings');
    }

    try {
        $stmt = $db->prepare("
            SELECT password
            FROM users
            WHERE id = ?
        ");

        $stmt->execute([$_SESSION['user_id']]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            action_redirect('../profile.php?error=invalid_current_password#password-settings');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        if ($hashedPassword === false) {
            action_redirect('../profile.php?error=update_failed#password-settings');
        }

        $stmt = $db->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $hashedPassword,
            $_SESSION['user_id']
        ]);

        action_redirect('../profile.php?success=password_updated#password-settings');
    } catch (Throwable $exception) {
        action_redirect('../profile.php?error=update_failed#password-settings');
    }
}

if ($formType !== 'profile') {
    action_redirect('../profile.php?error=update_failed');
}

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$favoriteFlow = trim($_POST['fav-class'] ?? '');
$primaryGoal = trim($_POST['fitness-goal'] ?? '');

$validFlows = [
    'hiit',
    'mobility',
    'strength'
];

$validGoals = [
    'endurance',
    'muscle',
    'weightloss'
];

if (
    $name === '' ||
    $username === '' ||
    !in_array($favoriteFlow, $validFlows, true) ||
    !in_array($primaryGoal, $validGoals, true)
) {
    action_redirect('../profile.php?error=empty_fields#account-settings');
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
        action_redirect('../profile.php?error=username_taken#account-settings');
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

    action_redirect('../profile.php?success=profile_updated#account-settings');
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../profile.php?error=update_failed#account-settings');
}