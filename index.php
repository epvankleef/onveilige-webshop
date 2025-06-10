<?php
$page_title = "TechShop - Onveilige Webshop voor Security Training";
$current_page = 'index';
$show_security_warning = true;

require_once 'config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';

// Alleen zoeken als er daadwerkelijk een zoekterm is
$result = null;
if ($search) {
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
    $result = executeQuery($query);
}

logAction("Homepage visited, search: '$search'");

include 'includes/header.php';
?>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Welkom bij TechShop</h2>
                <p>De beste tech producten voor de laagste prijzen!</p>
                <p style="font-size: 0.9rem; opacity: 0.8;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>WAARSCHUWING:</strong> Deze webshop bevat opzettelijke beveiligingslekken voor educatieve doeleinden
                </p>
                
                <?php if ($message): ?>
                    <?php echo displayMessage($message); ?>
                <?php endif; ?>
                
                <form class="search-form" method="GET">
                    <input type="text" name="search" placeholder="Zoek producten..." 
                           value="<?php echo $search; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                    
                    <?php if (DEBUG_MODE): ?>
                        <label style="color: white; font-size: 0.8rem; margin-left: 10px;">
                            <input type="checkbox" name="debug_sql" value="1" 
                                   <?php echo isset($_GET['debug_sql']) ? 'checked' : ''; ?>>
                            Debug SQL
                        </label>
                    <?php endif; ?>
                </form>
                
                <div style="background: rgba(220, 53, 69, 0.2); padding: 1rem; border-radius: 10px; margin-top: 2rem; font-size: 0.9rem;">
                    <h4><i class="fas fa-bug"></i> Security Test Guide voor Studenten:</h4>
                    <ul style="text-align: left; margin-top: 1rem;">
                        <li><strong>SQL Injection:</strong> Ga naar <a href="login.php" style="color: #fff;">login.php</a> en probeer <code>' OR 1=1#</code></li>
                        <li><strong>XSS Test:</strong> Probeer deze URL: <code>?message=&lt;script&gt;alert('XSS')&lt;/script&gt;</code></li>
                        <li><strong>IDOR Test:</strong> Ga naar een product en verander het ID nummer in de URL</li>
                        <li><strong>Admin Toegang:</strong> Ga direct naar <a href="admin.php" style="color: #fff;">admin.php</a> zonder in te loggen</li>
                        <li><strong>Search SQL Injection:</strong> Zoek naar <code>xyz' OR 1=1#</code> (alle producten verschijnen!)</li>
                    </ul>
                    <p style="margin-top: 1rem; font-size: 0.8rem; opacity: 0.8;">
                        💡 <strong>Tip:</strong> Begin met de login SQL injection - dat is het eenvoudigst!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section - Only show when searching -->
    <?php if ($search): ?>
        <section class="products-section">
            <div class="container">
                <h3>Zoekresultaten voor: "<?php echo htmlspecialchars($search); ?>"</h3>
                
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="products-grid">
                        <?php while ($product = $result->fetch_assoc()): ?>
                            <div class="product-card hover-glow">
                                <div class="product-image">
                                    <i class="fas fa-laptop product-icon"></i>
                                </div>
                                <div class="product-info">
                                    <h4><?php echo $product['name']; ?></h4>
                                    <p><?php echo substr($product['description'], 0, 100); ?>...</p>
                                    <div class="product-price">€<?php echo number_format($product['price'], 2); ?></div>
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-eye"></i> Bekijken
                                        </a>
                                        <a href="order.php?product_id=<?php echo $product['id']; ?>" class="btn btn-success">
                                            <i class="fas fa-shopping-cart"></i> Bestellen
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div style="margin-top: 2rem; text-align: center; background: rgba(40, 167, 69, 0.1); padding: 1rem; border-radius: 10px;">
                        <p style="color: #28a745; font-weight: bold;">
                            <i class="fas fa-check-circle"></i> 
                            Gevonden: <?php echo $result->num_rows; ?> product(en)
                        </p>
                        <?php if ($result->num_rows == 6): ?>
                            <p style="color: #dc3545; font-size: 0.9rem; margin-top: 0.5rem;">
                                🚨 <strong>SQL Injection Gedetecteerd!</strong> Alle producten getoond ondanks specifieke zoekterm.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #666;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>Geen producten gevonden voor "<?php echo htmlspecialchars($search); ?>"</h3>
                        <p>Probeer een andere zoekopdracht.</p>
                        
                        <div style="margin-top: 2rem; background: rgba(220, 53, 69, 0.1); padding: 1rem; border-radius: 10px;">
                            <h4 style="color: #dc3545;">💡 SQL Injection Test Hint:</h4>
                            <p style="color: #666;">
                                Als je SQL injection wilt testen, probeer dan: <br>
                                <code style="background: white; padding: 3px; border-radius: 3px;">xyz' OR 1=1#</code>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Welcome Section - No Products by Default -->
        <section class="products-section">
            <div class="container">
                <div style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 6rem; color: #667eea; margin-bottom: 2rem;">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 style="color: #333; margin-bottom: 1rem;">Ontdek Onze Producten</h3>
                    <p style="color: #666; font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem;">
                        Welkom bij TechShop! Gebruik de zoekbalk hierboven om onze geweldige tech producten te vinden, 
                        of bekijk alle beschikbare items.
                    </p>
                    
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-th-large"></i> Alle Producten Bekijken
                        </a>
                        <button onclick="document.querySelector('[name=search]').focus()" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Zoeken
                        </button>
                    </div>
                    
                    <!-- SQL Injection Demo -->
                    <div style="margin-top: 3rem; background: rgba(102, 126, 234, 0.1); padding: 2rem; border-radius: 15px;">
                        <h4 style="color: #667eea; margin-bottom: 1rem;">
                            <i class="fas fa-flask"></i> SQL Injection Demonstratie
                        </h4>
                        <p style="color: #666; margin-bottom: 1rem;">
                            Test de zoekfunctie met deze voorbeelden:
                        </p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; text-align: left;">
                            <div style="background: white; padding: 1rem; border-radius: 8px;">
                                <strong>Normale zoeken:</strong><br>
                                <code>laptop</code> → toont 1 product
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 8px;">
                                <strong>Niet-bestaand:</strong><br>
                                <code>xyz</code> → toont 0 producten
                            </div>
                            <div style="background: white; padding: 1rem; border-radius: 8px; border: 2px solid #dc3545;">
                                <strong>SQL Injection:</strong><br>
                                <code>xyz' OR 1=1#</code> → toont ALLE producten!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Security Testing Links -->
    <section class="products-section" style="background: rgba(220, 53, 69, 0.1);">
        <div class="container">
            <h3 style="color: #dc3545;"><i class="fas fa-shield-alt"></i> Security Testing Links</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <div class="product-card" style="border: 2px solid #dc3545;">
                    <div class="product-info">
                        <h4><i class="fas fa-user-lock"></i> SQL Injection (Login)</h4>
                        <p>Test login met <code>' OR 1=1#</code> - meest betrouwbaar!</p>
                        <a href="login.php" class="btn btn-danger">Test Login</a>
                    </div>
                </div>
                
                <div class="product-card" style="border: 2px solid #fd7e14;">
                    <div class="product-info">
                        <h4><i class="fas fa-code"></i> XSS Vulnerabilities</h4>
                        <p>Test Cross-Site Scripting in comments en URL parameters.</p>
                        <a href="product.php?id=1" class="btn btn-warning">Test XSS</a>
                    </div>
                </div>
                
                <div class="product-card" style="border: 2px solid #6f42c1;">
                    <div class="product-info">
                        <h4><i class="fas fa-key"></i> IDOR Access</h4>
                        <p>Test toegang tot producten door ID's te veranderen.</p>
                        <a href="product.php?id=999" class="btn btn-secondary">Test IDOR</a>
                    </div>
                </div>
                
                <div class="product-card" style="border: 2px solid #e83e8c;">
                    <div class="product-info">
                        <h4><i class="fas fa-user-cog"></i> Admin Access</h4>
                        <p>Test toegang tot admin panel zonder authenticatie.</p>
                        <a href="admin.php" class="btn" style="background: #e83e8c; color: white;">Test Admin</a>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

<?php
$page_type = 'public';
include 'includes/footer.php';
?>