<footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <p class="footer-brand-name">FLOW<span>FITNESS</span>&reg;</p>
                <p>Keep moving forward.</p>
            </div>
            <div class="footer-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="schedule.php">Classes</a></li>
                    <li><a href="trainers.php">Our Team</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li><a href="login.php">Member Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <address>
                    <p>145 Flow Avenue</p>
                    <p>Email: info@flowfitness.com</p>
                </address>
            </div>
        </div>
        <div class="footer-bottom container text-center">
            <p>&copy; <?php echo date('Y'); ?> Flow Fitness.</p>
        </div>
    </footer>
</body>
</html>