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
        
        // FIX: Gebruik product_id = 1 (bestaat wel) in plaats van 0
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
                
                <div style="background: rgba(220, 53, 69, 0.1); padding: 1rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h4 style="color: #dc3545;"><i class="fas fa-bug"></i> Formulier XSS Test (Nu Werkend!)</h4>
                    <p style="color: #666; font-size: 0.9rem;">
                        Test XSS in formulieren: <code>&lt;script&gt;alert('Form XSS')&lt;/script&gt;</code><br>
                        Simpele test: <code>&lt;script&gt;alert(123)&lt;/script&gt;</code><br>
                        Image test: <code>&lt;img src=x onerror=alert('IMG')&gt;</code>
                    </p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Naam *:</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        <small style="color: #999;">XSS test hier: &lt;script&gt;alert(123)&lt;/script&gt;</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail *:</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        <small style="color: #999;">Gebruik geldig email formaat: test@test.com</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Onderwerp:</label>
                        <input type="text" id="subject" name="subject" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                        <small style="color: #999;">XSS test: &lt;img src=x onerror=alert('Subject')&gt;</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Bericht *:</label>
                        <textarea id="message" name="message" class="form-control" required 
                                  placeholder="Je bericht hier... Of test XSS: &lt;script&gt;alert(456)&lt;/script&gt;"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Verstuur Bericht
                    </button>
                </form>
                
                <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 10px;">
                    <h5>✅ Werkende XSS Test Payloads:</h5>
                    <p style="font-size: 0.9rem;">
                        <strong>Naam:</strong> <code>&lt;script&gt;alert('Name XSS')&lt;/script&gt;</code><br>
                        <strong>Onderwerp:</strong> <code>&lt;img src=x onerror=alert('Subject')&gt;</code><br>
                        <strong>Bericht:</strong> <code>&lt;svg onload=alert('Message')&gt;</code><br>
                        <strong>Email:</strong> Gebruik altijd geldig formaat (test@test.com)
                    </p>
                    <small style="color: #dc3545;">
                        💡 Tip: Contact berichten worden opgeslagen als comments bij product ID 1. 
                        Bekijk product.php?id=1 om je XSS tests te zien!
                    </small>
                </div>
            </div>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>