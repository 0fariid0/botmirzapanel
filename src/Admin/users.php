<?php
session_start();
require_once __DIR__ . '/../Config/config.php';

// بررسی لاگین بودن ادمین
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// حذف کاربر
if (isset($_POST['delete_user'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($user_id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = 'کاربر با موفقیت حذف شد';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'خطا در حذف کاربر';
            error_log("User Delete Error: " . $e->getMessage());
        }
    }
}

// تغییر وضعیت کاربر
if (isset($_POST['toggle_status'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($user_id) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = 'وضعیت کاربر با موفقیت تغییر کرد';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'خطا در تغییر وضعیت کاربر';
            error_log("User Status Update Error: " . $e->getMessage());
        }
    }
}

// دریافت لیست کاربران
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

try {
    // تعداد کل کاربران
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    
    // دریافت لیست کاربران
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(s.id) as subscription_count,
               SUM(p.amount) as total_payments
        FROM users u
        LEFT JOIN subscriptions s ON u.id = s.user_id
        LEFT JOIN payments p ON u.id = p.user_id AND p.status = 'completed'
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$per_page, $offset]);
    $users = $stmt->fetchAll();
    
    $total_pages = ceil($total_users / $per_page);
} catch (PDOException $e) {
    $_SESSION['error'] = 'خطا در دریافت لیست کاربران';
    error_log("Users List Error: " . $e->getMessage());
    $users = [];
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربران</title>
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
            <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> داشبورد</a>
            <a class="nav-link active" href="users.php"><i class="bi bi-people"></i> کاربران</a>
            <a class="nav-link" href="servers.php"><i class="bi bi-hdd-rack"></i> سرورها</a>
            <a class="nav-link" href="plans.php"><i class="bi bi-card-list"></i> پلن‌ها</a>
            <a class="nav-link" href="payments.php"><i class="bi bi-currency-dollar"></i> پرداخت‌ها</a>
            <a class="nav-link" href="settings.php"><i class="bi bi-gear"></i> تنظیمات</a>
            <a class="nav-link text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> خروج</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>مدیریت کاربران</h2>
                <a href="add_user.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> افزودن کاربر جدید
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>شناسه</th>
                                    <th>نام کاربری</th>
                                    <th>تلگرام ID</th>
                                    <th>وضعیت</th>
                                    <th>تعداد اشتراک</th>
                                    <th>مجموع پرداخت</th>
                                    <th>تاریخ عضویت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['telegram_id']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo $user['status'] === 'active' ? 'فعال' : 'غیرفعال'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($user['subscription_count']); ?></td>
                                    <td><?php echo number_format($user['total_payments']); ?> تومان</td>
                                    <td><?php echo date('Y/m/d H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-toggle-on"></i>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('آیا مطمئن هستید؟');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="صفحه‌بندی">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 