<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->prepare("
    SELECT id
    FROM members
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: profile.php?error=Only members can submit reports.');
    exit();
}

$stmt = $db->prepare("
    SELECT *
    FROM reports
    WHERE member_id = ?
    ORDER BY
        CASE status
            WHEN 'open' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved' THEN 3
        END,
        created_at DESC
");
$stmt->execute([$member['id']]);
$reports = $stmt->fetchAll();

$pageTitle = "Reports | Flow Fitness";
$currentPage = 'reports';
$extraCSS = 'reports.css';

include 'templates/header.php';
?>

<main class="reports-page">
    <section class="container reports-layout">
        <div class="reports-main">
            <div class="section-header">
                <h1>Issue Reports</h1>
                <p>Report equipment problems, class issues, or anything that needs attention.</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="schedule-alert success-alert">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $_GET['success'])); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="schedule-alert error-alert">
                    <?php echo htmlspecialchars(str_replace('_', ' ', $_GET['error'])); ?>
                </div>
            <?php endif; ?>

            <article class="report-card">
                <h2>Submit a Report</h2>

                <form action="actions/action_submit_report.php" method="POST" class="report-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="form-group">
                        <label for="category">Category</label>

                        <select id="category" name="category" required>
                            <option value="equipment">Equipment</option>
                            <option value="class">Class</option>
                            <option value="trainer">Trainer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>

                        <input id="subject" name="subject" type="text" maxlength="120" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>

                        <textarea id="description" name="description" rows="6" required></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        Submit Report
                    </button>
                </form>
            </article>

            <article class="report-card">
                <h2>Your Reports</h2>

                <?php if (count($reports) > 0): ?>
                    <div class="reports-list">
                        <?php foreach ($reports as $report): ?>
                            <article class="report-item">
                                <div class="report-item-header">
                                    <div>
                                        <span class="report-category">
                                            <?php echo ucfirst(htmlspecialchars($report['category'])); ?>
                                        </span>

                                        <h3><?php echo htmlspecialchars($report['subject']); ?></h3>
                                    </div>

                                    <span class="status-badge status-<?php echo htmlspecialchars($report['status']); ?>">
                                        <?php echo str_replace('_', ' ', htmlspecialchars($report['status'])); ?>
                                    </span>
                                </div>

                                <p><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>

                                <?php if (!empty($report['admin_response'])): ?>
                                    <div class="admin-response">
                                        <strong>Admin response:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($report['admin_response'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($report['member_response'])): ?>
                                    <div class="member-response">
                                        <strong>Your response:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($report['member_response'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($report['status'] !== 'resolved'): ?>
                                    <form action="actions/action_member_update_report.php" method="POST" class="member-report-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                        <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report['id']); ?>">

                                        <div class="form-group">
                                            <label for="member-response-<?php echo htmlspecialchars($report['id']); ?>">
                                                Reply to admin
                                            </label>

                                            <textarea
                                                id="member-response-<?php echo htmlspecialchars($report['id']); ?>"
                                                name="member_response"
                                                rows="3"
                                                placeholder="Add more context or answer the admin..."
                                            ><?php echo htmlspecialchars($report['member_response'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="report-actions">
                                            <button type="submit" name="report_action" value="reply" class="btn-primary">
                                                Send Response
                                            </button>

                                            <button type="submit" name="report_action" value="close" class="btn-outline">
                                                Close Report
                                            </button>
                                        </div>
                                    </form>
                                <?php elseif ((int)($report['closed_by_member'] ?? 0) === 1): ?>
                                    <p class="muted-text report-closed-note">
                                        You closed this report.
                                    </p>
                                <?php endif; ?>

                                <small>
                                    Submitted on <?php echo htmlspecialchars(date('F j, Y H:i', strtotime($report['created_at']))); ?>
                                </small>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted-text">
                        You have not submitted any reports yet.
                    </p>
                <?php endif; ?>
            </article>
        </div>
    </section>
</main>

<?php include 'templates/footer.php'; ?>