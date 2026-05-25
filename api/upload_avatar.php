<?php

session_start();

require_once '../database/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access.'
    ]);
    exit();
}

$headers = function_exists('getallheaders') ? getallheaders() : [];
$clientToken = $_POST['csrf_token'] ?? $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';

if (
    empty($clientToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $clientToken)
) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token.'
    ]);
    exit();
}

if (!isset($_FILES['avatar'])) {
    echo json_encode([
        'success' => false,
        'message' => 'PHP did not receive any file data.'
    ]);
    exit();
}

if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = $_FILES['avatar']['error'];
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large.',
        UPLOAD_ERR_FORM_SIZE => 'File is too large.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];

    echo json_encode([
        'success' => false,
        'message' => $errorMessages[$errorCode] ?? 'Unknown upload error.'
    ]);
    exit();
}

$file = $_FILES['avatar'];
$userId = $_SESSION['user_id'];
$maxSize = 2 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    echo json_encode([
        'success' => false,
        'message' => 'File is too large. Maximum size is 2MB.'
    ]);
    exit();
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png'
];

$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($fileInfo, $file['tmp_name']);
finfo_close($fileInfo);

if (!array_key_exists($mimeType, $allowedTypes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid format. Only JPG and PNG are allowed.'
    ]);
    exit();
}

$extension = $allowedTypes[$mimeType];
$newFilename = 'avatar_user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$uploadPath = '../assets/' . $newFilename;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save the image on the server.'
    ]);
    exit();
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        UPDATE users
        SET photo = ?
        WHERE id = ?
    ");
    $stmt->execute([$newFilename, $userId]);

    $_SESSION['photo'] = $newFilename;

    echo json_encode([
        'success' => true,
        'new_avatar_url' => 'assets/' . $newFilename,
        'message' => 'Avatar updated successfully.'
    ]);
} catch (Exception $e) {
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }

    echo json_encode([
        'success' => false,
        'message' => 'Database error.'
    ]);
}