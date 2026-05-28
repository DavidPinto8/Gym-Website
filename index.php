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

            <?php
                $avatarJalen = getUserAvatar('Jalen B.', 'avatar-jalen.jpg', 96);
                $avatarSarah = getUserAvatar('Sarah L.', null, 96);
                $avatarDavid = getUserAvatar('David K.', null, 96);
            ?>

            <div class="cards-grid">
                <article class="card testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="quote">
                        "In my line of work, if you lose your rhythm, you lose the game. Flow Fitness is where I come to lock in during the off-season. The facility is world-class, the environment forces you to elevate your game, and the staff treats every member like a professional athlete. If you're serious about finding your rhythm and pushing past your limits, this is the only place to be. Every day here is a step toward the next milestone."
                    </p>

                    <div class="author">
                        <img src="<?php echo htmlspecialchars($avatarJalen, ENT_QUOTES, 'UTF-8'); ?>" alt="Jalen B." class="testimonial-avatar">
                        <div class="testimonial-meta">
                            <h3>Jalen Brunson</h3>
                            <p>NY Knicks Player</p>
                            <p>Elite Member</p>
                        </div>
                    </div>
                </article>

                <article class="card testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="quote">
                        "The morning sessions here are pure energy! The coaches are phenomenal, and being able to log my attendance streaks and leave class reviews keeps me accountable. It’s a beautifully polished fitness community."
                    </p>

                    <div class="author">
                        <img src="<?php echo htmlspecialchars($avatarSarah, ENT_QUOTES, 'UTF-8'); ?>" alt="Sarah L." class="testimonial-avatar">
                        <div class="testimonial-meta">
                            <h3>Sarah L.</h3>
                            <p>Pro Member</p>
                        </div>
                    </div>
                </article>

                <article class="card testimonial-card">
                    <div class="testimonial-stars">★★★★★</div>
                    <p class="quote">
                        "From 24/7 access and top-tier equipment to the seamless member portal, Flow Fitness sets a new standard. Upgrading my membership plan and updating billing info is smooth and instant, letting me focus on my goals."
                    </p>

                    <div class="author">
                        <img src="<?php echo htmlspecialchars($avatarDavid, ENT_QUOTES, 'UTF-8'); ?>" alt="David K." class="testimonial-avatar">
                        <div class="testimonial-meta">
                            <h3>David K.</h3>
                            <p>Elite Member</p>
                        </div>
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
                    <summary>How do I scan into the gym when I arrive?</summary>
                    <p>
                        Once you're logged into your account, a personalized, dynamic Gym Entry QR Code is generated on your profile page. Just hold it up to the scanner at our front desk and you're good to train!
                    </p>
                </details>

                <details class="faq-item">
                    <summary>What happens if the class I want to join is completely full?</summary>
                    <p>
                        Don't sweat it! You can join the waitlist with a single tap, and we'll show you exactly where you stand in line. If another member cancels their spot, our system will automatically slide you into the class list and notify you right away.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>Can I upgrade or downgrade my membership later on?</summary>
                    <p>
                        Of course! We know life changes, so your training options can too. You can easily switch between our Starter, Pro, and Elite plans directly from your profile settings. Just make sure you've added a payment method in your billing settings first, and the billing cycle will adjust automatically.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>How do I share feedback or report a broken machine?</summary>
                    <p>
                        We take our training environment seriously! If you notice a machine is out of order or want to share feedback about a class, just head to your Reports dashboard and let us know. Our admin team reads every single ticket, updates you on our progress, and alerts you as soon as the issue is resolved.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>How early can I book a class, and is there a cancellation fee?</summary>
                    <p>
                        You can book classes throughout the week as soon as the weekly schedule goes live! If your plans change, we kindly ask you to cancel your spot as early as possible so someone on the waitlist can take your place. There are no cancellation fees! We just love keeping our classes full of energy!
                    </p>
                </details>

                <details class="faq-item">
                    <summary>Why can I only review classes after I have attended them?</summary>
                    <p>
                        To keep our community reviews genuine and helpful! We want to make sure all feedback comes from members who actually experienced the sweat, music, and coaching of that session. Once our trainer marks you present on their roster, the review option will unlock on the class details page. You can even update your rating later if you change your mind!
                    </p>
                </details>
            </div>
        </div>
    </section>
</main>

<?php include 'templates/footer.php'; ?>