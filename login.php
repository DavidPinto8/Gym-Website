<?php 
    session_start();
    if (isset($_SESSION['user_id'])) {
        header("Location: profile.php");
        exit();
    }
    
    $pageTitle = "Login | Flow Fitness";
    $extraCSS = 'profile.css'; 
    include 'templates/header.php'; 
?>

<main class="bg-light" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div class="card form-container" style="max-width: 400px; width: 100%;">
        <h2 class="text-center" style="margin-bottom: 0.5rem; color: var(--black); font-family: var(--font-heading);">Welcome Back</h2>
        <p class="text-center text-muted" style="margin-bottom: 2rem;">Keep your momentum going.</p>
        
        <form action="actions/action_login.php" method="POST" class="profile-form">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333;">
            </div>
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333;">
            </div>
            
            <button type="submit" class="btn-primary btn-block">Log In</button>
        </form>
        
        <p class="text-center" style="margin-top: 1.5rem; font-size: 0.9rem;">
            Not a member yet? <a href="register.php" style="color: var(--gold); text-decoration: none; font-weight: 600;">Join Now</a>
        </p>
    </div>
</main>

<?php include 'templates/footer.php'; ?>