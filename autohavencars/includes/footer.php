    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-car"></i> AutoHavenCars</h3>
                    <p>Your trusted platform for buying and selling quality vehicles.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="listings.php">Browse Cars</a></li>
                        <li><a href="post-car.php">Sell Your Car</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> info@autohavencars.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> AutoHavenCars. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/language-currency.js"></script>
    <script src="assets/js/chatbot.js"></script>
    <script src="assets/js/wishlist_cart.js"></script>
    <script src="assets/js/map.js"></script>
    <script src="assets/js/reviews.js"></script>
    <?php if (basename($_SERVER['PHP_SELF']) === 'listings.php'): ?>
        <script src="assets/js/nearby-search.js"></script>
    <?php endif; ?>
</body>
</html>

