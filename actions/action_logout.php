<?php

session_start();

require_once __DIR__ . '/../includes/action_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    action_redirect('../index.php');
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    action_redirect('../index.php', [
        'error' => 'Invalid security token.'
    ]);
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        (bool) $params['secure'],
        (bool) $params['httponly']
    );
}

session_destroy();

action_redirect('../index.php');