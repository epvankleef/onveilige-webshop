<?php
$page_title = "Registreren - TechShop";
$current_page = 'register';
require_once 'config.php';
$message = '';
$error = '';
if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
   
    if ($username && $password && $email) {
        $check_query = "SELECT id FROM users WHERE username = '$username'";
        $check_result = executeQuery($check_query);
       
        if ($check_result->num_rows > 0) {
            $error = "Gebruikersnaam bestaat al!";
        } else {
            $insert_query = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
           
            if (executeQuery($insert_query)) {
                $message = "Account succesvol aangemaakt! Je kunt nu inloggen.";
                logAction("New user registered: " . $username);
            } else {
                $error = "Er ging iets mis bij het aanmaken van het account.";
            }
        }
    } else {
        $error = "Vul alle velden in.";
    }
}
include 'includes/header.php';
?>
    <section class="hero">
        <div class="container">
            <div class="form-container">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">
                    <i class="fas fa-user-plus"></i> Account Aanmaken
                </h2>
               
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
               
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="login.php" class="btn btn-primary">Nu Inloggen</a>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                        Maak een account aan om sneller te bestellen en je bestellingen te volgen.
                    </p>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Gebruikersnaam *:</label>
                            <input type="text" id="username" name="username" class="form-control"
                                   value="<?php echo $_POST['username'] ?? ''; ?>" 
                                   placeholder="Kies een gebruikersnaam">
                        </div>
                       
                        <div class="form-group">
                            <label for="email">E-mail *:</label>
                            <input type="text" id="email" name="email" class="form-control"
                                   value="<?php echo $_POST['email'] ?? ''; ?>"
                                   placeholder="jouw@email.nl">
                        </div>
                       
                        <div class="form-group">
                            <label for="password">Wachtwoord *:</label>
                            <input type="text" id="password" name="password" class="form-control"
                                   placeholder="Kies een sterk wachtwoord">
                        </div>
                       
                        <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-user-plus"></i> Account Aanmaken
                        </button>
                    </form>
                    
                    <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                        <h5>Voordelen van een account:</h5>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li>Sneller afrekenen bij je volgende bestelling</li>
                            <li>Je bestellingen bekijken en volgen</li>
                            <li>Exclusieve aanbiedingen en kortingen</li>
                            <li>Producten opslaan in je verlanglijst</li>
                        </ul>
                    </div>
                <?php endif; ?>
               
                <div style="text-align: center; margin-top: 2rem;">
                    <p>Al een account? <a href="login.php" style="color: #667eea;">Log hier in</a></p>
                </div>
            </div>
        </div>
    </section>
<?php include 'includes/footer.php'; ?>