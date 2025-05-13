<?php
// تنظیم مسیر اصلی پروژه
define('BASE_PATH', __DIR__);

// لود کردن تنظیمات
require_once 'config.php';

$adminPath = '//' . $domainhosts . '/admin';
header("Location: $adminPath");
exit;