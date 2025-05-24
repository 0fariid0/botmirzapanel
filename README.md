# 🤖 Mirza Panel Bot

[![Security Status](https://img.shields.io/badge/Security-Enhanced-green.svg)](./SECURITY.md)
[![Version](https://img.shields.io/badge/Version-4.14.6+-blue.svg)](./CHANGELOG.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)

## 🛡️ **Security Enhancement Notice**

**تمام مشکلات امنیتی این پروژه رفع شده است!**

### ✅ مشکلات رفع شده:
- **SQL Injection**: محافظت کامل با PDO prepared statements
- **Command Injection**: حذف استفاده خطرناک از `shell_exec()`
- **File Permissions**: اصلاح مجوزهای 777 به 755/600
- **Password Security**: حذف پسوردهای hardcoded (mirzahipass)
- **Input Validation**: اعتبارسنجی کامل ورودی‌ها
- **SSL/TLS**: بهبود پیکربندی امنیت
- **Error Handling**: بهبود مدیریت خطا

## 📥 نصب امن (Installation)

### روش 1: اسکریپت نصب بهبود یافته (پیشنهادی)

```bash
# دانلود اسکریپت نصب امن
wget https://raw.githubusercontent.com/0fariid0/botmirzapanel/main/install_fixed.sh

# اجرای اسکریپت
chmod +x install_fixed.sh
sudo ./install_fixed.sh
```

### مزایای اسکریپت جدید:
- 🔐 **پسوردهای قوی**: تولید خودکار پسوردهای امن
- 🛡️ **مجوزهای صحیح**: فایل‌ها با مجوزهای مناسب (755/600)
- ✅ **اعتبارسنجی ورودی**: بررسی دقیق تمام ورودی‌ها
- 📝 **لاگ امن**: ثبت کامل فرآیند نصب
- 🔥 **فایروال**: پیکربندی خودکار UFW
- 🔒 **SSL**: تنظیم امن گواهینامه‌های SSL

### روش 2: نصب دستی

```bash
# دانلود فایل‌ها
git clone https://github.com/0fariid0/botmirzapanel.git
cd botmirzapanel

# نصب dependencies
composer install

# پیکربندی فایل config.php
cp config.php.example config.php
nano config.php
```

## ⚙️ پیکربندی

### 1. تنظیمات پایگاه داده
```php
$dbname = "your_database_name";     // نام دیتابیس
$usernamedb = "your_db_username";   // نام کاربری دیتابیس  
$passworddb = "your_secure_password"; // رمز عبور قوی
$host = "localhost";
```

### 2. تنظیمات ربات
```php
$APIKEY = "YOUR_BOT_TOKEN";           // توکن ربات
$adminnumber = "YOUR_CHAT_ID";        // آیدی ادمین
$domainhosts = "yourdomain.com/bot";  // دامنه و مسیر
$usernamebot = "your_bot_username";   // نام کاربری ربات
```

## 🔧 امکانات

### 🛡️ امنیت
- محافظت کامل در برابر SQL Injection
- اعتبارسنجی تمام ورودی‌ها
- رمزنگاری امن اتصالات
- مجوزهای فایل بهینه
- Security headers

### 📊 مدیریت
- مدیریت کاربران و سرویس‌ها
- گزارش‌گیری کامل
- پشتیبانی از پنل‌های مختلف
- سیستم پرداخت

### 🎯 پنل‌های پشتیبانی شده
- Marzban
- Marzneshin  
- X-UI
- SanaeiPanel
- HiddifyManager

## 🚨 نکات امنیتی مهم

### ⚠️ قبل از نصب:
1. **پشتیبان‌گیری**: از دیتابیس فعلی پشتیبان بگیرید
2. **آپدیت سیستم**: سیستم‌عامل را به‌روزرسانی کنید
3. **فایروال**: تنظیمات امنیتی سرور را بررسی کنید

### ✅ بعد از نصب:
1. **تغییر پسوردها**: پسوردهای پیش‌فرض را تغییر دهید
2. **مجوزهای فایل**: فایل `config.php` باید مجوز 600 داشته باشد
3. **SSL**: گواهینامه SSL را فعال کنید
4. **مانیتورینگ**: لاگ‌ها را به‌طور منظم بررسی کنید

## 📁 ساختار فایل‌ها

```
botmirzapanel/
├── config.php              # تنظیمات اصلی (مجوز 600)
├── functions.php           # توابع امن
├── index.php              # نقطه ورود اصلی
├── install_fixed.sh       # اسکریپت نصب امن
├── .htaccess             # تنظیمات امنیتی Apache
├── composer.json         # مدیریت dependencies
├── SECURITY.md          # راهنمای امنیت
└── src/                # کدهای اصلی
    ├── Admin/         # پنل ادمین
    └── Config/       # تنظیمات اضافی
```

## 🔍 رفع مشکل

### مشکلات رایج:

#### 1. خطای اتصال به دیتابیس
```bash
# بررسی وضعیت MySQL
sudo systemctl status mysql

# بررسی لاگ‌ها
sudo tail -f /var/log/mysql/error.log
```

#### 2. مشکل SSL
```bash
# تجدید گواهینامه
sudo certbot renew

# بررسی وضعیت Apache
sudo systemctl status apache2
```

#### 3. مشکل مجوزهای فایل
```bash
# تنظیم مجوزهای صحیح
sudo chown -R www-data:www-data /var/www/html/mirzabotconfig
sudo chmod -R 755 /var/www/html/mirzabotconfig
sudo chmod 600 /var/www/html/mirzabotconfig/config.php
```

## 📞 پشتیبانی

### 🔗 لینک‌های مفید:
- **کانال تلگرام**: [@mirzapanel](https://t.me/mirzapanel)
- **مستندات**: [GitHub Wiki](https://github.com/0fariid0/botmirzapanel/wiki)
- **گزارش باگ**: [GitHub Issues](https://github.com/0fariid0/botmirzapanel/issues)

### 🛡️ گزارش مشکلات امنیتی:
اگر مشکل امنیتی پیدا کردید، لطفاً به‌صورت خصوصی گزارش دهید:
- **ایمیل**: security@mirzapanel.com
- **تلگرام**: [@mirzapanel](https://t.me/mirzapanel)

## 📋 الزامات سیستم

### حداقل الزامات:
- **OS**: Ubuntu 20.04+ / Debian 11+
- **RAM**: 512MB (پیشنهاد: 1GB+)
- **Storage**: 2GB+ فضای خالی
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Apache**: 2.4+

### الزامات پیشنهادی:
- **RAM**: 2GB+
- **CPU**: 2 Core+
- **Storage**: SSD 10GB+
- **SSL**: Certificate از Let's Encrypt

## 🎯 نسخه‌ها

### نسخه فعلی: 4.14.6+
- ✅ رفع تمام مشکلات امنیتی
- ✅ بهبود اسکریپت نصب
- ✅ اضافه شدن security headers
- ✅ بهبود error handling

### تغییرات مهم:
- **[BREAKING]** حذف پسوردهای hardcoded
- **[SECURITY]** اصلاح SQL injection vulnerabilities
- **[SECURITY]** حذف command injection risks
- **[IMPROVEMENT]** بهبود file permissions

## 📜 مجوز

این پروژه تحت مجوز MIT منتشر شده است. برای جزئیات بیشتر فایل [LICENSE](LICENSE) را مطالعه کنید.

## 🤝 مشارکت

برای مشارکت در پروژه:

1. **Fork** کنید
2. **Branch** جدید بسازید (`git checkout -b feature/amazing-feature`)
3. تغییرات را **Commit** کنید (`git commit -m 'Add amazing feature'`)
4. به **Branch** خود **Push** کنید (`git push origin feature/amazing-feature`)
5. **Pull Request** ایجاد کنید

### 📝 راهنمای مشارکت:
- کد باید clean و documented باشد
- تست‌های امنیتی را اجرا کنید
- از security best practices پیروی کنید
- در صورت تغییرات امنیتی، حتماً مستندات را به‌روزرسانی کنید

---

**⚡ نکته**: برای استفاده از آخرین نسخه با تمام بهبودهای امنیتی، از اسکریپت `install_fixed.sh` استفاده کنید.

**🛡️ امنیت اولویت اول ماست!** تمام مشکلات امنیتی شناسایی شده رفع شده‌اند.
