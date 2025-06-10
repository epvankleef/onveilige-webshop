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
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Gebruikersnaam *:</label>
                            <input type="text" id="username" name="username" class="form-control"
                                   value="<?php echo $_POST['username'] ?? ''; ?>">
                            <small style="color: #dc3545;">LET OP: Geen validatie - test met speciale tekens!</small>
                        </div>
                       
                        <div class="form-group">
                            <label for="email">E-mail *:</label>
                            <input type="text" id="email" name="email" class="form-control"
                                   value="<?php echo $_POST['email'] ?? ''; ?>">
                            <small style="color: #dc3545;">ONVEILIG: Accepteert elk formaat!</small>
                        </div>
                       
                        <div class="form-group">
                            <label for="password">Wachtwoord *:</label>
                            <input type="text" id="password" name="password" class="form-control">
                            <small style="color: #dc3545;">ONVEILIG: Wachtwoord wordt in plain text opgeslagen en is zichtbaar!</small>
                        </div>
                       
                        <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-user-plus"></i> Account Aanmaken
                        </button>
                    </form>
                    
                    <div style="margin-top: 2rem; padding: 1rem; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
                        <h4 style="color: #856404;"><i class="fas fa-exclamation-triangle"></i> Security Test Hints:</h4>
                        <ul style="color: #856404; margin: 0; padding-left: 20px;">
                            <li>Probeer HTML/JavaScript in de velden</li>
                            <li>Test met lege velden</li>
                            <li>Gebruik ongeldige email formats</li>
                            <li>Let op: wachtwoord is zichtbaar!</li>
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