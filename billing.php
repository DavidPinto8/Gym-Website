<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'database/db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'member') {
    header('Location: login.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT
        users.name,
        members.payment_method,
        members.card_last4,
        members.next_billing_date,
        plans.name AS plan_name,
        plans.price AS plan_price
    FROM users
    JOIN members ON users.id = members.user_id
    LEFT JOIN plans ON members.plan_id = plans.id
    WHERE users.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$billing = $stmt->fetch();

if (!$billing) {
    header('Location: profile.php?error=update_failed');
    exit();
}

$successMessages = [
    'payment_updated' => 'Payment method updated successfully.'
];

$errorMessages = [
    'invalid_input' => 'Please select a valid payment method and provide 4 digits.',
    'update_failed' => 'Could not update payment method.',
    'invalid_csrf' => 'Invalid security token.',
    'missing_payment_method' => 'Please add a payment method before changing your plan.'
];

$successMessage = null;
$errorMessage = null;

if (isset($_GET['success'], $successMessages[$_GET['success']])) {
    $successMessage = $successMessages[$_GET['success']];
}

if (isset($_GET['error'], $errorMessages[$_GET['error']])) {
    $errorMessage = $errorMessages[$_GET['error']];
}

$paymentLabels = [
    'visa' => 'Visa',
    'mastercard' => 'Mastercard',
    'paypal' => 'PayPal',
    'apple_pay' => 'Apple Pay',
    'google_pay' => 'Google Pay'
];

$currentPaymentLabel = $paymentLabels[$billing['payment_method'] ?? ''] ?? 'No payment method set';

$pageTitle = 'Billing | Flow Fitness';
$currentPage = 'billing';
$extraCSS = 'profile.css';

include 'templates/header.php';
?>

<main class="profile-page bg-light">
    <div class="container billing-layout">
        <section class="card form-container billing-card">
            <span class="access-pass-kicker">Billing</span>

            <h1>Payment Method</h1>

            <p class="text-muted">
                Manage your payment method for membership billing.
            </p>

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

            <div class="billing-summary">
                <div>
                    <strong>Current Plan</strong>
                    <span>
                        <?php echo htmlspecialchars($billing['plan_name'] ?? 'Basic'); ?>
                        · €<?php echo htmlspecialchars($billing['plan_price'] ?? '0.00'); ?>/month
                    </span>
                </div>

                <div>
                    <strong>Current Payment</strong>
                    <span>
                        <?php echo htmlspecialchars($currentPaymentLabel); ?>

                        <?php if (!empty($billing['card_last4'])): ?>
                            ending in <?php echo htmlspecialchars($billing['card_last4']); ?>
                        <?php endif; ?>
                    </span>
                </div>

                <?php if (!empty($billing['next_billing_date'])): ?>
                    <div>
                        <strong>Next Billing Date</strong>
                        <span>
                            <?php echo htmlspecialchars(date('M d, Y', strtotime($billing['next_billing_date']))); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <form action="actions/action_update_payment_method.php" method="POST" class="profile-form billing-form">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                >

                <fieldset>
                    <legend>Payment Details</legend>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="payment-method">Payment Method</label>

                            <select id="payment-method" name="payment_method" class="custom-select" required>
                                <option value="visa" <?php echo ($billing['payment_method'] ?? '') === 'visa' ? 'selected' : ''; ?>>
                                    Visa
                                </option>

                                <option value="mastercard" <?php echo ($billing['payment_method'] ?? '') === 'mastercard' ? 'selected' : ''; ?>>
                                    Mastercard
                                </option>

                                <option value="paypal" <?php echo ($billing['payment_method'] ?? '') === 'paypal' ? 'selected' : ''; ?>>
                                    PayPal
                                </option>

                                <option value="apple_pay" <?php echo ($billing['payment_method'] ?? '') === 'apple_pay' ? 'selected' : ''; ?>>
                                    Apple Pay
                                </option>

                                <option value="google_pay" <?php echo ($billing['payment_method'] ?? '') === 'google_pay' ? 'selected' : ''; ?>>
                                    Google Pay
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="card-last4">Card Last 4 Digits</label>

                            <input
                                type="text"
                                id="card-last4"
                                name="card_last4"
                                value="<?php echo htmlspecialchars($billing['card_last4'] ?? ''); ?>"
                                maxlength="4"
                                pattern="[0-9]{4}"
                                placeholder="4242"
                                required
                            >
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions">
                    <a href="profile.php#membership-details" class="btn-outline">
                        Back to Profile
                    </a>

                    <button type="submit" class="btn-primary btn-large">
                        Save Payment Method
                    </button>
                </div>
            </form>
        </section>
    </div>
</main>

<?php include 'templates/footer.php'; ?>