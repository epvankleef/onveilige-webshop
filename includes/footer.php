<footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>TechShop</h4>
                    <p>Uw betrouwbare partner voor tech producten.</p>
                    <?php if (isset($page_type) && $page_type == 'admin'): ?>
                        <p style="color: #dc3545; font-size: 0.9rem;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            ONVEILIGE ADMIN VERSIE
                        </p>
                    <?php endif; ?>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="index.php">Home</a>
                    <a href="products.php">Producten</a>
                    <a href="contact.php">Contact</a>
                    <?php if (isLoggedIn() && isAdmin()): ?>
                        <a href="admin.php">Admin Panel</a>
                    <?php endif; ?>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> info@techshop.nl</p>
                    <p><i class="fas fa-phone"></i> 020-1234567</p>
                    <p><i class="fas fa-map-marker-alt"></i> Amsterdam, Nederland</p>
                </div>
                <?php if (isset($show_security_warning) && $show_security_warning): ?>
                <div class="footer-section">
                    <h4 style="color: #dc3545;">Security Warning</h4>
                    <p style="color: #dc3545; font-size: 0.9rem;">
                        <i class="fas fa-shield-alt"></i> 
                        Deze webshop bevat opzettelijke beveiligingslekken voor educatieve doeleinden.
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 TechShop. <?php echo isset($page_type) && $page_type == 'admin' ? 'ONVEILIGE ADMIN VERSIE' : 'Alle rechten voorbehouden'; ?>.</p>
                <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                    <p style="color: #dc3545; font-size: 0.8rem;">
                        DEBUG MODE ACTIEF - Beveiligingslekken zichtbaar
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</body>
</html>