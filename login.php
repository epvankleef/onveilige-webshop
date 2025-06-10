<?php
$page_title = "Inloggen - TechShop";
$current_page = 'login';

require_once 'config.php';

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ONVEILIGE SQL query - verschillende injection methods mogelijk
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
    // Debug mode - toon SQL query
    if (isset($_GET['debug'])) {
        echo "<div class='alert alert-info' style='margin: 20px;'>";
        echo "<strong>DEBUG MODE:</strong><br>";
        echo "<strong>SQL Query:</strong> " . htmlspecialchars($query) . "<br>";
        echo "<strong>Username input:</strong> " . htmlspecialchars($username) . "<br>";
        echo "<strong>Password input:</strong> " . htmlspecialchars($password);
        echo "</div>";
    }
    
    $result = executeQuery($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        $success = "Inloggen gelukt! Welkom " . htmlspecialchars($user['username']);
        
        logAction("User logged in: " . $user['username']);
        
        // Redirect na 2 seconden
        header("refresh:2;url=index.php");
    } else {
        $error = "Ongeldige gebruikersnaam of wachtwoord!";
        logAction("Failed login attempt for: " . $username);
    }
}

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';
?>

    <section class="hero">
        <div class="container">
            <div class="form-container">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">
                    <i class="fas fa-sign-in-alt"></i> Inloggen
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><small>Je wordt doorgestuurd naar de homepage...</small>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Gebruikersnaam:</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Wachtwoord:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="fas fa-sign-in-alt"></i> Inloggen
                    </button>
                </form>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <p>Nog geen account? <a href="register.php" style="color: #667eea;">Registreer hier</a></p>
                </div>
                
                <!-- SQL Injection Test Zone -->
                <div style="text-align: center; margin-top: 1rem; background: rgba(220, 53, 69, 0.1); padding: 1rem; border-radius: 10px;">
                    <h4 style="color: #dc3545;">SQL Injection Test Zone</h4>
                    <p style="color: #666; font-size: 0.9rem;">
                        <a href="?debug=1" style="color: #dc3545; font-weight: bold;">🔧 Debug modus inschakelen</a><br><br>
                        <strong>Werkende SQL Injection Tests:</strong><br>
                        📋 <code>' OR 1=1-- </code> (met spatie na --)<br>
                        📋 <code>admin'-- </code> (admin bypass)<br>
                        📋 <code>' OR '1'='1</code> (OR injection)<br>
                        📋 <code>' OR 1=1#</code> (hash comment)<br><br>
                        
                        <strong>Test Accounts:</strong><br>
                        👤 admin/admin123 (administrator)<br>
                        👤 john/password<br>
                        👤 test/test
                    </p>
                </div>
                
                <?php if (isset($_GET['debug'])): ?>
                <div style="margin-top: 1rem; background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                    <h5>🐛 SQL Injection Tutorial:</h5>
                    <ol style="text-align: left; font-size: 0.9rem;">
                        <li><strong>Probeer:</strong> <code>' OR 1=1-- </code> als gebruikersnaam</li>
                        <li><strong>Wachtwoord:</strong> Vul iets willekeurigs in</li>
                        <li><strong>Resultaat:</strong> Je wordt ingelogd als eerste user (admin)</li>
                        <li><strong>Waarom:</strong> De SQL query wordt: <br>
                            <code>SELECT * FROM users WHERE username = '' OR 1=1-- ' AND password = 'test'</code></li>
                    </ol>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>