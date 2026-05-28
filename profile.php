<?php

session_start();

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/avatar.php';
require_once __DIR__ . '/includes/message_maps.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: admin.php');
    exit();
}

if (($_SESSION['role'] ?? '') === 'trainer') {
    header('Location: trainer_dashboard.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT
        users.id,
        users.name,
        users.username,
        users.photo,
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
    INNER JOIN members
        ON users.id = members.user_id
    LEFT JOIN plans
        ON members.plan_id = plans.id
    WHERE users.id = ?
");

$stmt->execute([$_SESSION['user_id']]);

$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();

    header('Location: login.php');
    exit();
}

$stmt = $db->prepare("
    SELECT COUNT(*)
    FROM enrollments
    WHERE attended = 1
      AND member_id = (
          SELECT id
          FROM members
          WHERE user_id = ?
      )
");

$stmt->execute([$_SESSION['user_id']]);

$attendedCount = (int) $stmt->fetchColumn();
$nextMilestone = max(50, (int) ceil(($attendedCount + 1) / 50) * 50);

$memberAccessCode = 'FLOW-' . str_pad((string) $user['id'], 5, '0', STR_PAD_LEFT);
$qrPayload = 'FlowFitness Member Access | ID: ' . $memberAccessCode . ' | User: ' . $user['username'];
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($qrPayload);

$profileMessages = get_message_map('profile');

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

if (isset($_GET['success'], $profileMessages[$_GET['success']])) {
    $successMessage = $profileMessages[$_GET['success']];
}

if (isset($_GET['error'], $profileMessages[$_GET['error']])) {
    $errorMessage = $profileMessages[$_GET['error']];
}

$pageTitle = 'My Profile | Flow Fitness';
$currentPage = 'profile';
$extraCSS = 'profile.css';

include __DIR__ . '/templates/header.php';
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
                    src="<?php echo htmlspecialchars($profileAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?> profile picture"
                    id="profile-picture-preview"
                >

                <input
                    type="file"
                    id="avatar-upload"
                    name="avatar"
                    accept="image/png, image/jpeg"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                    id="avatar-csrf-token"
                >

                <label for="avatar-upload" class="btn-outline avatar-upload-btn">
                    Change Photo
                </label>

                <h3><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></h3>

                <span class="badge-gold">
                    <?php echo htmlspecialchars($user['plan_name'] ?? 'Basic', ENT_QUOTES, 'UTF-8'); ?> Member
                </span>

                <p id="avatar-upload-status" class="avatar-upload-status" aria-live="polite"></p>
            </div>

            <nav class="profile-nav">
                <ul>
                    <li><a href="#my-flow" class="active">My Flow</a></li>
                    <li><a href="#access-pass">Access Pass</a></li>
                    <li><a href="#account-settings">Account Settings</a></li>
                    <li><a href="#password-settings">Password</a></li>
                    <li><a href="#membership-details">Membership &amp; Billing</a></li>
                    <li>
                        <form action="actions/action_logout.php" method="POST">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <button type="submit" class="text-danger">
                                Log Out
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </aside>

        <section class="profile-content">
            <?php if ($successMessage): ?>
                <p class="form-message form-success">
                    <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <p class="form-message form-error">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>

            <div id="my-flow" class="stats-grid" role="region">
                <div class="stat-card card">
                    <span class="stat-title">Current Streak</span>

                    <span class="stat-value">
                        <?php echo htmlspecialchars((string) ($user['streak'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>
                        <small>Days</small>
                    </span>
                </div>

                <div class="stat-card card">
                    <span class="stat-title">Classes Attended</span>

                    <span class="stat-value">
                        <?php echo htmlspecialchars((string) $attendedCount, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div class="stat-card card">
                    <span class="stat-title">Next Milestone</span>

                    <span class="stat-value">
                        <?php echo htmlspecialchars((string) $nextMilestone, ENT_QUOTES, 'UTF-8'); ?>
                        <small>Workouts</small>
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
                                <span><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <div>
                                <strong>Plan</strong>
                                <span><?php echo htmlspecialchars($user['plan_name'] ?? 'Basic', ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <div>
                                <strong>Access Code</strong>
                                <span><?php echo htmlspecialchars($memberAccessCode, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="qr-pass-box">
                        <img
                            src="<?php echo htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>"
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
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <input type="hidden" name="form_type" value="profile">

                    <fieldset>
                        <legend>Personal Information</legend>

                        <div class="form-group">
                            <label for="name">Full Name</label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="<?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>

                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>"
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
                                    <option value="hiit" <?php echo ($user['favorite_flow'] ?? '') === 'hiit' ? 'selected' : ''; ?>>
                                        HIIT &amp; Conditioning
                                    </option>

                                    <option value="mobility" <?php echo ($user['favorite_flow'] ?? '') === 'mobility' ? 'selected' : ''; ?>>
                                        Mobility &amp; Yoga
                                    </option>

                                    <option value="strength" <?php echo ($user['favorite_flow'] ?? '') === 'strength' ? 'selected' : ''; ?>>
                                        Strength Training
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="fitness-goal">Primary Goal</label>

                                <select id="fitness-goal" name="fitness-goal" class="custom-select">
                                    <option value="endurance" <?php echo ($user['primary_goal'] ?? '') === 'endurance' ? 'selected' : ''; ?>>
                                        Build Endurance
                                    </option>

                                    <option value="muscle" <?php echo ($user['primary_goal'] ?? '') === 'muscle' ? 'selected' : ''; ?>>
                                        Gain Muscle
                                    </option>

                                    <option value="weightloss" <?php echo ($user['primary_goal'] ?? '') === 'weightloss' ? 'selected' : ''; ?>>
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
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                    >

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
                <h2>Membership &amp; Billing</h2>
                <p class="text-muted">Review your active plan, billing details and mock payment method.</p>

                <div class="membership-details">
                    <div class="plan-info">
                        <span class="badge-gold">
                            <?php echo htmlspecialchars($user['plan_name'] ?? 'Basic', ENT_QUOTES, 'UTF-8'); ?> Member
                        </span>

                        <p class="price">
                            €<?php echo htmlspecialchars((string) ($user['plan_price'] ?? '0.00'), ENT_QUOTES, 'UTF-8'); ?><span>/month</span>
                        </p>

                        <?php if (!empty($user['next_billing_date'])): ?>
                            <p class="billing-date">
                                Next billing date:
                                <?php echo htmlspecialchars(date('M d, Y', strtotime($user['next_billing_date'])), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php endif; ?>

                        <div class="payment-summary-card">
                            <strong>Payment Method</strong>

                            <span>
                                <?php echo htmlspecialchars($paymentMethodLabel, ENT_QUOTES, 'UTF-8'); ?>

                                <?php if (!empty($user['card_last4'])): ?>
                                    ending in <?php echo htmlspecialchars($user['card_last4'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </span>

                            <a href="billing.php" class="btn-outline payment-link">
                                Manage Payment Method
                            </a>
                        </div>
                    </div>

                    <div class="plan-actions">
                        <form action="actions/action_upgrade_plan.php" method="POST" class="plan-upgrade-form">
                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                            >

                            <label for="plan_id">Change Membership Plan</label>

                            <select id="plan_id" name="plan_id" class="custom-select" required>
                                <option value="1" <?php echo (int) ($user['plan_id'] ?? 0) === 1 ? 'selected' : ''; ?>>
                                    Starter - €30/month
                                </option>

                                <option value="2" <?php echo (int) ($user['plan_id'] ?? 0) === 2 ? 'selected' : ''; ?>>
                                    Pro - €45/month
                                </option>

                                <option value="3" <?php echo (int) ($user['plan_id'] ?? 0) === 3 ? 'selected' : ''; ?>>
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

<?php include __DIR__ . '/templates/footer.php'; ?>