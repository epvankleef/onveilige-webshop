<?php
$page_title = "Admin Panel - TechShop";
$current_page = 'admin';
$page_type = 'admin';

require_once 'config.php';

// ONVEILIG: Geen authenticatie check!
// Deze pagina zou alleen toegankelijk moeten zijn voor administrators
// maar er wordt niet gecontroleerd of de gebruiker is ingelogd of admin rechten heeft

// Statistieken ophalen - ONVEILIGE queries
$total_users = executeQuery("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = executeQuery("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = executeQuery("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_comments = executeQuery("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];

// Recente bestellingen - ONVEILIG: geen toegangscontrole
$recent_orders_query = "SELECT o.*, u.username, p.name as product_name 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       LEFT JOIN products p ON o.product_id = p.id 
                       ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = executeQuery($recent_orders_query);

// Alle gebruikers - ONVEILIG: wachtwoorden zichtbaar
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = executeQuery($users_query);

// ONVEILIG: Admin acties zonder CSRF bescherming
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = $_GET['user_id'] ?? '';
    
    switch ($action) {
        case 'delete_user':
            // ONVEILIG: Geen bevestiging, direct verwijderen
            if ($user_id && $user_id != 1) { // Bescherm admin account
                executeQuery("DELETE FROM users WHERE id = $user_id");
                logAction("User deleted: ID $user_id");
                header("Location: admin.php?message=" . urlencode("Gebruiker $user_id verwijderd!"));
                exit();
            }
            break;
            
        case 'make_admin':
            // ONVEILIG: Iedereen kan admin worden
            if ($user_id) {
                executeQuery("UPDATE users SET is_admin = 1 WHERE id = $user_id");
                logAction("User made admin: ID $user_id");
                header("Location: admin.php?message=" . urlencode("Gebruiker $user_id is nu admin!"));
                exit();
            }
            break;
            
        case 'delete_all_comments':
            // ZEER ONVEILIG: Verwijder alle comments
            executeQuery("DELETE FROM comments");
            logAction("All comments deleted");
            header("Location: admin.php?message=" . urlencode("Alle comments verwijderd!"));
            exit();
            break;
            
        case 'reset_passwords':
            // EXTRA ONVEILIGE ACTIE: Reset alle wachtwoorden
            executeQuery("UPDATE users SET password = 'password123' WHERE id > 1");
            logAction("All passwords reset");
            header("Location: admin.php?message=" . urlencode("Alle wachtwoorden gereset naar 'password123'!"));
            exit();
            break;
    }
}

$message = $_GET['message'] ?? '';

