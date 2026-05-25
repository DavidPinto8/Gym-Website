<?php

session_start();

require_once 'database/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit();
}

if ($_SESSION['role'] === 'trainer') {
    header('Location: trainer_dashboard.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT
        users.*,
        members.streak,
        members.favorite_flow,
        members.primary_goal,
        members.next_billing_date,
        members.plan_id,
        plans.name AS plan_name,
        plans.price AS plan_price
    FROM users
    JOIN members
        ON users.id = members.user_id
    LEFT JOIN plans
        ON members.plan_id = plans.id
    WHERE users.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: actions/action_logout.php');
    exit();
}

$stmtAttended = $db->prepare("
    SELECT COUNT(*)
    FROM enrollments
    WHERE attended = 1
      AND member_id = (
          SELECT id
          FROM members
          WHERE user_id = ?
      )
");
$stmtAttended->execute([$_SESSION['user_id']]);
$attendedCount = $stmtAttended->fetchColumn();

$successMessages = [
    'profile_updated' => 'Profile updated successfully.',
    'plan_updated' => 'Membership plan updated successfully.'
];

$errorMessages = [
    'empty_fields' => 'Please fill in all required fields.',
    'username_taken' => 'That username is already taken.',
    'update_failed' => 'Something went wrong while updating your profile.',
    'invalid_plan' => 'Please select a valid membership plan.'
];

$successMessage = null;
$errorMessage = null;

if (isset($_GET['success'], $successMessages[$_GET['success']])) {
    $successMessage = $successMessages[$_GET['success']];
}

if (isset($_GET['error'], $errorMessages[$_GET['error']])) {
    $errorMessage = $errorMessages[$_GET['error']];
}

$pageTitle = 'My Profile | Flow Fitness';
$extraCSS = 'profile.css';

include 'templates/header.php';
?>

<main class="profile-page bg-light">
    <div class="container profile-layout">
        <aside class="profile-sidebar card">
            <div class="profile-summary text-center">
                <?php
                    require_once 'includes/avatar.php';

                    $profileAvatar = getUserAvatar(
                        $user['name'],
                        $user['photo'] ?? null,
                        180
                    );
                ?>

                <img
                    src="<?php echo htmlspecialchars($profileAvatar); ?>"
                    alt="<?php echo htmlspecialchars($user['name']); ?> profile picture"
                    id="profile-picture-preview"
                >

                <input
                    type="file"
                    id="avatar-upload"
                    name="avatar"
                    accept="image/png, image/jpeg, image/jpg"
                >

                <label for="avatar-upload" class="btn-outline avatar-upload-btn">
                    Change Photo
                </label>

                <h3><?php echo htmlspecialchars($user['name']); ?></h3>

                <span class="badge-gold">
                    <?php echo htmlspecialchars($user['plan_name'] ?? 'Basic'); ?> Member
                </span>
            </div>

            <nav class="profile-nav">
                <ul>
                    <li><a href="#my-flow" class="active">My Flow</a></li>
                    <li><a href="#account-settings">Account Settings</a></li>
                    <li><a href="#membership-details">Membership Plan</a></li>
                    <li><a href="actions/action_logout.php" class="text-danger">Log Out</a></li>
                </ul>
            </nav>
        </aside>

        <section class="profile-content">
            <?php if ($successMessage): ?>
                <p class="form-message form-success">
                    <?php echo htmlspecialchars($successMessage); ?>
                </p>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <p class="form-message form-error">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </p>
            <?php endif; ?>

            <div id="my-flow" class="stats-grid" role="region">
                <div class="stat-card card">
                    <span class="stat-title">Current Streak</span>

                    <span class="stat-value">
                        <?php echo htmlspecialchars($user['streak'] ?? 0); ?> <small>Days</small>
                    </span>
                </div>

                <div class="stat-card card">
                    <span class="stat-title">Classes Attended</span>

                    <span class="stat-value">
                        <?php echo htmlspecialchars($attendedCount); ?>
                    </span>
                </div>

                <div class="stat-card card">
                    <span class="stat-title">Next Milestone</span>

                    <span class="stat-value">
                        150 <small>Workouts</small>
                    </span>
                </div>
            </div>

            <div id="account-settings" class="card form-container">
                <h2 id="form-title">Account Settings</h2>
                <p class="text-muted">Manage your personal details and workout preferences.</p>

                <form action="actions/action_update_profile.php" method="POST" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="name">Full Name</label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?php echo htmlspecialchars($user['name']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>"
                                required
                            >
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Workout Preferences</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="fav-class">Favorite Flow</label>

                                <select id="fav-class" name="fav-class" class="custom-select">
                                    <option value="hiit" <?php if (($user['favorite_flow'] ?? '') === 'hiit') echo 'selected'; ?>>
                                        HIIT & Conditioning
                                    </option>

                                    <option value="mobility" <?php if (($user['favorite_flow'] ?? '') === 'mobility') echo 'selected'; ?>>
                                        Mobility & Yoga
                                    </option>

                                    <option value="strength" <?php if (($user['favorite_flow'] ?? '') === 'strength') echo 'selected'; ?>>
                                        Strength Training
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="fitness-goal">Primary Goal</label>

                                <select id="fitness-goal" name="fitness-goal" class="custom-select">
                                    <option value="endurance" <?php if (($user['primary_goal'] ?? '') === 'endurance') echo 'selected'; ?>>
                                        Build Endurance
                                    </option>

                                    <option value="muscle" <?php if (($user['primary_goal'] ?? '') === 'muscle') echo 'selected'; ?>>
                                        Gain Muscle
                                    </option>

                                    <option value="weightloss" <?php if (($user['primary_goal'] ?? '') === 'weightloss') echo 'selected'; ?>>
                                        Weight Loss
                                    </option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <div id="membership-details" class="card form-container">
                <h2>Current Membership</h2>
                <p class="text-muted">Review your active plan and billing details.</p>

                <div class="membership-details">
                    <div class="plan-info">
                        <span class="badge-gold">
                            <?php echo htmlspecialchars($user['plan_name'] ?? 'Basic'); ?> Member
                        </span>

                        <p class="price">
                            €<?php echo htmlspecialchars($user['plan_price'] ?? '0.00'); ?><span>/month</span>
                        </p>

                        <?php if (!empty($user['next_billing_date'])): ?>
                            <p class="billing-date">
                                Next billing date:
                                <?php echo htmlspecialchars(date('M d, Y', strtotime($user['next_billing_date']))); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="plan-actions">
                        <form action="actions/action_upgrade_plan.php" method="POST" class="plan-upgrade-form">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <label for="plan_id">Change Membership Plan</label>

                            <select id="plan_id" name="plan_id" class="custom-select" required>
                                <option value="1" <?php if ((int)($user['plan_id'] ?? 0) === 1) echo 'selected'; ?>>
                                    Starter - €30/month
                                </option>

                                <option value="2" <?php if ((int)($user['plan_id'] ?? 0) === 2) echo 'selected'; ?>>
                                    Pro - €45/month
                                </option>

                                <option value="3" <?php if ((int)($user['plan_id'] ?? 0) === 3) echo 'selected'; ?>>
                                    Elite - €70/month
                                </option>
                            </select>

                            <button type="submit" class="btn-primary btn-large">
                                Update Plan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script src="js/profile.js"></script>

<?php include 'templates/footer.php'; ?>