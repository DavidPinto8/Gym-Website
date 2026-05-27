<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin_reports.php?error=invalid_request');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../admin_reports.php?error=invalid_csrf');
    exit();
}

$reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';
$adminResponse = trim($_POST['admin_response'] ?? '');

$validStatuses = ['open', 'in_progress', 'resolved'];

if (!$reportId || !in_array($status, $validStatuses, true)) {
    header('Location: ../admin_reports.php?error=invalid_input');
    exit();
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
        JOIN members ON reports.member_id = members.id
        JOIN users ON members.user_id = users.id
        WHERE reports.id = ?
    ");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();

    if (!$report) {
        header('Location: ../admin_reports.php?error=report_not_found');
        exit();
    }

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

    $responseChanged = $adminResponse !== '' && $adminResponse !== ($report['old_admin_response'] ?? '');
    $statusChanged = $status !== $report['old_status'];

    if ($responseChanged || $statusChanged) {
        $message = $status === 'resolved'
            ? 'Your report "' . $report['subject'] . '" was marked as resolved.'
            : 'An admin responded to your report "' . $report['subject'] . '".';

        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, message, link)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $report['member_user_id'],
            $message,
            'reports.php'
        ]);
    }

    header('Location: ../admin_reports.php?success=report_updated');
    exit();
} catch (Exception $e) {
    header('Location: ../admin_reports.php?error=update_failed');
    exit();
}