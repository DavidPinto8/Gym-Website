<?php 
    session_start();
    require_once 'database/db.php';
    
    $db = getDatabaseConnection();

    $stmt = $db->query("
        SELECT trainers.id AS trainer_id, users.name, users.photo, trainers.bio, trainers.specializations, trainers.certifications 
        FROM trainers 
        JOIN users ON trainers.user_id = users.id
    ");
    $trainers = $stmt->fetchAll();

    $pageTitle = "Our Team | Flow Fitness";
    $currentPage = 'trainers';
    include 'templates/header.php'; 
?>

<style>
    .trainers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .trainer-mini-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        padding: 1.5rem;
    }

    .trainer-mini-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: var(--gold);
    }

    /* O Modal (Fundo Escuro) */
    .trainer-modal-overlay {
        display: none; /* Escondido por defeito */
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* O Cartão Expandido dentro do Modal */
    .trainer-modal-content {
        background-color: var(--white);
        width: 90%;
        max-width: 600px;
        border-radius: var(--radius-md);
        padding: 2.5rem;
        position: relative;
        transform: translateY(20px);
        transition: transform 0.3s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    .trainer-modal-overlay.active {
        display: flex;
        opacity: 1;
    }

    .trainer-modal-overlay.active .trainer-modal-content {
        transform: translateY(0);
    }

    .close-modal {
        position: absolute;
        top: 1rem;
        right: 1.5rem;
        font-size: 2rem;
        color: var(--black);
        cursor: pointer;
        line-height: 1;
        transition: color 0.2s;
    }

    .close-modal:hover {
        color: var(--gold);
    }
</style>

    <main class="bg-light" style="padding: 4rem 0; min-height: 80vh;">
        <div class="container">
            <div class="section-header text-center">
                <h1 style="color: var(--black); font-family: var(--font-heading); margin-bottom: 1rem;">Meet Our Experts</h1>
                <p class="text-muted">Click on a coach to view their full profile and classes.</p>
            </div>

            <div class="trainers-grid">
                <?php if (count($trainers) > 0): ?>
                    <?php foreach ($trainers as $trainer): 
                        // Gerador de Avatar Dinâmico se não houver foto (Iniciais Douradas em fundo Preto)
                        $photoUrl = !empty($trainer['photo']) 
                            ? htmlspecialchars($trainer['photo']) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($trainer['name']) . '&background=1a1a1a&color=d4af37&size=200&bold=true';
                    ?>
                        <article class="card text-center trainer-mini-card" onclick="openTrainerModal('modal-<?php echo $trainer['trainer_id']; ?>')">
                            <img src="<?php echo $photoUrl; ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" 
                                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 2px solid var(--gold);">
                            <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($trainer['name']); ?></h3>
                            
                            <?php 
                                $specs = explode(',', $trainer['specializations']);
                                $mainSpec = $specs[0] ?? 'Fitness Expert';
                            ?>
                            <span style="color: var(--gold); font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">
                                <?php echo htmlspecialchars(trim($mainSpec)); ?>
                            </span>
                        </article>

                        <div id="modal-<?php echo $trainer['trainer_id']; ?>" class="trainer-modal-overlay" onclick="closeTrainerModal(event, this)">
                            <div class="trainer-modal-content card">
                                <span class="close-modal" onclick="forceCloseModal('modal-<?php echo $trainer['trainer_id']; ?>')">&times;</span>
                                
                                <div style="text-align: center; margin-bottom: 2rem;">
                                    <img src="<?php echo $photoUrl; ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" 
                                         style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 1rem; border: 3px solid var(--gold);">
                                    <h2 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($trainer['name']); ?></h2>
                                    <p class="text-muted" style="font-style: italic;">"<?php echo htmlspecialchars($trainer['bio']); ?>"</p>
                                </div>

                                <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2rem;">
                                    <?php foreach($specs as $spec): ?>
                                        <span style="background-color: var(--black); color: var(--gold); padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">
                                            <?php echo htmlspecialchars(trim($spec)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>

                                <div style="background-color: #f9f9f9; padding: 1.5rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
                                    <h4 style="font-size: 0.95rem; color: var(--black); margin-bottom: 0.5rem; text-transform: uppercase;">Certifications</h4>
                                    <p style="font-size: 0.95rem; color: #555;"><?php echo htmlspecialchars($trainer['certifications']); ?></p>
                                </div>

                                <?php
                                    $stmt_classes = $db->prepare("SELECT DISTINCT name FROM classes WHERE trainer_id = ?");
                                    $stmt_classes->execute([$trainer['trainer_id']]);
                                    $classes_taught = $stmt_classes->fetchAll();
                                ?>
                                
                                <div>
                                    <h4 style="font-size: 0.95rem; color: var(--black); margin-bottom: 1rem; text-transform: uppercase;">Classes Taught</h4>
                                    <?php if (count($classes_taught) > 0): ?>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                            <?php foreach ($classes_taught as $class): ?>
                                                <div style="padding: 0.5rem; background: #fff; border: 1px solid #eee; border-radius: 4px; font-size: 0.9rem; color: #333; border-left: 3px solid var(--gold);">
                                                    <?php echo htmlspecialchars($class['name']); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p style="font-size: 0.9rem; color: #999;">No classes currently scheduled.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center" style="grid-column: 1 / -1;">We are currently recruiting new talent. Check back soon!</p>
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