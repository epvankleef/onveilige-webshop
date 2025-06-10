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
    
    header("Location: product.php?id=$product_id&message=" . urlencode("Comment toegevoegd!"));
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
                    <div class="product-detail-description">
                        <?php echo $product['description']; ?>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <p><strong>Voorraad:</strong> <?php echo $product['stock']; ?> stuks</p>
                        <p><strong>Product ID:</strong> <?php echo $product['id']; ?> 
                           <small style="color: #dc3545;">(IDOR Test: verander dit nummer in URL)</small></p>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <a href="order.php?product_id=<?php echo $product['id']; ?>" class="btn btn-success">
                            <i class="fas fa-shopping-cart"></i> Nu Bestellen
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Terug naar overzicht
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="comments-section">
        <div class="container">
            <h3><i class="fas fa-comments"></i> Reviews & Comments</h3>
            
            <div style="background: rgba(220, 53, 69, 0.1); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
                <h4 style="color: #dc3545;"><i class="fas fa-bug"></i> XSS Test Zone</h4>
                <p style="color: #666; font-size: 0.9rem;">
                    Test XSS in comments (werkt nu!): <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code><br>
                    Of probeer: <code>&lt;img src=x onerror=alert('Image XSS')&gt;</code><br>
                    Simpel: <code>&lt;script&gt;alert(123)&lt;/script&gt;</code>
                </p>
            </div>
            
            <div class="form-container" style="margin-bottom: 3rem;">
                <h4>Laat een review achter:</h4>
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Naam:</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo isLoggedIn() ? $_SESSION['username'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">Comment:</label>
                        <textarea id="comment" name="comment" class="form-control" 
                                  placeholder="Deel je ervaring... XSS test: &lt;script&gt;alert(123)&lt;/script&gt;" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Verstuur Comment
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
                            <i class="fas fa-comment-slash"></i> Nog geen reviews voor dit product.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        console.log('Viewing product: <?php echo addslashes($product['name']); ?>');
        
        var productData = {
            id: <?php echo $product['id']; ?>,
            name: '<?php echo addslashes($product['name']); ?>',
            price: <?php echo $product['price']; ?>
        };
    </script>

<?php include 'includes/footer.php'; ?>