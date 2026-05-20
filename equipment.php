<?php

session_start();

$pageTitle = "Equipment | Flow Fitness";
$currentPage = "equipment";
$extraCSS = "equipment.css";

include 'templates/header.php';

?>

<main class="equipment-page">

    <div class="container">

        <div class="equipment-header">

            <h1>
                Equipment Availability
            </h1>

            <p>
                Check equipment usage in real time
            </p>

        </div>

        <section class="equipment-filters">

            <input
                type="text"
                id="equipment-search"
                placeholder="Search equipment..."
            >

            <select id="equipment-type">

                <option value="all">
                    All Types
                </option>

                <option value="cardio">
                    Cardio
                </option>

                <option value="weights">
                    Weights
                </option>

                <option value="machines">
                    Machines
                </option>

                <option value="functional">
                    Functional
                </option>

            </select>

        </section>

        <section
            id="equipment-grid"
            class="equipment-grid"
        >

            <p>
                Loading equipment...
            </p>

        </section>

    </div>

</main>

<script src="js/equipment.js"></script>

<?php include 'templates/footer.php'; ?>