include 'includes/header.php';
?>

    <section class="admin-panel">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; color: #333;">
                <i class="fas fa-cogs"></i> Administrator Dashboard
            </h2>
            
            <!-- ONVEILIG: Waarschuwing dat iedereen deze pagina kan bezoeken -->
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>SECURITY WARNING:</strong> Deze admin pagina heeft geen toegangscontrole! 
                Iedereen kan deze pagina bezoeken, zelfs zonder in te loggen.
                <br><strong>Test:</strong> Open incognito venster en ga direct naar admin.php
            </div>
            
            <!-- ONVEILIG: XSS via message parameter -->
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; // ONVEILIG: geen htmlspecialchars() ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label"><i class="fas fa-users"></i> Totaal Gebruikers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_products; ?></div>
                    <div class="stat-label"><i class="fas fa-box"></i> Totaal Producten</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_orders; ?></div>
                    <div class="stat-label"><i class="fas fa-shopping-cart"></i> Totaal Bestellingen</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_comments; ?></div>
                    <div class="stat-label"><i class="fas fa-comments"></i> Totaal Comments</div>
                </div>
            </div>
            
            <!-- Users Management -->
            <div style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <h3><i class="fas fa-users-cog"></i> Gebruikersbeheer</h3>
                <p style="color: #dc3545; margin-bottom: 2rem;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    ONVEILIG: Wachtwoorden staan in plain text in de database en zijn hier zichtbaar!
                </p>
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gebruikersnaam</th>
                                <th>Wachtwoord</th>
                                <th>Email</th>
                                <th>Admin</th>
                                <th>Aangemaakt</th>
                                <th>Acties (CSRF Kwetsbaar)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <!-- ZEER ONVEILIG: wachtwoorden zichtbaar -->
                                    <td style="font-family: monospace; background: #f8f9fa; padding: 0.5rem; color: #dc3545;">
                                        <?php echo $user['password']; ?>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span style="color: #28a745;"><i class="fas fa-check"></i> Admin</span>
                                        <?php else: ?>
                                            <span style="color: #6c757d;"><i class="fas fa-times"></i> Gebruiker</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d-m-Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <!-- ONVEILIG: Geen CSRF bescherming -->
                                        <?php if (!$user['is_admin']): ?>
                                            <a href="?action=make_admin&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-primary" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                                                <i class="fas fa-user-shield"></i> Maak Admin
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['id'] != 1): // Bescherm admin account ?>
                                            <a href="?action=delete_user&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-danger" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;"
                                               onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">
                                                <i class="fas fa-trash"></i> Verwijder
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div style="background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.05);">
                <h3><i class="fas fa-shopping-cart"></i> Recente Bestellingen</h3>
                
                <?php if ($recent_orders->num_rows > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Gebruiker</th>
                                    <th>Product</th>
                                    <th>Aantal</th>
                                    <th>Totaal</th>
                                    <th>Klantgegevens</th>
                                    <th>Datum</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo $order['username'] ?? 'Gast'; ?></td>
                                        <td><?php echo $order['product_name'] ?? 'Verwijderd product'; ?></td>
                                        <td><?php echo $order['quantity']; ?></td>
                                        <td>€<?php echo $order['total_price']; ?></td>
                                        <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo $order['customer_info'] ?? 'Geen info'; ?>
                                        </td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 2rem;">
                        <i class="fas fa-shopping-cart"></i> Nog geen bestellingen geplaatst.
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Dangerous Admin Tools -->
            <div style="background: white; padding: 2rem; border-radius: 15px; border: 2px solid #dc3545;">
                <h3 style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Gevaarlijke Admin Tools (CSRF Test)</h3>
                <p style="color: #666; margin-bottom: 2rem;">
                    Deze functies zijn ONVEILIG geïmplementeerd en hebben geen CSRF bescherming!<br>
                    <strong>Test:</strong> Kopieer deze URLs en open in nieuwe tab - acties worden uitgevoerd zonder bevestiging.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <a href="?action=delete_all_comments" class="btn btn-danger"
                       onclick="return confirm('GEVAARLIJK: Dit verwijdert ALLE comments! Doorgaan?')">
                        <i class="fas fa-trash-alt"></i> Verwijder Alle Comments
                    </a>
                    
                    <a href="?action=make_admin&user_id=2" class="btn btn-warning"
                       onclick="return confirm('Maak user ID 2 admin zonder verificatie?')">
                        <i class="fas fa-user-shield"></i> Maak User 2 Admin
                    </a>
                    
                    <a href="?action=reset_passwords" class="btn btn-danger"
                       onclick="return confirm('Reset alle wachtwoorden naar password123?')">
                        <i class="fas fa-key"></i> Reset Alle Passwords
                    </a>
                    
                    <a href="?action=delete_user&user_id=3" class="btn btn-danger"
                       onclick="return confirm('Verwijder user ID 3?')">
                        <i class="fas fa-user-times"></i> Verwijder User 3
                    </a>
                </div>
                
                <!-- CSRF Test URLs -->
                <div style="margin-top: 2rem; background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <h5>CSRF Test URLs (kopieer en plak in nieuwe tab):</h5>
                    <div style="font-family: monospace; font-size: 0.9rem;">
                        <div style="margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                            <strong>Make Admin:</strong><br>
                            <code>http://localhost/webshop/admin.php?action=make_admin&user_id=2</code>
                        </div>
                        <div style="margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                            <strong>Delete User:</strong><br>
                            <code>http://localhost/webshop/admin.php?action=delete_user&user_id=3</code>
                        </div>
                        <div style="margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                            <strong>Delete Comments:</strong><br>
                            <code>http://localhost/webshop/admin.php?action=delete_all_comments</code>
                        </div>
                        <div style="margin: 5px 0; padding: 5px; background: white; border-radius: 3px;">
                            <strong>Reset Passwords:</strong><br>
                            <code>http://localhost/webshop/admin.php?action=reset_passwords</code>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 1rem; padding: 1rem; background: #fff3cd; border-radius: 5px; border: 1px solid #ffeaa7;">
                    <h6>🎯 CSRF Testing Instructions:</h6>
                    <ol style="font-size: 0.9rem; margin: 0;">
                        <li>Kopieer een van bovenstaande URLs</li>
                        <li>Open nieuwe browser tab</li>
                        <li>Plak URL in adresbalk</li>
                        <li>Druk Enter</li>
                        <li>Actie wordt uitgevoerd zonder bevestiging!</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <script>
        // ONVEILIG: Client-side admin functies
        function showLogFiles() {
            alert('Log files zouden hier getoond worden - information disclosure vulnerability!');
            console.log('Toegang tot server logs zonder authenticatie');
        }
        
        function showPhpInfo() {
            window.open('data:text/html,<h1>PHP Info zou hier staan</h1><p>Information disclosure vulnerability!</p><p>Server configuratie zichtbaar voor iedereen!</p>');
        }
        
        // Log admin access
        console.log('Admin panel accessed at: ' + new Date());
        console.log('No authentication required - major security flaw!');
    </script>

<?php include 'includes/footer.php'; ?>