<?php
session_start();

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    header('Location: ../reports.php?error=Invalid CSRF security token.');
    exit();
}

require_once '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../reports.php?error=Invalid request.');
    exit();
}

$category = $_POST['category'] ?? '';
$subject = trim($_POST['subject'] ?? '');
$description = trim($_POST['description'] ?? '');

$validCategories = ['equipment', 'class', 'trainer', 'other'];

if (!in_array($category, $validCategories, true) || $subject === '' || $description === '') {
    header('Location: ../reports.php?error=Please fill in all report fields.');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT id
    FROM members
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: ../reports.php?error=Only members can submit reports.');
    exit();
}

$stmt = $db->prepare("
    INSERT INTO reports (member_id, category, subject, description)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([
    $member['id'],
    $category,
    $subject,
    $description
]);

header('Location: ../reports.php?success=Report submitted successfully.');
exit();