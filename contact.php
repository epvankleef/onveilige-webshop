<?php
$page_title = "Contact - TechShop";
$current_page = 'contact';

require_once 'config.php';

$message = '';
$error = '';

if ($_POST) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $contact_message = $_POST['message'] ?? '';
    
    if ($name && $email && $contact_message) {
        // Basic escape om SQL errors te voorkomen maar kwetsbaarheid te behouden
        $name = str_replace("'", "''", $name);
        $email = str_replace("'", "''", $email);
        $subject = str_replace("'", "''", $subject);
        $contact_message = str_replace("'", "''", $contact_message);
        
        $insert_query = "INSERT INTO comments (product_id, username, comment) VALUES (1, 'CONTACT: $name - $email', '$subject: $contact_message')";
        executeQuery($insert_query);
        
        $message = "Bedankt voor je bericht! We nemen zo snel mogelijk contact op.";
        logAction("Contact form submitted by: " . $name);
    } else {
        $error = "Vul alle verplichte velden in.";
    }
}

include 'includes/header.php';
?>

    <section class="hero">
        <div class="container">
            <div class="form-container">
                <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">
                    <i class="fas fa-envelope"></i> Contact Opnemen
                </h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <p style="text-align: center; color: #666; margin-bottom: 2rem;">
                    Heb je vragen over onze producten of diensten? Neem gerust contact met ons op!
                    We streven ernaar om binnen 24 uur te reageren.
                </p>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Naam *:</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail *:</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Onderwerp:</label>
                        <input type="text" id="subject" name="subject" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Bericht *:</label>
                        <textarea id="message" name="message" class="form-control" rows="5" required 
                                  placeholder="Schrijf hier je bericht..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Verstuur Bericht
                    </button>
                </form>
                
                <div style="margin-top: 3rem; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
                    <h5><i class="fas fa-info-circle"></i> Andere manieren om contact op te nemen:</h5>
                    <div style="margin-top: 1rem;">
                        <p><i class="fas fa-phone"></i> <strong>Telefoon:</strong> 088-1234567 (ma-vr 9:00-17:00)</p>
                        <p><i class="fas fa-envelope"></i> <strong>E-mail:</strong> info@techshop.nl</p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Adres:</strong> Techstraat 42, 1234 AB Amsterdam</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>