<?php
session_start();
require_once '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $db = getDatabaseConnection();

    $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['photo'] = $user['photo'];

        header('Location: ../profile.php');
        exit();
    } else {
        header('Location: ../login.php?error=invalid_credentials');
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
}
?>