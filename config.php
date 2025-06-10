<?php
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "webshop";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

function executeQuery($query) {
    global $conn;
    
    if (DEBUG_MODE && isset($_GET['debug_sql'])) {
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "<strong>DEBUG SQL:</strong> " . htmlspecialchars($query);
        echo "</div>";
    }
    
    $result = $conn->query($query);
    if (!$result) {
        die("<div style='color: red; background: #ffe6e6; padding: 15px; border: 1px solid red; margin: 10px 0;'>"
            . "<strong>SQL Error:</strong> " . $conn->error . "<br>"
            . "<strong>Query:</strong> " . htmlspecialchars($query) . "</div>");
    }
    return $result;
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

$current_page = basename($_SERVER['PHP_SELF'], '.php');
$show_security_warning = true;
?>