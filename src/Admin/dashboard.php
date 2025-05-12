<?php
session_start();
require_once __DIR__ . '/../Config/config.php';

// بررسی لاگین بودن ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// دریافت آمار کلی
$stats = [
    'total_users' => 0,
    'active_users' => 0,
    'total_servers' => 0,
    'total_income' => 0
];

try {
    // تعداد کل کاربران
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // تعداد کاربران فعال
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
    $stats['active_users'] = $stmt->fetchColumn();
    
    // تعداد سرورها
    $stmt = $pdo->query("SELECT COUNT(*) FROM servers");
    $stats['total_servers'] = $stmt->fetchColumn();
    
    // مجموع درآمد
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE status = 'completed'");
    $stats['total_income'] = $stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد مدیریت</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: Vazirmatn, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 250px;
            padding: 20px;
            background: #343a40;
            color: white;
        }
        .main-content {
            margin-right: 250px;
            padding: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 8px 16px;
            margin: 4px 0;
            border-radius: 5px;
        }
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .nav-link.active {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3 class="mb-4 text-center">پنل مدیریت میرزا</h3>
        <nav class="nav flex-column">
            <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> داشبورد</a>
            <a class="nav-link" href="users.php"><i class="bi bi-people"></i> کاربران</a>
            <a class="nav-link" href="servers.php"><i class="bi bi-hdd-rack"></i> سرورها</a>
            <a class="nav-link" href="plans.php"><i class="bi bi-card-list"></i> پلن‌ها</a>
            <a class="nav-link" href="payments.php"><i class="bi bi-currency-dollar"></i> پرداخت‌ها</a>
            <a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> تنظیمات</a>
            <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> خروج</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">داشبورد</h2>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3 class="h5 mb-3">کل کاربران</h3>
                        <h4 class="h2 mb-0"><?php echo number_format($stats['total_users']); ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3 class="h5 mb-3">کاربران فعال</h3>
                        <h4 class="h2 mb-0"><?php echo number_format($stats['active_users']); ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3 class="h5 mb-3">تعداد سرورها</h3>
                        <h4 class="h2 mb-0"><?php echo number_format($stats['total_servers']); ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h3 class="h5 mb-3">درآمد کل</h3>
                        <h4 class="h2 mb-0"><?php echo number_format($stats['total_income']); ?> تومان</h4>
                    </div>
                </div>
            </div>

            <!-- اینجا می‌توانید نمودارها و جداول بیشتری اضافه کنید -->
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 