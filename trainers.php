<?php
session_start();

require_once 'database/db.php';
require_once 'includes/avatar.php';

$db = getDatabaseConnection();

$stmt = $db->query("
    SELECT
        trainers.id AS trainer_id,
        users.name,
        users.photo,
        trainers.bio,
        trainers.specializations,
        trainers.certifications
    FROM trainers
    JOIN users ON trainers.user_id = users.id
");

$trainers = $stmt->fetchAll();

$pageTitle = "Our Team | Flow Fitness";
$currentPage = 'trainers';
$extraCSS = 'trainers.css';

include 'templates/header.php';
?>

<main class="bg-light trainers-page">
    <div class="container">
        <section class="trainers-hero text-center">
            <span class="section-kicker">Flow Fitness Team</span>

            <h1 class="trainers-title">Meet Our Experts</h1>

            <p class="trainers-subtitle">
                Elite coaches, real experience, and classes built to keep you moving forward.
            </p>
        </section>

        <section class="trainers-grid" aria-label="Trainer profiles">
            <?php if (count($trainers) > 0): ?>
                <?php foreach ($trainers as $trainer):
                    $photoUrl = getUserAvatar(
                        $trainer['name'],
                        $trainer['photo'] ?? null,
                        200
                    );

                    $specs = array_filter(array_map('trim', explode(',', $trainer['specializations'])));
                    $mainSpec = $specs[0] ?? 'Fitness Expert';

                    $stmt_classes = $db->prepare("
                        SELECT DISTINCT name
                        FROM classes
                        WHERE trainer_id = ?
                        ORDER BY name
                    ");
                    $stmt_classes->execute([$trainer['trainer_id']]);
                    $classes_taught = $stmt_classes->fetchAll();

                    $modalId = 'trainer-modal-' . $trainer['trainer_id'];
                ?>
                    <article class="trainer-card">
                        <button
                            type="button"
                            class="trainer-card-button js-open-trainer-modal"
                            data-modal-target="<?php echo $modalId; ?>"
                            aria-haspopup="dialog"
                            aria-controls="<?php echo $modalId; ?>"
                        >
                            <span class="trainer-image-wrapper">
                                <img
                                    src="<?php echo $photoUrl; ?>"
                                    alt="<?php echo htmlspecialchars($trainer['name']); ?>"
                                    class="trainer-avatar"
                                >
                            </span>

                            <span class="trainer-card-body">
                                <span class="trainer-name">
                                    <?php echo htmlspecialchars($trainer['name']); ?>
                                </span>

                                <span class="trainer-specialization">
                                    <?php echo htmlspecialchars($mainSpec); ?>
                                </span>

                                <span class="trainer-card-cta">
                                    View profile
                                </span>
                            </span>
                        </button>
                    </article>

                    <div
                        id="<?php echo $modalId; ?>"
                        class="trainer-modal-overlay"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="<?php echo $modalId; ?>-title"
                        hidden
                    >
                        <div class="trainer-modal-content">
                            <button
                                type="button"
                                class="close-modal js-close-trainer-modal"
                                aria-label="Close trainer profile"
                            >
                                &times;
                            </button>

                            <div class="trainer-modal-header">
                                <img
                                    src="<?php echo $photoUrl; ?>"
                                    alt="<?php echo htmlspecialchars($trainer['name']); ?>"
                                    class="trainer-modal-avatar"
                                >

                                <span class="trainer-modal-role">
                                    <?php echo htmlspecialchars($mainSpec); ?>
                                </span>

                                <h2
                                    id="<?php echo $modalId; ?>-title"
                                    class="trainer-modal-name"
                                >
                                    <?php echo htmlspecialchars($trainer['name']); ?>
                                </h2>

                                <p class="trainer-bio">
                                    “<?php echo htmlspecialchars($trainer['bio']); ?>”
                                </p>
                            </div>

                            <?php if (count($specs) > 0): ?>
                                <div class="specializations-list">
                                    <?php foreach ($specs as $spec): ?>
                                        <span class="specialization-tag">
                                            <?php echo htmlspecialchars($spec); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <section class="trainer-info-box">
                                <h3 class="modal-section-title">Certifications</h3>

                                <p class="certifications-text">
                                    <?php echo htmlspecialchars($trainer['certifications']); ?>
                                </p>
                            </section>

                            <section class="trainer-info-box">
                                <h3 class="modal-section-title">Classes Taught</h3>

                                <?php if (count($classes_taught) > 0): ?>
                                    <div class="trainer-classes-grid">
                                        <?php foreach ($classes_taught as $class): ?>
                                            <span class="class-item">
                                                <?php echo htmlspecialchars($class['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="no-classes">
                                        No classes currently scheduled.
                                    </p>
                                <?php endif; ?>
                            </section>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center no-trainers">
                    We are currently recruiting new talent. Check back soon!
                </p>
            <?php endif; ?>
        </section>
    </div>
</main>

<script src="js/trainers.js"></script>

<?php include 'templates/footer.php'; ?>