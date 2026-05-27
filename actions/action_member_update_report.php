<?php

session_start();

require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../includes/action_helpers.php';

require_post_request('../reports.php');
require_user_role('member');
require_valid_csrf_token('../reports.php');

$reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$memberResponse = trim($_POST['member_response'] ?? '');
$reportAction = $_POST['report_action'] ?? '';

if (
    $reportId === false ||
    $reportId === null ||
    $reportId <= 0 ||
    !in_array($reportAction, ['reply', 'close'], true)
) {
    action_redirect('../reports.php', [
        'error' => 'invalid_input'
    ]);
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT reports.id, reports.status, reports.subject
        FROM reports
        INNER JOIN members ON reports.member_id = members.id
        WHERE reports.id = ?
          AND members.user_id = ?
    ");

    $stmt->execute([
        $reportId,
        $_SESSION['user_id']
    ]);

    $report = $stmt->fetch();

    if (!$report) {
        action_redirect('../reports.php', [
            'error' => 'report_not_found'
        ]);
    }

    if ($report['status'] === 'resolved') {
        action_redirect('../reports.php', [
            'error' => 'report_already_resolved'
        ]);
    }

    if ($reportAction === 'reply' && $memberResponse === '') {
        action_redirect('../reports.php', [
            'error' => 'empty_response'
        ]);
    }

    $db->beginTransaction();

    if ($reportAction === 'close') {
        $stmt = $db->prepare("
            UPDATE reports
            SET status = 'resolved',
                closed_by_member = 1,
                updated_at = datetime('now')
            WHERE id = ?
        ");

        $stmt->execute([$reportId]);

        $notificationMessage = 'A member closed the report "' . $report['subject'] . '".';
        $redirectSuccess = 'report_closed';
    } else {
        $stmt = $db->prepare("
            UPDATE reports
            SET member_response = ?,
                status = 'in_progress',
                updated_at = datetime('now')
            WHERE id = ?
        ");

        $stmt->execute([
            $memberResponse,
            $reportId
        ]);

        $notificationMessage = 'A member replied to the report "' . $report['subject'] . '".';
        $redirectSuccess = 'response_sent';
    }

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

    foreach ($admins as $admin) {
        $stmtInsert->execute([
            $admin['id'],
            $notificationMessage,
            'admin_reports.php'
        ]);
    }

    $db->commit();

    action_redirect('../reports.php', [
        'success' => $redirectSuccess
    ]);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    action_redirect('../reports.php', [
        'error' => 'update_failed'
    ]);
}