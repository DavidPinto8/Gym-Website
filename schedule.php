<?php 
session_start();

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
                        <option value="other">Other</option>
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
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary btn-block">
                    Apply Filters
                </button>
            </form>
        </aside>

        <section class="classes-section">
            <div class="section-header">
                <h2>Class Schedule</h2>
                <p>High Performance Training</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="schedule-alert success-alert">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="schedule-alert error-alert">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div class="classes-grid">
                <p>Loading classes...</p>
            </div>
        </section>
    </div>
</main>

<script>
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const userRole = "<?php echo $_SESSION['role'] ?? ''; ?>";
</script>

<script src="js/schedule.js"></script>

<?php include 'templates/footer.php'; ?>