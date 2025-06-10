<?php
$page_title = "Admin Panel - TechShop";
$current_page = 'admin';
$page_type = 'admin';

require_once 'config.php';

// Statistieken ophalen
$total_users = executeQuery("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = executeQuery("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = executeQuery("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_comments = executeQuery("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];

// Recente bestellingen
$recent_orders_query = "SELECT o.*, u.username, p.name as product_name 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       LEFT JOIN products p ON o.product_id = p.id 
                       ORDER BY o.created_at DESC LIMIT 10";
$recent_orders = executeQuery($recent_orders_query);

// Alle gebruikers
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = executeQuery($users_query);

// Admin acties
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = $_GET['user_id'] ?? '';
    
    switch ($action) {
        case 'delete_user':
            if ($user_id && $user_id != 1) {
                executeQuery("DELETE FROM users WHERE id = $user_id");
                logAction("User deleted: ID $user_id");
                header("Location: admin.php?message=" . urlencode("Gebruiker verwijderd."));
                exit();
            }
            break;
            
        case 'make_admin':
            if ($user_id) {
                executeQuery("UPDATE users SET is_admin = 1 WHERE id = $user_id");
                logAction("User made admin: ID $user_id");
                header("Location: admin.php?message=" . urlencode("Gebruiker is nu administrator."));
                exit();
            }
            break;
            
        case 'delete_all_comments':
            executeQuery("DELETE FROM comments");
            logAction("All comments deleted");
            header("Location: admin.php?message=" . urlencode("Alle comments verwijderd."));
            exit();
            break;
            
        case 'reset_passwords':
            executeQuery("UPDATE users SET password = 'password123' WHERE id > 1");
            logAction("All passwords reset");
            header("Location: admin.php?message=" . urlencode("Wachtwoorden gereset."));
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
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
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
                
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Gebruikersnaam</th>
                                <th>Wachtwoord</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Aangemaakt</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td style="font-family: monospace; background: #f8f9fa; padding: 0.5rem;">
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
                                        <?php if (!$user['is_admin']): ?>
                                            <a href="?action=make_admin&user_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-primary" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                                                <i class="fas fa-user-shield"></i> Maak Admin
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['id'] != 1): ?>
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
            
            <!-- Admin Tools -->
            <div style="background: white; padding: 2rem; border-radius: 15px;">
                <h3><i class="fas fa-tools"></i> Beheer Tools</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <a href="?action=delete_all_comments" class="btn btn-warning"
                       onclick="return confirm('Weet je zeker dat je alle comments wilt verwijderen?')">
                        <i class="fas fa-trash-alt"></i> Verwijder Alle Comments
                    </a>
                    
                    <a href="?action=reset_passwords" class="btn btn-danger"
                       onclick="return confirm('Weet je zeker dat je alle wachtwoorden wilt resetten?')">
                        <i class="fas fa-key"></i> Reset Alle Wachtwoorden
                    </a>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>