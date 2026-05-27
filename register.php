<?php

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

$selectedPlan = $_GET['plan'] ?? 'starter';
$errorMessage = $_GET['error'] ?? null;

$pageTitle = 'Register | Flow Fitness';
$extraCSS = 'profile.css';

include 'templates/header.php';
?>

<main class="auth-page bg-light">
    <div class="card form-container auth-card auth-card-large">
        <h2 class="auth-title text-center">Join Flow Fitness</h2>
        <p class="auth-subtitle text-center text-muted">Start your journey today.</p>

        <?php if ($errorMessage): ?>
            <p class="form-error text-center auth-error-message auth-error-message-strong">
                <?php
                    if ($errorMessage === 'user_exists') {
                        echo 'Username or Email already taken.';
                    } elseif ($errorMessage === 'db_fail') {
                        echo 'Registration failed. Please try again.';
                    } elseif ($errorMessage === 'empty_fields') {
                        echo 'All fields are required.';
                    } elseif ($errorMessage === 'invalid_email') {
                        echo 'Please provide a valid email address.';
                    } elseif ($errorMessage === 'short_password') {
                        echo 'Password must be at least 6 characters long.';
                    } elseif ($errorMessage === 'invalid_plan') {
                        echo 'Please select a valid membership plan.';
                    } else {
                        echo htmlspecialchars($errorMessage);
                    }
                ?>
            </p>
        <?php endif; ?>

        <form action="actions/action_register.php" method="POST" class="profile-form auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    minlength="6"
                    required
                >
            </div>

            <div class="form-group form-group-large">
                <label for="plan_id">Select Plan</label>

                <select id="plan_id" name="plan_id" class="custom-select" required>
                    <option value="1" <?php if ($selectedPlan === 'starter') echo 'selected'; ?>>
                        Starter - €30/month
                    </option>

                    <option value="2" <?php if ($selectedPlan === 'pro') echo 'selected'; ?>>
                        Pro - €45/month
                    </option>

                    <option value="3" <?php if ($selectedPlan === 'elite') echo 'selected'; ?>>
                        Elite - €70/month
                    </option>
                </select>
            </div>

            <button type="submit" class="btn-primary btn-block">
                Create Account
            </button>
        </form>

        <p class="auth-footer text-center">
            Already a member? <a href="login.php">Log In</a>
        </p>
    </div>
</main>

<script src="js/auth.js"></script>

<?php include 'templates/footer.php'; ?>