<?php 
    session_start();
    require_once 'database/db.php';
    require_once 'includes/avatar.php';
    
    $db = getDatabaseConnection();

    $stmt = $db->query("
        SELECT trainers.id AS trainer_id, users.name, users.photo, trainers.bio, trainers.specializations, trainers.certifications 
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
        <div class="section-header text-center">
            <h1 class="trainers-title">Meet Our Experts</h1>
            <p class="text-muted">Click on a coach to view their full profile and classes.</p>
        </div>

        <div class="trainers-grid">
            <?php if (count($trainers) > 0): ?>
                <?php foreach ($trainers as $trainer): 
                    $photoUrl = getUserAvatar(
                        $trainer['name'],
                        $trainer['photo'] ?? null,
                        200
                    );

                    $specs = explode(',', $trainer['specializations']);
                    $mainSpec = $specs[0] ?? 'Fitness Expert';
                ?>
                    <article class="card text-center trainer-mini-card" onclick="openTrainerModal('modal-<?php echo $trainer['trainer_id']; ?>')">
                        <img 
                            src="<?php echo $photoUrl; ?>" 
                            alt="<?php echo htmlspecialchars($trainer['name']); ?>" 
                            class="trainer-avatar"
                        >

                        <h3 class="trainer-name">
                            <?php echo htmlspecialchars($trainer['name']); ?>
                        </h3>

                        <span class="trainer-specialization">
                            <?php echo htmlspecialchars(trim($mainSpec)); ?>
                        </span>
                    </article>

                    <div 
                        id="modal-<?php echo $trainer['trainer_id']; ?>" 
                        class="trainer-modal-overlay" 
                        onclick="closeTrainerModal(event, this)"
                    >
                        <div class="trainer-modal-content card">
                            <span 
                                class="close-modal" 
                                onclick="forceCloseModal('modal-<?php echo $trainer['trainer_id']; ?>')"
                            >
                                &times;
                            </span>

                            <div class="trainer-modal-header">
                                <img 
                                    src="<?php echo $photoUrl; ?>" 
                                    alt="<?php echo htmlspecialchars($trainer['name']); ?>" 
                                    class="trainer-modal-avatar"
                                >

                                <h2 class="trainer-modal-name">
                                    <?php echo htmlspecialchars($trainer['name']); ?>
                                </h2>

                                <p class="text-muted trainer-bio">
                                    "<?php echo htmlspecialchars($trainer['bio']); ?>"
                                </p>
                            </div>

                            <div class="specializations-list">
                                <?php foreach ($specs as $spec): ?>
                                    <span class="specialization-tag">
                                        <?php echo htmlspecialchars(trim($spec)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>

                            <div class="certifications-box">
                                <h4 class="modal-section-title">Certifications</h4>
                                <p class="certifications-text">
                                    <?php echo htmlspecialchars($trainer['certifications']); ?>
                                </p>
                            </div>

                            <?php
                                $stmt_classes = $db->prepare("SELECT DISTINCT name FROM classes WHERE trainer_id = ?");
                                $stmt_classes->execute([$trainer['trainer_id']]);
                                $classes_taught = $stmt_classes->fetchAll();
                            ?>

                            <div class="classes-section">
                                <h4 class="modal-section-title">Classes Taught</h4>

                                <?php if (count($classes_taught) > 0): ?>
                                    <div class="classes-grid">
                                        <?php foreach ($classes_taught as $class): ?>
                                            <div class="class-item">
                                                <?php echo htmlspecialchars($class['name']); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="no-classes">No classes currently scheduled.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center no-trainers">
                    We are currently recruiting new talent. Check back soon!
                </p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    function openTrainerModal(modalId) {
        document.getElementById(modalId).classList.add('active');
        document.body.style.overflow = 'hidden'; 
    }

    function forceCloseModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        document.body.style.overflow = 'auto'; 
    }

    function closeTrainerModal(event, modalElement) {
        if (event.target === modalElement) {
            modalElement.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
</script>

<?php include 'templates/footer.php'; ?>