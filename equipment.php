<?php
session_start();

$pageTitle = "Equipment | Flow Fitness";
$currentPage = "equipment";
$extraCSS = "equipment.css";

include 'templates/header.php';
?>

<main class="equipment-page">
    <div class="container">
        <section class="equipment-hero">
            <span class="equipment-eyebrow">Live Floor Status</span>

            <h1>Equipment Availability</h1>

            <p>
                Check real-time equipment availability before starting your workout.
            </p>
        </section>

        <section class="equipment-filters" aria-label="Equipment filters">
            <div class="filter-group">
                <label for="equipment-search">Search equipment</label>

                <input
                    type="text"
                    id="equipment-search"
                    placeholder="Search by name or location..."
                >
            </div>

            <div class="filter-group">
                <label for="equipment-type">Equipment type</label>

                <select id="equipment-type">
                    <option value="all">All Types</option>
                    <option value="cardio">Cardio</option>
                    <option value="weights">Weights</option>
                    <option value="machines">Machines</option>
                    <option value="functional">Functional</option>
                </select>
            </div>
        </section>

        <section class="equipment-summary" aria-label="Equipment availability summary">
            <article class="equipment-summary-card">
                <strong id="equipment-total-count">—</strong>
                <span>Items Listed</span>
            </article>

            <article class="equipment-summary-card">
                <strong id="equipment-available-count">—</strong>
                <span>Units Available</span>
            </article>

            <article class="equipment-summary-card">
                <strong id="equipment-critical-count">—</strong>
                <span>Critical Items</span>
            </article>
        </section>

        <section
            id="equipment-grid"
            class="equipment-grid"
            aria-live="polite"
        >
            <p class="equipment-empty-state">
                Loading equipment...
            </p>
        </section>
    </div>
</main>

<script src="js/equipment.js"></script>

<?php include 'templates/footer.php'; ?>