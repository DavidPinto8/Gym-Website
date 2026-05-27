<?php

function action_redirect(string $path, array $parameters = []): void
{
    $url = $path;

    if (!empty($parameters)) {
        $url .= '?' . http_build_query($parameters);
    }

    header('Location: ' . $url);
    exit();
}

function require_post_request(string $redirectPath): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        action_redirect($redirectPath, [
            'error' => 'Invalid request.'
        ]);
    }
}

function require_authenticated_user(): void
{
    if (!isset($_SESSION['user_id'])) {
        action_redirect('../login.php');
    }
}

function require_user_role(string $requiredRole, string $redirectPath = '../login.php'): void
{
    if (
        !isset($_SESSION['user_id']) ||
        ($_SESSION['role'] ?? '') !== $requiredRole
    ) {
        action_redirect($redirectPath);
    }
}

function require_valid_csrf_token(string $redirectPath): void
{
    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        action_redirect($redirectPath, [
            'error' => 'Invalid CSRF security token.'
        ]);
    }
}


function get_current_member(PDO $db): ?array
{
    $stmt = $db->prepare("
        SELECT id
        FROM members
        WHERE user_id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $member = $stmt->fetch();

    return $member ?: null;
}