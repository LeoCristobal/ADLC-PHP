<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'login_system';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create mysqli connection for backward compatibility (if needed)
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'leocristobal108@gmail.com');
define('SMTP_PASS', 'arzvgthvvhzzrpnw');
define('SMTP_FROM', 'leocristobal108@gmail.com');
define('SMTP_NAME', 'ADLC');
?> 