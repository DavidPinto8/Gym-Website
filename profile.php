<?php

session_start();

require_once 'database/db.php';
require_once 'includes/avatar.php';

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
        members.payment_method,
        members.card_last4,
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
$attendedCount = (int)$stmtAttended->fetchColumn();

$nextMilestone = max(50, (ceil(($attendedCount + 1) / 50) * 50));

$memberAccessCode = 'FLOW-' . str_pad((string)$user['id'], 5, '0', STR_PAD_LEFT);
$qrPayload = 'FlowFitness Member Access | ID: ' . $memberAccessCode . ' | User: ' . $user['username'];
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($qrPayload);

$successMessages = [
    'profile_updated' => 'Profile updated successfully.',
    'password_updated' => 'Password updated successfully.',
    'plan_updated' => 'Membership plan updated successfully.'
];

$errorMessages = [
    'empty_fields' => 'Please fill in all required fields.',
    'username_taken' => 'That username is already taken.',
    'update_failed' => 'Something went wrong while updating your profile.',
    'invalid_plan' => 'Please select a valid membership plan.',
    'password_mismatch' => 'The new passwords do not match.',
    'short_password' => 'Password must be at least 6 characters long.',
    'invalid_current_password' => 'Current password is incorrect.'
];

$paymentLabels = [
    'visa' => 'Visa',
    'mastercard' => 'Mastercard',
    'paypal' => 'PayPal',
    'apple_pay' => 'Apple Pay',
    'google_pay' => 'Google Pay'
];

$paymentMethodLabel = $paymentLabels[$user['payment_method'] ?? ''] ?? 'No payment method set';

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

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                    id="avatar-csrf-token"
                >

                <label for="avatar-upload" class="btn-outline avatar-upload-btn">
                    Change Photo
                </label>

                <h3><?php echo htmlspecialchars($user['name']); ?></h3>

                <span class="badge-gold">
                    <?php echo htmlspecialchars($user['plan_name'] ?? 'Basic'); ?> Member
                </span>

                <p id="avatar-upload-status" class="avatar-upload-status" aria-live="polite"></p>
            </div>

            <nav class="profile-nav">
                <ul>
                    <li><a href="#my-flow" class="active">My Flow</a></li>
                    <li><a href="#access-pass">Access Pass</a></li>
                    <li><a href="#account-settings">Account Settings</a></li>
                    <li><a href="#password-settings">Password</a></li>
                    <li><a href="#membership-details">Membership & Billing</a></li>
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
                        <?php echo htmlspecialchars($nextMilestone); ?> <small>Workouts</small>
                    </span>
                </div>
            </div>

            <div id="access-pass" class="card form-container access-pass-card">
                <div class="access-pass-content">
                    <div>
                        <span class="access-pass-kicker">Digital Access Pass</span>

                        <h2>Gym Entry QR</h2>

                        <p class="text-muted">
                            Use this member pass at the front desk to identify your Flow Fitness account.
                        </p>

                        <div class="access-pass-details">
                            <div>
                                <strong>Member</strong>
                                <span><?php echo htmlspecialchars($user['name']); ?></span>
                            </div>

                            <div>
                                <strong>Plan</strong>
                                <span><?php echo htmlspecialchars($user['plan_name'] ?? 'Basic'); ?></span>
                            </div>

                            <div>
                                <strong>Access Code</strong>
                                <span><?php echo htmlspecialchars($memberAccessCode); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="qr-pass-box">
                        <img
                            src="<?php echo htmlspecialchars($qrUrl); ?>"
                            alt="Member access QR code"
                            class="member-qr-code"
                        >

                        <span>Scan for member access</span>
                    </div>
                </div>
            </div>

            <div id="account-settings" class="card form-container">
                <h2 id="form-title">Account Settings</h2>
                <p class="text-muted">Manage your personal details and workout preferences.</p>

                <form action="actions/action_update_profile.php" method="POST" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="form_type" value="profile">

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

            <div id="password-settings" class="card form-container">
                <h2>Password Settings</h2>
                <p class="text-muted">Update your password securely.</p>

                <form action="actions/action_update_profile.php" method="POST" class="profile-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="form_type" value="password">

                    <fieldset>
                        <legend>Security</legend>

                        <div class="form-group">
                            <label for="current-password">Current Password</label>

                            <input
                                type="password"
                                id="current-password"
                                name="current_password"
                                required
                            >
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="new-password">New Password</label>

                                <input
                                    type="password"
                                    id="new-password"
                                    name="new_password"
                                    minlength="6"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="confirm-password">Confirm New Password</label>

                                <input
                                    type="password"
                                    id="confirm-password"
                                    name="confirm_password"
                                    minlength="6"
                                    required
                                >
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <div id="membership-details" class="card form-container">
                <h2>Membership & Billing</h2>
                <p class="text-muted">Review your active plan, billing details and mock payment method.</p>

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

                        <div class="payment-summary-card">
                            <strong>Payment Method</strong>

                            <span>
                                <?php echo htmlspecialchars($paymentMethodLabel); ?>

                                <?php if (!empty($user['card_last4'])): ?>
                                    ending in <?php echo htmlspecialchars($user['card_last4']); ?>
                                <?php endif; ?>
                            </span>

                            <a href="billing.php" class="btn-outline payment-link">
                                Manage Payment Method
                            </a>
                        </div>
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

                            <?php if (empty($user['payment_method'])): ?>
                                <p class="payment-warning">
                                    Add a payment method before changing your membership plan.
                                </p>
                            <?php endif; ?>

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