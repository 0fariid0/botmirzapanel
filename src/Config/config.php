<?php
/**
 * Configuration file for Mirza Panel
 * @package MirzaPanel
 * @author MirzaPanel Team
 * @link https://t.me/mirzapanel
 */

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../../.env')) {
    $env = parse_ini_file(__DIR__ . '/../../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? '');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// Bot configuration
define('BOT_TOKEN', $_ENV['BOT_TOKEN'] ?? '');
define('BOT_USERNAME', $_ENV['BOT_USERNAME'] ?? '');
define('ADMIN_ID', $_ENV['ADMIN_ID'] ?? '');
define('DOMAIN', $_ENV['DOMAIN'] ?? '');

// Database connection settings
$db_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

// Create database connection
try {
    $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s", DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $db_options);
} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die('خطا در اتصال به پایگاه داده');
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
if (isset($_ENV['PRODUCTION']) && $_ENV['PRODUCTION'] === 'true') {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
} 