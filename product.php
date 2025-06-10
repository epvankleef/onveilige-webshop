<?php
$page_title = "Product Detail - TechShop";
$current_page = 'product';

require_once 'config.php';

$product_id = $_GET['id'] ?? 1;

$query = "SELECT * FROM products WHERE id = $product_id";
$result = executeQuery($query);

if ($result->num_rows == 0) {
    header("Location: index.php?message=" . urlencode("Product niet gevonden!"));
    exit();
}

$product = $result->fetch_assoc();

if ($_POST && isset($_POST['comment'])) {
    $username = $_POST['username'] ?? 'Anoniem';
    $comment = $_POST['comment'];
    
    // Basic escape om SQL errors te voorkomen maar kwetsbaarheid te behouden
    $username = str_replace("'", "''", $username);
    $comment = str_replace("'", "''", $comment);
    
    $insert_query = "INSERT INTO comments (product_id, username, comment) VALUES ($product_id, '$username', '$comment')";
    executeQuery($insert_query);
    
    logAction("Comment added on product $product_id by $username");
    
    header("Location: product.php?id=$product_id&message=" . urlencode("Review toegevoegd!"));
    exit();
}

$comments_query = "SELECT * FROM comments WHERE product_id = $product_id ORDER BY created_at DESC";
$comments_result = executeQuery($comments_query);

$message = $_GET['message'] ?? '';

$page_title = $product['name'] . " - TechShop";

include 'includes/header.php';
?>

    <section class="product-detail">
        <div class="container">
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="product-detail-content">
                <div class="product-detail-image">
                    <i class="fas fa-laptop"></i>
                </div>
                
                <div class="product-detail-info">
                    <h1><?php echo $product['name']; ?></h1>
                    <div class="product-detail-price">€<?php echo $product['price']; ?></div>
                    
                    <div class="product-rating" style="margin: 1rem 0;">
                        <i class="fas fa-star" style="color: #ffc107;"></i>
                        <i class="fas fa-star" style="color: #ffc107;"></i>
                        <i class="fas fa-star" style="color: #ffc107;"></i>
                        <i class="fas fa-star" style="color: #ffc107;"></i>
                        <i class="far fa-star" style="color: #ffc107;"></i>
                        <span style="margin-left: 0.5rem;">4.0 (<?php echo rand(10, 50); ?> reviews)</span>
                    </div>
                    
                    <div class="product-detail-description">
                        <?php echo $product['description']; ?>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <p><strong>Voorraad:</strong> <?php echo $product['stock']; ?> stuks</p>
                        <p><strong>Artikelnummer:</strong> <?php echo str_pad($product['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p><strong>Levertijd:</strong> 1-2 werkdagen</p>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <a href="order.php?product_id=<?php echo $product['id']; ?>" class="btn btn-success">
                            <i class="fas fa-shopping-cart"></i> Nu Bestellen
                        </a>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Terug naar overzicht
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="comments-section">
        <div class="container">
            <h3><i class="fas fa-comments"></i> Klantreviews</h3>
            
            <div class="form-container" style="margin-bottom: 3rem;">
                <h4>Schrijf een review</h4>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Naam:</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo isLoggedIn() ? $_SESSION['username'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">Je review:</label>
                        <textarea id="comment" name="comment" class="form-control" rows="4"
                                  placeholder="Wat vind je van dit product?" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Verstuur Review
                    </button>
                </form>
            </div>
            
            <div class="comments-list">
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment">
                        <div class="comment-author">
                            <i class="fas fa-user"></i> <?php echo $comment['username']; ?>
                        </div>
                        <div class="comment-text">
                            <?php echo $comment['comment']; ?>
                        </div>
                        <div class="comment-date">
                            <i class="fas fa-clock"></i> <?php echo date('d-m-Y H:i', strtotime($comment['created_at'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <?php if ($comments_result->num_rows == 0): ?>
                    <div class="comment">
                        <div class="comment-text" style="text-align: center; color: #666;">
                            <i class="fas fa-comment-slash"></i> Nog geen reviews voor dit product. Wees de eerste!
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>