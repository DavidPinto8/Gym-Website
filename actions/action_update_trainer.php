<?php
session_start();
require_once '../database/db.php';

// Security: Must be logged in as a trainer
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'trainer') {
    header('Location: ../login.php');
    exit();
}

// Security: Must be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../trainer_dashboard.php');
    exit();
}

// Security: CSRF Protection
if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    header('Location: ../trainer_dashboard.php?error=Security_token_invalid#trainer-profile');
    exit();
}

$userId = $_SESSION['user_id'];
$bio = trim($_POST['bio'] ?? '');
$specializations = trim($_POST['specializations'] ?? '');
$certifications = trim($_POST['certifications'] ?? '');

// Validation
if ($bio === '' || $specializations === '' || $certifications === '') {
    header('Location: ../trainer_dashboard.php?error=All_fields_are_required#trainer-profile');
    exit();
}

$db = getDatabaseConnection();

try {
    // Update the trainer's profile based on their user_id
    $stmt = $db->prepare('
        UPDATE trainers 
        SET bio = ?, specializations = ?, certifications = ? 
        WHERE user_id = ?
    ');
    
    $stmt->execute([$bio, $specializations, $certifications, $userId]);

    header('Location: ../trainer_dashboard.php?success=Profile_updated_successfully#trainer-profile');
    exit();

} catch (Exception $e) {
    header('Location: ../trainer_dashboard.php?error=Database_error_occurred#trainer-profile');
    exit();
}