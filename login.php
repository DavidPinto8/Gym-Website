<?php 
    session_start();

    if (isset($_SESSION['user_id'])) {
        header("Location: profile.php");
        exit();
    }

    $loginError = isset($_GET['error']);
    
    $pageTitle = "Login | Flow Fitness";
    $extraCSS = 'profile.css'; 
    include 'templates/header.php'; 
?>

<main class="auth-page bg-light">
    <div class="card form-container auth-card">
        <h2 class="auth-title text-center">Welcome Back</h2>
        <p class="auth-subtitle text-center text-muted">Keep your momentum going.</p>

        <?php if ($loginError): ?>
            <p class="form-error text-center">Invalid username or password.</p>
        <?php endif; ?>
        
        <form action="actions/action_login.php" method="POST" class="profile-form auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group form-group-large">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-primary btn-block">Log In</button>
        </form>
        
        <p class="auth-footer text-center">
            Not a member yet? <a href="register.php">Join Now</a>
        </p>
    </div>
</main>

<script src="js/auth.js"></script>
<?php include 'templates/footer.php'; ?>