<?php
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// SQLite database pad
$db_path = __DIR__ . '/webshop.db';
$db_needs_init = !file_exists($db_path);

try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (Exception $e) {
    die("Database verbinding mislukt: " . $e->getMessage());
}

// Wrapper class die MySQLi result interface nabootst
class SQLiteResult {
    private $rows;
    private $index = 0;
    public $num_rows;

    public function __construct($rows) {
        $this->rows = $rows;
        $this->num_rows = count($rows);
    }

    public function fetch_assoc() {
        if ($this->index < $this->num_rows) {
            return $this->rows[$this->index++];
        }
        return false;
    }
}

// Database initialiseren als het nog niet bestaat
if ($db_needs_init) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            is_admin INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock INTEGER DEFAULT 0,
            image_url VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            product_id INTEGER,
            quantity INTEGER DEFAULT 1,
            total_price DECIMAL(10,2),
            customer_info TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER,
            username VARCHAR(50),
            comment TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- CTF: Geheime tabel (vlag 2 - UNION attack)
        CREATE TABLE IF NOT EXISTS geheimen (
            id INTEGER PRIMARY KEY,
            titel TEXT,
            inhoud TEXT
        );

        -- CTF: Inzendingen scorebord
        CREATE TABLE IF NOT EXISTS ctf_submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            naam VARCHAR(100),
            vlag VARCHAR(200),
            challenge_naam VARCHAR(100),
            ingediend_op DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Normale gebruikers
    $pdo->exec("
        INSERT INTO users (username, password, email, is_admin) VALUES
        ('admin', 'admin123', 'admin@webshop.nl', 1),
        ('john', 'password', 'john@email.com', 0),
        ('jane', '123456', 'jane@email.com', 0),
        ('test', 'test', 'test@test.com', 0);
    ");

    // CTF: Geheime gebruikers
    // Vlag 1: User 'geheim' — alleen via SQL login bypass te bereiken
    // Vlag 4: Password van 'vlag_gebruiker' IS de flag — zichtbaar in admin panel
    $pdo->exec("
        INSERT INTO users (username, password, email, is_admin) VALUES
        ('geheim', 'Xk9!mQ2#pL7\$nR4@vZ', 'geheim@techshop.nl', 0),
        ('vlag_gebruiker', 'FLAG{plaintext_is_nooit_veilig}', 'vlag@techshop.nl', 0);
    ");

    // Producten
    $pdo->exec("
        INSERT INTO products (name, description, price, stock, image_url) VALUES
        ('Laptop Pro X1', 'Krachtige laptop voor professionals', 1299.99, 10, 'laptop.jpg'),
        ('Gaming Muis RGB', 'High-end gaming muis met RGB verlichting', 79.99, 25, 'mouse.jpg'),
        ('Mechanical Keyboard', 'Premium mechanisch toetsenbord', 149.99, 15, 'keyboard.jpg'),
        ('Webcam 4K Ultra', '4K webcam voor streaming en videocalls', 199.99, 8, 'webcam.jpg'),
        ('Headset Pro', 'Professionele headset met noise cancelling', 249.99, 12, 'headset.jpg'),
        ('Monitor 27\" 4K', 'Ultra HD monitor voor gaming en werk', 399.99, 6, 'monitor.jpg');
    ");

    // CTF: Geheim product (vlag 7 — IDOR)
    // Niet zichtbaar in de normale lijst (stock=0), maar bereikbaar via product.php?id=7
    $pdo->exec("
        INSERT INTO products (name, description, price, stock) VALUES
        ('GEHEIM PROTOTYPE', 'Interne testversie — niet voor verkoop. FLAG{idor_geheime_data_bereikt}', 0.01, 0);
    ");

    // Reacties
    $pdo->exec("
        INSERT INTO comments (product_id, username, comment) VALUES
        (1, 'john', 'Geweldige laptop, zeer tevreden!'),
        (1, 'jane', 'Snel en betrouwbaar, aanrader!'),
        (2, 'test', 'Mooie RGB effecten en goede grip'),
        (3, 'admin', 'Beste toetsenbord dat ik ooit heb gehad');
    ");

    // CTF: Geheimen tabel (vlag 2 — UNION SELECT attack)
    $pdo->exec("
        INSERT INTO geheimen (id, titel, inhoud) VALUES
        (1, 'Server Wachtwoord', 'FLAG{union_select_geheim_gevonden}');
    ");
}

// Globale connectie
$conn = $pdo;

function executeQuery($query) {
    global $pdo;

    if (DEBUG_MODE && isset($_GET['debug_sql'])) {
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "<strong>DEBUG SQL:</strong> " . htmlspecialchars($query);
        echo "</div>";
    }

    try {
        $stmt = $pdo->query($query);

        $query_upper = ltrim(strtoupper($query));
        if (strpos($query_upper, 'SELECT') === 0) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return new SQLiteResult($rows);
        }

        return true;
    } catch (Exception $e) {
        die("<div style='color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 10px 0;'>"
            . "<strong>SQL Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>"
            . "<strong>Query:</strong> " . htmlspecialchars($query) . "</div>");
    }
}

function displayMessage($message, $type = 'info') {
    if (!$message) return '';
    $class = 'alert-' . $type;
    return "<div class='alert $class'>$message</div>";
}

function logAction($action, $user_id = null) {
    $log_file = __DIR__ . '/logs/actions.log';
    $timestamp = date('Y-m-d H:i:s');
    $user = $user_id ?? ($_SESSION['user_id'] ?? 'anonymous');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $log_entry = "[$timestamp] User: $user, IP: $ip, Action: $action\n";
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$show_security_warning = true;
?>
