<?php
if (!defined('INCLUDED_CONFIG')) {
    require_once __DIR__ . '/../config.php';
    define('INCLUDED_CONFIG', true);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'TechShop - Onveilige Webshop'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $css_path ?? 'styles.css'; ?>">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1><i class="fas fa-laptop"></i> TechShop</h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" <?php echo ($current_page == 'index') ? 'style="color: #667eea;"' : ''; ?>>Home</a>
                <a href="products.php" <?php echo ($current_page == 'products') ? 'style="color: #667eea;"' : ''; ?>>Producten</a>
                <a href="contact.php" <?php echo ($current_page == 'contact') ? 'style="color: #667eea;"' : ''; ?>>Contact</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" <?php echo ($current_page == 'admin') ? 'style="color: #667eea;"' : ''; ?>>Admin</a>
                    <?php endif; ?>
                    <span style="color: #666; margin-right: 1rem;">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['username'] ?? 'Gebruiker'; ?>
                    </span>
                    <a href="logout.php">Uitloggen</a>
                <?php else: ?>
                    <a href="login.php" <?php echo ($current_page == 'login') ? 'style="color: #667eea;"' : ''; ?>>Inloggen</a>
                    <a href="register.php" <?php echo ($current_page == 'register') ? 'style="color: #667eea;"' : ''; ?>>Registreren</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>