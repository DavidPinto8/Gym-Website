<?php 
    session_start();
    require_once 'database/db.php';
    
    // Ligar à BD e puxar as aulas e os nomes dos treinadores
    $db = getDatabaseConnection();
    $stmt = $db->prepare("
        SELECT classes.*, users.name AS trainer_name 
        FROM classes 
        LEFT JOIN trainers ON classes.trainer_id = trainers.id 
        LEFT JOIN users ON trainers.user_id = users.id 
        ORDER BY classes.start_time ASC
    ");
    $stmt->execute();
    $classes = $stmt->fetchAll();

    $pageTitle = "Classes | Flow Fitness";
    $currentPage = 'schedule';
    $extraCSS = 'schedule.css';
    include 'templates/header.php'; 
?>

    <main class="schedule-page">
        <div class="container schedule-layout">
            
            <aside class="filters">
                <h2 class="filters-title">Filter Classes</h2>
                <form class="filter-form">
                    <div class="form-group">
                        <label for="class-type">Class Type</label>
                        <select id="class-type">
                            <option value="all">All Disciplines</option>
                            <option value="yoga">Yoga</option>
                            <option value="hiit">HIIT</option>
                            <option value="strength">Strength</option>
                            <option value="cardio">Cardio</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="day-select">Day of Week</label>
                        <select id="day-select">
                            <option value="all">All Days</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="trainer-select">Trainer</label>
                        <select id="trainer-select">
                            <option value="all">All Trainers</option>
                            <option value="anna">Anna Costa</option>
                            <option value="mike">Mike Ferreira</option>
                            <option value="john">John Silva</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary btn-block">Apply Filters</button>
                </form>
            </aside>

            <section class="classes-section">
                <div class="section-header">
                    <h2>Class Schedule</h2>
                    <p>High Performance Training</p>
                </div>

                <div class="classes-grid">
                    <?php if (count($classes) > 0): ?>
                        <?php foreach ($classes as $class): 
                            // Formatar a data (ex: Monday • 08:00)
                            $timestamp = strtotime($class['start_time']);
                            $dayOfWeek = date('l', $timestamp);
                            $timeFormated = date('H:i', $timestamp);
                        ?>
                            <article class="class-card <?php echo htmlspecialchars($class['type']); ?>" 
                                     data-type="<?php echo htmlspecialchars($class['type']); ?>" 
                                     data-day="<?php echo strtolower($dayOfWeek); ?>">
                                <div class="card-overlay"></div>
                                <div class="card-content">
                                    <span class="class-tag"><?php echo ucfirst(htmlspecialchars($class['type'])); ?></span>
                                    <h3><?php echo htmlspecialchars($class['name']); ?></h3>
                                    <p class="class-info"><?php echo $dayOfWeek . ' • ' . $timeFormated; ?></p>
                                    <p class="class-trainer">Trainer: <?php echo htmlspecialchars($class['trainer_name'] ?? 'TBA'); ?></p>
                                    
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button type="button" class="btn-primary btn-block book-btn">Book Spot</button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn-outline btn-block text-center" style="display: block; margin-top: 1rem; color: var(--white); border-color: var(--white);">Login to Book</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No classes scheduled at the moment.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

<?php include 'templates/footer.php'; ?>