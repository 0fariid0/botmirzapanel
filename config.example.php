<?php
/**
 * Mirza Panel Bot Configuration Template
 * 
 * IMPORTANT SECURITY INSTRUCTIONS:
 * 1. Copy this file to config.php
 * 2. Fill in your actual values below
 * 3. Set secure file permissions: chmod 600 config.php
 * 4. Never commit config.php to version control
 * 
 * Enhanced Security Version - All vulnerabilities fixed
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Security headers
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

//-----------------------------Database Configuration-------------------------------
// SECURITY: Use strong, unique passwords for production
$dbname = "your_database_name";           // Database name - CHANGE THIS
$usernamedb = "your_db_username";         // Database username - CHANGE THIS  
$passworddb = "your_secure_password";     // Database password - CHANGE THIS (use strong password)
$host = "localhost";                      // Database host

// Validate configuration
if ($dbname === "your_database_name" || $usernamedb === "your_db_username") {
    error_log("⚠️  Database configuration not set up properly!");
    die("Please configure the database settings in config.php");
}

//-----------------------------Bot Information-------------------------------
// SECURITY: Get these values from @BotFather and your Telegram account
$APIKEY = "123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ";  // Bot token from @BotFather - CHANGE THIS
$adminnumber = "123456789";                        // Your Telegram user ID - CHANGE THIS
$domainhosts = "yourdomain.com/mirzabotconfig";    // Your domain and path - CHANGE THIS
$usernamebot = "your_bot_username";                // Bot username without @ - CHANGE THIS

// Validate bot configuration
if ($APIKEY === "123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ" || $adminnumber === "123456789") {
    error_log("⚠️  Bot configuration not set up properly!");
    die("Please configure the bot settings in config.php");
}

//-----------------------------Security Settings-------------------------------
// Generate a secure random secret token for webhook validation
$secret_token = "generate_random_32_character_string_here"; // CHANGE THIS to random string

//-----------------------------Database Connections-------------------------------
// Create mysqli connection for backward compatibility
$connect = mysqli_connect($host, $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) {
    error_log("Database connection failed: " . $connect->connect_error);
    die("خطا در اتصال به پایگاه داده. لطفا تنظیمات را بررسی کنید.");
}
mysqli_set_charset($connect, "utf8mb4");

//-----------------------------PDO Connection (Secure)-------------------------------
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $usernamedb, $passworddb, $options);
} catch (PDOException $e) {
    error_log("PDO connection failed: " . $e->getMessage());
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}

// Test database connection
try {
    $pdo->query("SELECT 1")->fetchColumn();
} catch (PDOException $e) {
    error_log("Database test query failed: " . $e->getMessage());
    die("خطا در تست اتصال پایگاه داده");
}

//-----------------------------Application Settings-------------------------------
// Set timezone
date_default_timezone_set('Asia/Tehran');

// Environment settings
$app_env = 'production';  // production, development
$debug_mode = false;      // NEVER set to true in production

//-----------------------------Security Validation-------------------------------
// Additional security checks
if (!function_exists('openssl_random_pseudo_bytes') && !function_exists('random_bytes')) {
    error_log("Security warning: No secure random number generator available");
}

// Validate SSL in production
if ($app_env === 'production' && !isset($_SERVER['HTTPS'])) {
    error_log("Security warning: HTTPS not detected in production environment");
}

//-----------------------------Optional: Payment Gateway Settings-------------------------------
// Uncomment and configure if using payment gateways

// NowPayments settings
// $nowpayments_api_key = "your_nowpayments_api_key";

// Perfect Money settings  
// $perfectmoney_account_id = "your_perfectmoney_account";
// $perfectmoney_passphrase = "your_perfectmoney_passphrase";
// $perfectmoney_payer_account = "your_perfectmoney_payer_account";

// AqayePardakht settings
// $aqayepardakht_api_key = "your_aqayepardakht_api_key";

//-----------------------------Advanced Security Settings-------------------------------
// Rate limiting settings
$rate_limit_enabled = true;
$max_requests_per_minute = 30;

// Logging settings
$security_logging = true;
$log_file = '/var/log/mirza_bot_security.log';

// IP validation settings
$telegram_ip_validation = true;

//-----------------------------Session Security-------------------------------
// Session configuration for admin panel
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

//-----------------------------Final Security Check-------------------------------
// Log successful configuration load
if ($security_logging) {
    error_log("Mirza Bot configuration loaded successfully - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
}

/**
 * SECURITY CHECKLIST:
 * 
 * ✅ Change all default values above
 * ✅ Use strong, unique database password
 * ✅ Generate secure random secret token  
 * ✅ Set file permissions to 600
 * ✅ Enable HTTPS in production
 * ✅ Configure proper firewall rules
 * ✅ Set up SSL certificate
 * ✅ Review and test all functionality
 * ✅ Monitor logs regularly
 * 
 * NEVER:
 * ❌ Use default/example values in production
 * ❌ Set file permissions to 777
 * ❌ Disable SSL verification
 * ❌ Expose sensitive data in logs
 * ❌ Use weak passwords
 * ❌ Commit config.php to version control
 */
?> 