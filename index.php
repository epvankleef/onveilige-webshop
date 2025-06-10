<?php
$page_title = "TechShop - De beste tech deals online";
$current_page = 'index';
$show_security_warning = false;

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
                <p>Jouw betrouwbare partner voor technologie sinds 2020!</p>
                <p style="font-size: 1.1rem; margin-top: 1rem;">
                    🎉 <strong>Feestelijke Aanbieding:</strong> 20% korting op alle laptops deze week!
                </p>
                
                <?php if ($message): ?>
                    <?php echo displayMessage($message); ?>
                <?php endif; ?>
                
                <form class="search-form" method="GET">
                    <input type="text" name="search" placeholder="Zoek producten..." 
                           value="<?php echo $search; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                
                <div style="background: rgba(102, 126, 234, 0.1); padding: 1.5rem; border-radius: 10px; margin-top: 2rem;">
                    <h4><i class="fas fa-truck"></i> Waarom TechShop?</h4>
                    <ul style="text-align: left; margin-top: 1rem;">
                        <li>✓ Gratis verzending vanaf €50</li>
                        <li>✓ 30 dagen retourrecht</li>
                        <li>✓ 2 jaar garantie op alle producten</li>
                        <li>✓ Deskundig advies van onze specialisten</li>
                        <li>✓ Veilig betalen met iDEAL of creditcard</li>
                    </ul>
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
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: #666;">
                        <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>Geen producten gevonden voor "<?php echo htmlspecialchars($search); ?>"</h3>
                        <p>Probeer een andere zoekopdracht of bekijk al onze producten.</p>
                        <a href="products.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-th-large"></i> Alle Producten Bekijken
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <!-- Welcome Section -->
        <section class="products-section">
            <div class="container">
                <div style="text-align: center; padding: 3rem 2rem;">
                    <h3 style="color: #333; margin-bottom: 2rem;">Populaire Categorieën</h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                        <div class="product-card">
                            <div class="product-info" style="text-align: center;">
                                <i class="fas fa-laptop" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                                <h4>Laptops</h4>
                                <p>Krachtige laptops voor werk en gaming</p>
                            </div>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-info" style="text-align: center;">
                                <i class="fas fa-mobile-alt" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                                <h4>Smartphones</h4>
                                <p>De nieuwste smartphones en accessories</p>
                            </div>
                        </div>
                        
                        <div class="product-card">
                            <div class="product-info" style="text-align: center;">
                                <i class="fas fa-headphones" style="font-size: 3rem; color: #667eea; margin-bottom: 1rem;"></i>
                                <h4>Audio</h4>
                                <p>Premium koptelefoons en speakers</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-th-large"></i> Bekijk Alle Producten
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- About Section -->
    <section class="products-section" style="background: #f8f9fa;">
        <div class="container">
            <h3><i class="fas fa-store"></i> Over TechShop</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                
                <div class="product-card">
                    <div class="product-info">
                        <h4><i class="fas fa-history"></i> Onze Geschiedenis</h4>
                        <p>TechShop werd opgericht in 2020 door een groep tech-enthousiastelingen met een passie voor innovatie. Wat begon als een kleine webshop is uitgegroeid tot een van de meest vertrouwde namen in de tech-industrie.</p>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-info">
                        <h4><i class="fas fa-users"></i> Klanttevredenheid</h4>
                        <p>Met meer dan 10.000 tevreden klanten en een gemiddelde beoordeling van 4.8 sterren, streven we ernaar om de beste service te bieden. Ons deskundige team staat altijd klaar om je te helpen.</p>
                    </div>
                </div>
                
                <div class="product-card">
                    <div class="product-info">
                        <h4><i class="fas fa-shield-alt"></i> Veilig Winkelen</h4>
                        <p>Bij TechShop staat jouw veiligheid voorop. We gebruiken de nieuwste technologieën om je persoonlijke gegevens te beschermen en bieden veilige betaalmethoden.</p>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

<?php
$page_type = 'public';
include 'includes/footer.php';
?>