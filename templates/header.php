<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Flow Fitness — premium gym with classes, personal trainers, and state-of-the-art equipment. Find your rhythm today.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Flow Fitness'); ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Oswald:wght@500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/components.css">

    <?php if (isset($extraCSS)): ?>
        <link rel="stylesheet" href="css/<?php echo htmlspecialchars($extraCSS); ?>">
    <?php endif; ?>
</head>
<body>
    <header class="main-header">
        <nav class="nav-container container" aria-label="Main navigation">
            <div class="nav-top">
                <div class="logo">
                    <a href="index.php">
                        <img src="assets/logo-landscape.png" alt="Flow Fitness Logo" class="logo-img">
                    </a>
                </div>

                <label class="nav-toggle" for="nav-toggle-checkbox">
                    <span style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0;">Toggle navigation</span>
                    <span></span>
                    <span></span>
                    <span></span>
                </label>
            </div>

            <input type="checkbox" id="nav-toggle-checkbox" class="nav-toggle-input">

            <div class="nav-menu">
                <ul class="nav-links">
                    <li>
                        <a href="index.php" class="<?php echo (isset($currentPage) && $currentPage === 'home') ? 'active' : ''; ?>">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="schedule.php" class="<?php echo (isset($currentPage) && $currentPage === 'schedule') ? 'active' : ''; ?>">
                            Classes
                        </a>
                    </li>
                    <li>
                        <a href="trainers.php" class="<?php echo (isset($currentPage) && $currentPage === 'trainers') ? 'active' : ''; ?>">
                            Trainers
                        </a>
                    </li>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li>
                            <a href="admin.php" class="<?php echo (isset($currentPage) && $currentPage === 'admin') ? 'active' : ''; ?>">
                                Admin
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-auth <?php echo isset($_SESSION['user_id']) ? 'logged-in' : ''; ?>">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                            require_once __DIR__ . '/../includes/avatar.php';

                            $navAvatar = getUserAvatar(
                                $_SESSION['name'] ?? 'User',
                                $_SESSION['photo'] ?? null,
                                100
                            );
                        ?>

                        <a href="profile.php" class="profile-dropdown">
                            <img 
                                src="<?php echo $navAvatar; ?>" 
                                alt="Profile picture" 
                                class="nav-avatar"
                            >
                            <span class="user-greeting">
                                Hi, <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-outline" style="color: var(--white); border-color: var(--white); margin-right: 1rem;">Log In</a>
                        <a href="register.php" class="btn-primary" style="padding: 0.5rem 1rem;">Join Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>