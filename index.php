<?php 
    session_start();
    $pageTitle = "Flow Fitness - Find Your Rhythm";
    $currentPage = 'home';
    $extraCSS = 'home.css';
    include 'templates/header.php'; 
?>

    <main>
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero-content container">
                <h1 id="hero-title">Find Your Flow.</h1>
                <p>Momentum, balance, and the best equipment in the city. Flow Fitness is where you find your rhythm and push past your limits.</p>
                <div class="hero-actions">
                    <a href="#plans" class="btn-primary btn-large">View Memberships</a>
                    <a href="schedule.php" class="btn-secondary btn-large">Explore Classes</a>
                </div>
            </div>
        </section>

        <section id="about" class="about-section container">
            <div class="section-header text-center">
                <h2>Movement is Everything</h2>
            </div>
            
            <div class="about-grid">
                <div class="about-text">
                    <p>Founded on the belief that fitness is a continuous journey, Flow Fitness is designed to keep you moving forward without friction.</p>
                    <p>Whether you are finding your footing or pushing for a new personal best, our state-of-the-art facilities and expert trainers are here to help you maintain your momentum and achieve peak physical health.</p>
                </div>
                <figure class="about-image">
                    <img src="assets/gym-floor.jpeg" alt="Our premium gym floor with modern equipment">
                    <figcaption>Main training area at Flow Fitness.</figcaption>
                </figure>
            </div>
        </section>

        <section id="plans" class="plans-section bg-light" aria-labelledby="plans-title">
            <div class="container">
                <div class="section-header text-center">
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
                            <li>Basic app access (schedule & check-ins)</li>
                        </ul>
                        <a href="register.php?plan=starter" class="btn-outline btn-block">Choose Starter</a>
                    </article>

                    <article class="card plan-card highlighted">
                        <div class="card-badge">Most Popular</div>
                        <div class="card-header">
                            <h3>Pro</h3>
                            <p class="price">€45<span>/month</span></p>
                        </div>
                        <ul class="plan-features">
                            <li>Everything in Starter</li>
                            <li>24/7 Access</li>
                            <li>All group fitness classes</li>
                            <li>1 Personal Training session/month</li>
                            <li>Advanced app access (equipment tracking & progress)</li>
                            <li>Guest pass: 1/month</li>
                        </ul>
                        <a href="register.php?plan=pro" class="btn-primary btn-block">Choose Pro</a>
                    </article>

                    <article class="card plan-card">
                        <div class="card-header">
                            <h3>Elite</h3>
                            <p class="price">€70<span>/month</span></p>
                        </div>
                        <ul class="plan-features">
                            <li>Everything in Pro</li>
                            <li>Weekly Personal Training</li>
                            <li>Custom nutrition plan</li>
                            <li>Priority class booking & waitlist</li>
                            <li>Sauna & cold plunge access</li>
                            <li>Premium towels & amenities</li>
                            <li>Exclusive Elite events & workshops</li>
                            <li>Unlimited guest passes</li>
                        </ul>
                        <a href="register.php?plan=elite" class="btn-outline btn-block">Choose Elite</a>
                    </article>
                </div>
            </div>
        </section>

        <section id="testimonials" class="testimonials-section container" aria-labelledby="testimonials-title">
            <div class="section-header text-center">
                <h2 id="testimonials-title">The Flow Community</h2>
                <p>Hear from the members who bring the energy every single day.</p>
            </div>
            <div class="cards-grid">
                <article class="card testimonial-card text-center">
                    <p class="quote">"The atmosphere here is unmatched. It feels like a professional training facility. The equipment is top-tier and the trainers truly push you to your limits."</p>
                    <div class="author">
                        <h3>Marcus T.</h3>
                        <p>Elite Member</p>
                    </div>
                </article>
                <article class="card testimonial-card text-center">
                    <p class="quote">"I've never been to a gym with such great class variety. The morning HIIT sessions completely changed my routine and the instructors are phenomenal."</p>
                    <div class="author">
                        <h3>Sarah L.</h3>
                        <p>Pro Member</p>
                    </div>
                </article>
                <article class="card testimonial-card text-center">
                    <p class="quote">"Finding a gym with 24/7 access, excellent recovery zones, and a real sense of community is rare. Flow Fitness has quickly become my second home."</p>
                    <div class="author">
                        <h3>David K.</h3>
                        <p>Elite Member</p>
                    </div>
                </article>
            </div>
        </section>

        <section id="faq" class="faq-section container" aria-labelledby="faq-title">
            <div class="faq-inner">
                <div class="section-header text-center">
                    <h2 id="faq-title">Frequently Asked Questions</h2>
                </div>
                <div class="faq-list">
                    <details class="faq-item">
                        <summary>Do I need to book classes in advance?</summary>
                        <p>Yes, our classes fill up quickly. We recommend booking your spot through your profile at least 24 hours in advance to keep your routine steady.</p>
                    </details>

                    <details class="faq-item">
                        <summary>Can I check if the gym is crowded right now?</summary>
                        <p>Absolutely. As a Pro or Elite member, you can log into your account and check the real-time availability of equipment on the main floor.</p>
                    </details>

                    <details class="faq-item">
                        <summary>Can I cancel my membership at any time?</summary>
                        <p>Our Starter and Pro plans are contract-free and can be canceled with a 30-day notice. The Elite plan requires a 6-month commitment.</p>
                    </details>
                </div>
            </div>
        </section>
    </main>

<?php include 'templates/footer.php'; ?>