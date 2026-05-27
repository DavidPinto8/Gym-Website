<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../trainer_dashboard.php');
require_user_role('trainer');
require_valid_csrf_token('../trainer_dashboard.php');

$bio = trim($_POST['bio'] ?? '');
$specializations = trim($_POST['specializations'] ?? '');
$certifications = trim($_POST['certifications'] ?? '');

if (
    $bio === '' ||
    $specializations === '' ||
    $certifications === ''
) {
    action_redirect('../trainer_dashboard.php?error=All_fields_are_required#trainer-profile');
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        UPDATE trainers
        SET bio = ?,
            specializations = ?,
            certifications = ?
        WHERE user_id = ?
    ");

    $stmt->execute([
        $bio,
        $specializations,
        $certifications,
        $_SESSION['user_id']
    ]);

    if ($stmt->rowCount() === 0) {
        $stmt = $db->prepare("
            SELECT id
            FROM trainers
            WHERE user_id = ?
        ");

        $stmt->execute([$_SESSION['user_id']]);

        if (!$stmt->fetch()) {
            action_redirect('../trainer_dashboard.php?error=Trainer_profile_not_found#trainer-profile');
        }
    }

    action_redirect('../trainer_dashboard.php?success=Profile_updated_successfully#trainer-profile');
} catch (Throwable $exception) {
    action_redirect('../trainer_dashboard.php?error=Database_error_occurred#trainer-profile');
}