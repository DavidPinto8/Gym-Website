<?php 
    session_start();
    if (isset($_SESSION['user_id'])) {
        header("Location: profile.php");
        exit();
    }
    
    $selectedPlan = isset($_GET['plan']) ? $_GET['plan'] : 'starter';
    
    $pageTitle = "Register | Flow Fitness";
    $extraCSS = 'profile.css'; 
    include 'templates/header.php'; 
?>

<main class="bg-light" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem;">
    <div class="card form-container" style="max-width: 500px; width: 100%;">
        <h2 class="text-center" style="margin-bottom: 0.5rem; color: var(--black); font-family: var(--font-heading);">Join Flow Fitness</h2>
        <p class="text-center text-muted" style="margin-bottom: 2rem;">Start your journey today.</p>
        
        <form action="actions/action_register.php" method="POST" class="profile-form">
            <div class="form-row" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="flex: 1;">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333;">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333;">
            </div>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333;">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333;">
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="plan_id">Select Plan</label>
                <select id="plan_id" name="plan_id" required style="width: 100%; padding: 0.8rem; background-color: #ffffff; border: 1px solid #ccc; border-radius: 4px; color: #333; cursor: pointer;">
                    <option value="1" <?php if($selectedPlan == 'starter') echo 'selected'; ?>>Starter - €30/month</option>
                    <option value="2" <?php if($selectedPlan == 'pro') echo 'selected'; ?>>Pro - €45/month</option>
                    <option value="3" <?php if($selectedPlan == 'elite') echo 'selected'; ?>>Elite - €70/month</option>
                </select>
            </div>
            
            <button type="submit" class="btn-primary btn-block">Create Account</button>
        </form>
        
        <p class="text-center" style="margin-top: 1.5rem; font-size: 0.9rem;">
            Already a member? <a href="login.php" style="color: var(--gold); text-decoration: none; font-weight: 600;">Log In</a>
        </p>
    </div>
</main>

<?php include 'templates/footer.php'; ?>