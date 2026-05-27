<?php

session_start();

require_once __DIR__ . '/../database/db.php';

header('Content-Type: application/json; charset=UTF-8');

function avatar_response(int $statusCode, bool $success, string $message, array $extra = []): void
{
    http_response_code($statusCode);

    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $extra));

    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    avatar_response(405, false, 'Invalid request method.');
}

if (!isset($_SESSION['user_id'])) {
    avatar_response(401, false, 'Unauthorized access.');
}

$headers = function_exists('getallheaders') ? getallheaders() : [];

$clientToken = $_POST['csrf_token']
    ?? $headers['X-CSRF-Token']
    ?? $headers['x-csrf-token']
    ?? '';

if (
    empty($clientToken) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $clientToken)
) {
    avatar_response(403, false, 'Invalid security token.');
}

if (!isset($_FILES['avatar'])) {
    avatar_response(400, false, 'PHP did not receive any file data.');
}

$file = $_FILES['avatar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File is too large.',
        UPLOAD_ERR_FORM_SIZE => 'File is too large.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];

    avatar_response(
        400,
        false,
        $errorMessages[$file['error']] ?? 'Unknown upload error.'
    );
}

$maxSize = 2 * 1024 * 1024;

if ((int) $file['size'] <= 0 || (int) $file['size'] > $maxSize) {
    avatar_response(400, false, 'File is too large. Maximum size is 2MB.');
}

if (!is_uploaded_file($file['tmp_name'])) {
    avatar_response(400, false, 'Invalid uploaded file.');
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png'
];

try {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
} catch (Throwable $exception) {
    avatar_response(500, false, 'Failed to determine file type.');
}

if (!isset($allowedTypes[$mimeType])) {
    avatar_response(400, false, 'Invalid format. Only JPG and PNG are allowed.');
}

$imageInfo = @getimagesize($file['tmp_name']);

if ($imageInfo === false) {
    avatar_response(400, false, 'Invalid image file.');
}

$extension = $allowedTypes[$mimeType];
$userId = (int) $_SESSION['user_id'];
$newFilename = 'avatar_user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
$uploadPath = __DIR__ . '/../assets/' . $newFilename;

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    avatar_response(500, false, 'Failed to save the image on the server.');
}

$db = getDatabaseConnection();

try {
    $stmt = $db->prepare("
        UPDATE users
        SET photo = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $newFilename,
        $userId
    ]);

    if ($stmt->rowCount() === 0) {
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }

        avatar_response(404, false, 'User not found.');
    }

    $_SESSION['photo'] = $newFilename;

    avatar_response(
        200,
        true,
        'Avatar updated successfully.',
        [
            'new_avatar_url' => 'assets/' . $newFilename
        ]
    );
} catch (Throwable $exception) {
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }

    avatar_response(500, false, 'Database error.');
}