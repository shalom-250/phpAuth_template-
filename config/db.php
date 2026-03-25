<?php
// config/db.php - Database configuration

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'security');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');  // Change to your SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'niyonkurushalom2003@gmail.com');  // Your email
define('SMTP_PASSWORD', 'uqmt dvxv faup wkko');  // App password (not regular password)
define('SMTP_FROM_EMAIL', 'niyonkurushalom2003@gmail.com');
define('SMTP_FROM_NAME', 'Security System');
define('SMTP_SECURE', 'tls');  // tls or ssl

// Security configuration
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_OPTIONS', ['cost' => 12]);
define('TOKEN_EXPIRY_HOURS', 24);
define('MAX_LOGIN_ATTEMPTS', 5);
define('ACCOUNT_LOCKOUT_MINUTES', 30);

// Create database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error',
        'data' => null,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
?>