<?php
if (!defined('INCLUDED_CONFIG')) {
    require_once __DIR__ . '/../config.php';
    define('INCLUDED_CONFIG', true);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="Microsoft FrontPage 4.0">
    <meta name="author" content="Jan de Webmaster - jan@techshop.nl">
    <title><?php echo $page_title ?? 'TechShop - Onveilige Webshop'; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $css_path ?? 'styles.css'; ?>">
</head>
<body>

<!-- UNDER CONSTRUCTION BALK -->
<div style="background: repeating-linear-gradient(45deg,#ffff00,#ffff00 10px,#000 10px,#000 20px); padding: 4px 10px; text-align: center; border-bottom: 3px solid #cc0000;">
    <span style="background:#cc0000; color:#ffff00; font-family:'Comic Sans MS',cursive; font-weight:bold; font-size:11px; padding:2px 10px; border:2px solid #ffff00;">
        🚧 DEZE SITE IS NOG IN AANBOUW 🚧 &nbsp;|&nbsp; LAATSTE UPDATE: 14-03-2001 &nbsp;|&nbsp; WEBMASTER: JAN
    </span>
</div>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1><i class="fas fa-laptop"></i> TechShop</h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" <?php echo ($current_page == 'index') ? 'style="color: #667eea;"' : ''; ?>>Home</a>
                <a href="products.php" <?php echo ($current_page == 'products') ? 'style="color: #667eea;"' : ''; ?>>Producten</a>
                <a href="contact.php" <?php echo ($current_page == 'contact') ? 'style="color: #667eea;"' : ''; ?>>Contact</a>
                <a href="ctf.php" <?php echo ($current_page == 'ctf') ? 'style="color: #00ff41;"' : 'style="color: #00cc33;"'; ?>>🚩 CTF</a>
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