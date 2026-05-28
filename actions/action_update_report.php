<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../admin_reports.php');
require_user_role('admin');
require_valid_csrf_token('../admin_reports.php');

$reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');
$adminResponse = trim($_POST['admin_response'] ?? '');

$validStatuses = [
    'open',
    'in_progress',
    'resolved'
];

if (
    $reportId === false ||
    $reportId === null ||
    $reportId <= 0 ||
    !in_array($status, $validStatuses, true)
) {
    action_redirect('../admin_reports.php', [
        'error' => 'invalid_input'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT
            reports.subject,
            reports.status AS old_status,
            reports.admin_response AS old_admin_response,
            users.id AS member_user_id
        FROM reports
        INNER JOIN members ON reports.member_id = members.id
        INNER JOIN users ON members.user_id = users.id
        WHERE reports.id = ?
    ");

    $stmt->execute([$reportId]);

    $report = $stmt->fetch();

    if (!$report) {
        action_redirect('../admin_reports.php', [
            'error' => 'report_not_found'
        ]);
    }

    $responseChanged = $adminResponse !== ($report['old_admin_response'] ?? '');
    $statusChanged = $status !== $report['old_status'];

    if (!$responseChanged && !$statusChanged) {
        action_redirect('../admin_reports.php', [
            'success' => 'report_updated'
        ]);
    }

    $db->beginTransaction();

    $stmt = $db->prepare("
        UPDATE reports
        SET status = ?,
            admin_response = ?,
            updated_at = datetime('now')
        WHERE id = ?
    ");

    $stmt->execute([
        $status,
        $adminResponse,
        $reportId
    ]);

    if ($responseChanged || $statusChanged) {
        if ($status === 'resolved' && $statusChanged) {
            $message = 'Your report "' . $report['subject'] . '" was marked as resolved.';
        } elseif ($adminResponse !== '' && $responseChanged) {
            $message = 'An admin responded to your report "' . $report['subject'] . '".';
        } else {
            $message = 'The status of your report "' . $report['subject'] . '" was updated.';
        }

        $stmt = $db->prepare("
            INSERT INTO notifications (
                user_id,
                message,
                link
            )
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $report['member_user_id'],
            $message,
            'reports.php'
        ]);
    }

    $db->commit();

    action_redirect('../admin_reports.php', [
        'success' => 'report_updated'
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../admin_reports.php', [
        'error' => 'update_failed'
    ]);
}