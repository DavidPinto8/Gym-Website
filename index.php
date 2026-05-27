<?php
session_start();

$pageTitle = "Flow Fitness - Find Your Rhythm";
$currentPage = 'home';
$extraCSS = 'home.css';

include 'templates/header.php';
?>

<main>
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-overlay"></div>

        <div class="hero-content container">
            <span class="hero-kicker">Premium Training Club</span>

            <h1 id="hero-title">Find Your Rhythm.</h1>

            <p>
                Train harder, recover smarter, and stay connected with real-time classes,
                expert coaches and a gym experience built around your flow.
            </p>

            <div class="hero-actions">
                <a href="#plans" class="btn-primary btn-large">View Memberships</a>
                <a href="schedule.php" class="btn-secondary btn-large">Explore Classes</a>
            </div>

            <div class="hero-stats" aria-label="Flow Fitness highlights">
                <div>
                    <strong>15+</strong>
                    <span>Weekly Classes</span>
                </div>

                <div>
                    <strong>8</strong>
                    <span>Expert Trainers</span>
                </div>

                <div>
                    <strong>24/7</strong>
                    <span>Pro Access</span>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="about-section container">
        <div class="section-header text-center">
            <span class="section-kicker">Why Flow Fitness</span>
            <h2>Movement is Everything</h2>
            <p>
                A high-performance gym built for consistency, intensity and progress.
            </p>
        </div>

        <div class="about-grid">
            <div class="about-text">
                <h3>Designed to keep you moving forward.</h3>

                <p>
                    Flow Fitness combines premium equipment, expert coaching and digital tools
                    that help members train with less friction and more confidence.
                </p>

                <p>
                    Browse classes, check equipment availability, join waitlists, track attendance
                    and stay connected to the community from one clean platform.
                </p>

                <div class="feature-list">
                    <span>Real-time equipment availability</span>
                    <span>Class booking and waitlists</span>
                    <span>Trainer profiles and reviews</span>
                </div>
            </div>

            <figure class="about-image">
                <img src="assets/gym-floor.jpeg" alt="Our premium gym floor with modern equipment">
                <figcaption>Main training area at Flow Fitness.</figcaption>
            </figure>
        </div>
    </section>

    <section class="features-strip bg-light" aria-label="Main platform features">
        <div class="container features-grid">
            <article class="feature-card">
                <span class="feature-icon">⚡</span>
                <h3>Live Availability</h3>
                <p>Check equipment status before stepping onto the floor.</p>
            </article>

            <article class="feature-card">
                <span class="feature-icon">🔥</span>
                <h3>Class Energy</h3>
                <p>Book HIIT, strength, yoga and cardio sessions in seconds.</p>
            </article>

            <article class="feature-card">
                <span class="feature-icon">🏆</span>
                <h3>Expert Coaching</h3>
                <p>Train with specialized instructors and review your sessions.</p>
            </article>
        </div>
    </section>

    <section id="plans" class="plans-section" aria-labelledby="plans-title">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-kicker">Memberships</span>
                <h2 id="plans-title">Choose Your Plan</h2>
                <p>Flexible memberships designed for every rhythm.</p>
            </div>

            <div class="cards-grid">
                <article class="card plan-card">
                    <div class="card-header">
                        <h3>Starter</h3>
                        <p class="price">€30<span>/month</span></p>
                    </div>

                    <ul class="plan-features">
                        <li>Access to main gym floor</li>
                        <li>Free locker usage</li>
                        <li>Off-peak hours access</li>
                        <li>Basic schedule access</li>
                    </ul>

                    <a href="register.php?plan=starter" class="btn-outline btn-block">
                        Choose Starter
                    </a>
                </article>

                <article class="card plan-card highlighted">
                    <div class="card-badge">Most Popular</div>

                    <div class="card-header">
                        <h3>Pro</h3>
                        <p class="price">€45<span>/month</span></p>
                    </div>

                    <ul class="plan-features">
                        <li>Everything in Starter</li>
                        <li>24/7 access</li>
                        <li>All group fitness classes</li>
                        <li>Equipment availability tracking</li>
                        <li>Priority schedule access</li>
                    </ul>

                    <a href="register.php?plan=pro" class="btn-primary btn-block">
                        Choose Pro
                    </a>
                </article>

                <article class="card plan-card">
                    <div class="card-header">
                        <h3>Elite</h3>
                        <p class="price">€70<span>/month</span></p>
                    </div>

                    <ul class="plan-features">
                        <li>Everything in Pro</li>
                        <li>Weekly personal training</li>
                        <li>Priority class booking</li>
                        <li>Premium amenities</li>
                        <li>Exclusive events and workshops</li>
                    </ul>

                    <a href="register.php?plan=elite" class="btn-outline btn-block">
                        Choose Elite
                    </a>
                </article>
            </div>
        </div>
    </section>

    <section id="testimonials" class="testimonials-section bg-light" aria-labelledby="testimonials-title">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-kicker">Community</span>
                <h2 id="testimonials-title">The Flow Community</h2>
                <p>Members who bring the energy every single day.</p>
            </div>

            <div class="cards-grid">
                <article class="card testimonial-card">
                    <p class="quote">
                        “The atmosphere here is unmatched. It feels like a professional training facility.”
                    </p>

                    <div class="author">
                        <h3>Marcus T.</h3>
                        <p>Elite Member</p>
                    </div>
                </article>

                <article class="card testimonial-card">
                    <p class="quote">
                        “The morning HIIT sessions completely changed my routine. The instructors are phenomenal.”
                    </p>

                    <div class="author">
                        <h3>Sarah L.</h3>
                        <p>Pro Member</p>
                    </div>
                </article>

                <article class="card testimonial-card">
                    <p class="quote">
                        “24/7 access, excellent equipment and a real sense of community. Flow became my second home.”
                    </p>

                    <div class="author">
                        <h3>David K.</h3>
                        <p>Elite Member</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="faq" class="faq-section container" aria-labelledby="faq-title">
        <div class="faq-inner">
            <div class="section-header text-center">
                <span class="section-kicker">Questions</span>
                <h2 id="faq-title">Frequently Asked Questions</h2>
            </div>

            <div class="faq-list">
                <details class="faq-item">
                    <summary>What happens if a class is full?</summary>
                    <p>
                        Members can join the waitlist. When someone cancels, the next member is automatically promoted.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>Can I report problems with equipment or classes?</summary>
                    <p>
                        Yes. Members can submit reports about equipment, classes, trainers or other issues, and admins can respond directly.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>How do reviews work?</summary>
                    <p>
                        Reviews are only available after a trainer confirms that the member attended the class.
                    </p>
                </details>
            </div>
        </div>
    </section>
</main>

<?php include 'templates/footer.php'; ?>