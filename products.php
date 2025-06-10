<?php
$page_title = "Alle Producten - TechShop";
$current_page = 'products';

require_once 'config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

if ($search) {
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
} else {
    $query = "SELECT * FROM products ORDER BY name";
}

$result = executeQuery($query);

include 'includes/header.php';
?>

    <section class="products-section" style="margin-top: 2rem;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem;">
                <?php echo $search ? "Zoekresultaten voor: " . $search : "Alle Producten"; ?>
            </h2>
            
            <div style="text-align: center; margin-bottom: 3rem;">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Zoek producten..." 
                           value="<?php echo $search; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <div class="products-grid">
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <i class="fas fa-laptop product-icon"></i>
                        </div>
                        <div class="product-info">
                            <h4><?php echo $product['name']; ?></h4>
                            <p><?php echo substr($product['description'], 0, 100); ?>...</p>
                            <div class="product-price">€<?php echo $product['price']; ?></div>
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
            
            <?php if ($result->num_rows == 0): ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-search" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h3>Geen producten gevonden</h3>
                    <p>Probeer een andere zoekopdracht.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>