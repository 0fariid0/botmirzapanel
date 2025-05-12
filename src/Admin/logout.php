<?php
session_start();

// پاک کردن تمام متغیرهای سشن
$_SESSION = array();

// پاک کردن کوکی سشن
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// از بین بردن سشن
session_destroy();

// ریدایرکت به صفحه لاگین
header('Location: login.php');
exit; 