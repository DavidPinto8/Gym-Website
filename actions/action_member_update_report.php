<?php
session_start();

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../reports.php?error=invalid_request');
    exit();
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../reports.php?error=invalid_csrf');
    exit();
}

$reportId = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
$memberResponse = trim($_POST['member_response'] ?? '');
$action = $_POST['report_action'] ?? '';

if (!$reportId || !in_array($action, ['reply', 'close'], true)) {
    header('Location: ../reports.php?error=invalid_input');
    exit();
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        SELECT reports.id, reports.status, reports.subject
        FROM reports
        JOIN members ON reports.member_id = members.id
        WHERE reports.id = ?
        AND members.user_id = ?
    ");
    $stmt->execute([$reportId, $_SESSION['user_id']]);
    $report = $stmt->fetch();

    if (!$report) {
        header('Location: ../reports.php?error=report_not_found');
        exit();
    }

    if ($report['status'] === 'resolved') {
        header('Location: ../reports.php?error=report_already_resolved');
        exit();
    }

    if ($action === 'close') {
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
        if ($memberResponse === '') {
            header('Location: ../reports.php?error=empty_response');
            exit();
        }

        $stmt = $db->prepare("
            UPDATE reports
            SET member_response = ?,
                status = 'in_progress',
                updated_at = datetime('now')
            WHERE id = ?
        ");
        $stmt->execute([$memberResponse, $reportId]);

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
        INSERT INTO notifications (user_id, message, link)
        VALUES (?, ?, ?)
    ");

    foreach ($admins as $admin) {
        $stmtInsert->execute([
            $admin['id'],
            $notificationMessage,
            'admin_reports.php'
        ]);
    }

    header('Location: ../reports.php?success=' . $redirectSuccess);
    exit();
} catch (Exception $e) {
    header('Location: ../reports.php?error=update_failed');
    exit();
}