<?php
session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../admin_reports.php?error=invalid_csrf');
    exit();
}

require_once '../database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin_reports.php?error=invalid_request');
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

try{
    $stmt = $db->prepare("
        UPDATE reports
        SET status = ?, admin_response = ?, updated_at = datetime('now')
        WHERE id = ?
    ");
    $stmt->execute([
        $status,
        $adminResponse,
        $reportId
    ]);

    header('Location: ../admin_reports.php?success=report_updated');
    exit();
} catch(Exception $e){
    header('Location: ../admin_reports.php?error=update_failed');
    exit();
}