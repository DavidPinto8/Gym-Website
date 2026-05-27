<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../reports.php');
require_user_role('member');
require_valid_csrf_token('../reports.php');

$category = trim($_POST['category'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$description = trim($_POST['description'] ?? '');

$validCategories = [
    'equipment',
    'class',
    'trainer',
    'other'
];

if (
    !in_array($category, $validCategories, true) ||
    $subject === '' ||
    $description === ''
) {
    action_redirect('../reports.php', [
        'error' => 'invalid_input'
    ]);
}

$db = getDatabaseConnection();

try {
    $member = get_current_member($db);

    if (!$member) {
        action_redirect('../reports.php', [
            'error' => 'not_a_member'
        ]);
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO reports (
            member_id,
            category,
            subject,
            description,
            status
        )
        VALUES (?, ?, ?, ?, 'open')
    ");

    $stmt->execute([
        $member['id'],
        $category,
        $subject,
        $description
    ]);

    $stmt = $db->query("
        SELECT id
        FROM users
        WHERE role = 'admin'
    ");

    $admins = $stmt->fetchAll();

    $stmtInsert = $db->prepare("
        INSERT INTO notifications (
            user_id,
            message,
            link
        )
        VALUES (?, ?, ?)
    ");

    $notificationMessage = 'A new report was submitted: "' . $subject . '".';

    foreach ($admins as $admin) {
        $stmtInsert->execute([
            $admin['id'],
            $notificationMessage,
            'admin_reports.php'
        ]);
    }

    $db->commit();

    action_redirect('../reports.php', [
        'success' => 'report_submitted'
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../reports.php', [
        'error' => 'update_failed'
    ]);
}