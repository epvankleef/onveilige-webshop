<?php
$page_title = "Inloggen - TechShop";
$current_page = 'login';

require_once 'config.php';

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    
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
                    <p style="margin-top: 1rem;"><a href="#" style="color: #666;">Wachtwoord vergeten?</a></p>
                </div>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                    <h5 style="text-align: center; color: #666;">Veilig inloggen</h5>
                    <p style="text-align: center; color: #999; font-size: 0.9rem; margin: 0;">
                        <i class="fas fa-lock"></i> Je gegevens worden veilig verwerkt
                    </p>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>