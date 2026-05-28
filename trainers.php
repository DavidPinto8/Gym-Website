<?php

session_start();

require_once __DIR__ . '/database/db.php';
require_once __DIR__ . '/includes/avatar.php';

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
    INNER JOIN users
        ON trainers.user_id = users.id
    ORDER BY users.name ASC
");

$trainers = $stmt->fetchAll();

$pageTitle = 'Our Team | Flow Fitness';
$currentPage = 'trainers';
$extraCSS = 'trainers.css';

include __DIR__ . '/templates/header.php';
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
                <?php foreach ($trainers as $trainer): ?>
                    <?php
                        $trainerId = (int) $trainer['trainer_id'];

                        $photoUrl = getUserAvatar(
                            $trainer['name'],
                            $trainer['photo'] ?? null,
                            200
                        );

                        $specializationsText = $trainer['specializations'] ?? '';

                        $specializations = array_filter(
                            array_map('trim', explode(',', $specializationsText))
                        );

                        $mainSpecialization = $specializations[0] ?? 'Fitness Expert';

                        $stmt = $db->prepare("
                            SELECT DISTINCT name
                            FROM classes
                            WHERE trainer_id = ?
                            ORDER BY name ASC
                        ");

                        $stmt->execute([$trainerId]);

                        $classesTaught = $stmt->fetchAll();
                        $modalId = 'trainer-modal-' . $trainerId;
                    ?>

                    <article class="trainer-card">
                        <button
                            type="button"
                            class="trainer-card-button js-open-trainer-modal"
                            data-modal-target="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>"
                            aria-haspopup="dialog"
                            aria-controls="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <span class="trainer-image-wrapper">
                                <img
                                    src="<?php echo htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="trainer-avatar"
                                >
                            </span>

                            <span class="trainer-card-body">
                                <span class="trainer-name">
                                    <?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <span class="trainer-specialization">
                                    <?php echo htmlspecialchars($mainSpecialization, ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <span class="trainer-card-cta">
                                    View profile
                                </span>
                            </span>
                        </button>
                    </article>

                    <div
                        id="<?php echo htmlspecialchars($modalId, ENT_QUOTES, 'UTF-8'); ?>"
                        class="trainer-modal-overlay"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="<?php echo htmlspecialchars($modalId . '-title', ENT_QUOTES, 'UTF-8'); ?>"
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
                                    src="<?php echo htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="trainer-modal-avatar"
                                >

                                <span class="trainer-modal-role">
                                    <?php echo htmlspecialchars($mainSpecialization, ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <h2
                                    id="<?php echo htmlspecialchars($modalId . '-title', ENT_QUOTES, 'UTF-8'); ?>"
                                    class="trainer-modal-name"
                                >
                                    <?php echo htmlspecialchars($trainer['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h2>

                                <?php if (!empty($trainer['bio'])): ?>
                                    <p class="trainer-bio">
                                        “<?php echo htmlspecialchars($trainer['bio'], ENT_QUOTES, 'UTF-8'); ?>”
                                    </p>
                                <?php endif; ?>
                            </div>

                            <?php if (count($specializations) > 0): ?>
                                <div class="specializations-list">
                                    <?php foreach ($specializations as $specialization): ?>
                                        <span class="specialization-tag">
                                            <?php echo htmlspecialchars($specialization, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($trainer['certifications'])): ?>
                                <section class="trainer-info-box">
                                    <h3 class="modal-section-title">Certifications</h3>

                                    <p class="certifications-text">
                                        <?php echo htmlspecialchars($trainer['certifications'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </section>
                            <?php endif; ?>

                            <section class="trainer-info-box">
                                <h3 class="modal-section-title">Classes Taught</h3>

                                <?php if (count($classesTaught) > 0): ?>
                                    <div class="trainer-classes-grid">
                                        <?php foreach ($classesTaught as $class): ?>
                                            <span class="class-item">
                                                <?php echo htmlspecialchars($class['name'], ENT_QUOTES, 'UTF-8'); ?>
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

<?php include __DIR__ . '/templates/footer.php'; ?>