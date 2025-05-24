<?php
/*
channel => @mirzapanel
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
}

//-----------------------------Database Configuration-------------------------------
// TODO: Configure these values for your server
$dbname = "databasename"; //  نام دیتابیس - CHANGE THIS
$usernamedb = "username"; // نام کاربری دیتابیس - CHANGE THIS
$passworddb = "password"; // رمز عبور دیتابیس - CHANGE THIS
$host = "localhost"; // میزبان دیتابیس

// Create mysqli connection for backward compatibility
$connect = mysqli_connect($host, $usernamedb, $passworddb, $dbname);
if ($connect->connect_error) {
    error_log("Database connection failed: " . $connect->connect_error);
    die("خطا در اتصال به پایگاه داده. لطفا تنظیمات را بررسی کنید.");
}
mysqli_set_charset($connect, "utf8mb4");

//-----------------------------Bot Information-------------------------------
// TODO: Configure these values for your bot
$APIKEY = "**TOKEN**"; // توکن ربات خود را وارد کنید - CHANGE THIS
$adminnumber = "5522424631";// آیدی عددی ادمین - CHANGE THIS
$domainhosts = "mirzabot.blue88shop.ir/bot";// دامنه  هاست و مسیر سورس - CHANGE THIS
$usernamebot = "marzbaninfobot"; //نام کاربری ربات  بدون @ - CHANGE THIS

// Validate configuration
if ($APIKEY === "**TOKEN**" || $dbname === "databasename") {
    error_log("Configuration not properly set up!");
    if (defined('STDIN')) {
        echo "⚠️  لطفا ابتدا فایل config.php را پیکربندی کنید!\n";
    }
}

//-----------------------------PDO Connection-------------------------------
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

// Set timezone
date_default_timezone_set('Asia/Tehran');
