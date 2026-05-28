<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'database/db.php';
require_once 'includes/ui_helpers.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

$db = getDatabaseConnection();

$stmt = $db->query("
    SELECT
        reports.*,
        users.name AS member_name,
        users.email AS member_email
    FROM reports

    JOIN members
        ON reports.member_id = members.id

    JOIN users
        ON members.user_id = users.id

    ORDER BY
        CASE reports.status
            WHEN 'open' THEN 1
            WHEN 'in_progress' THEN 2
            WHEN 'resolved' THEN 3
        END,
        reports.created_at DESC
");

$reports = $stmt->fetchAll();

$pageTitle = "Manage Reports | Flow Fitness";
$currentPage = 'admin_reports';
$extraCSS = 'reports.css';

include 'templates/header.php';
?>

<main class="reports-page admin-reports-page">
    <section class="container reports-layout">
        <div class="reports-main">
            <div class="section-header">
                <h1>Manage Reports</h1>
                <p>Review member feedback and respond to reported issues.</p>
            </div>

            <?php render_alerts(); ?>

            <div class="reports-list">
                <?php if (count($reports) > 0): ?>
                    <?php foreach ($reports as $report): ?>
                        <article class="report-item admin-report-item">
                            <div class="report-item-header">
                                <div>
                                    <span class="report-category">
                                        <?php echo ucfirst(htmlspecialchars($report['category'])); ?>
                                    </span>

                                    <h2><?php echo htmlspecialchars($report['subject']); ?></h2>

                                    <p class="report-member">
                                        Submitted by <?php echo htmlspecialchars($report['member_name']); ?>
                                        · <?php echo htmlspecialchars($report['member_email']); ?>
                                    </p>
                                </div>

                                <span class="status-badge status-<?php echo htmlspecialchars($report['status']); ?>">
                                    <?php echo str_replace('_', ' ', htmlspecialchars($report['status'])); ?>
                                </span>
                            </div>

                            <p><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>

                            <?php if (!empty($report['member_response'])): ?>
                                <div class="member-response">
                                    <strong>Member response:</strong>
                                    <p><?php echo nl2br(htmlspecialchars($report['member_response'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ((int)($report['closed_by_member'] ?? 0) === 1): ?>
                                <p class="muted-text report-closed-note">
                                    This report was closed by the member.
                                </p>
                            <?php endif; ?>

                            <form action="actions/action_update_report.php" method="POST" class="admin-report-form">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report['id']); ?>">

                                <div class="form-group">
                                    <label for="status-<?php echo htmlspecialchars($report['id']); ?>">Status</label>

                                    <select id="status-<?php echo htmlspecialchars($report['id']); ?>" name="status" required>
                                        <option value="open" <?php echo $report['status'] === 'open' ? 'selected' : ''; ?>>
                                            Open
                                        </option>
                                        <option value="in_progress" <?php echo $report['status'] === 'in_progress' ? 'selected' : ''; ?>>
                                            In Progress
                                        </option>
                                        <option value="resolved" <?php echo $report['status'] === 'resolved' ? 'selected' : ''; ?>>
                                            Resolved
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="response-<?php echo htmlspecialchars($report['id']); ?>">Admin Response</label>

                                    <textarea id="response-<?php echo htmlspecialchars($report['id']); ?>" name="admin_response" rows="4"><?php echo htmlspecialchars($report['admin_response'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn-primary">
                                    Update Report
                                </button>
                            </form>

                            <small>
                                Last updated on <?php echo htmlspecialchars(date('F j, Y H:i', strtotime($report['updated_at']))); ?>
                            </small>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <article class="report-card">
                        <p class="muted-text">There are no reports yet.</p>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'templates/footer.php'; ?>