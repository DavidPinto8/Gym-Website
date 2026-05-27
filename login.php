<?php

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

$errorMessage = $_GET['error'] ?? null;

$pageTitle = 'Login | Flow Fitness';
$extraCSS = 'profile.css';

include 'templates/header.php';
?>

<main class="auth-page bg-light">
    <div class="card form-container auth-card">
        <h2 class="auth-title text-center">Welcome Back</h2>
        <p class="auth-subtitle text-center text-muted">Keep your momentum going.</p>

        <?php if ($errorMessage): ?>
            <p class="form-error text-center auth-error-message">
                <?php echo htmlspecialchars($errorMessage === 'invalid' ? 'Invalid username or password.' : $errorMessage); ?>
            </p>
        <?php endif; ?>

        <form action="actions/action_login.php" method="POST" class="profile-form auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >
            </div>

            <div class="form-group form-group-large">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <button type="submit" class="btn-primary btn-block">
                Log In
            </button>
        </form>

        <p class="auth-footer text-center">
            Not a member yet? <a href="register.php">Join Now</a>
        </p>
    </div>
</main>

<script src="js/auth.js"></script>

<?php include 'templates/footer.php'; ?>