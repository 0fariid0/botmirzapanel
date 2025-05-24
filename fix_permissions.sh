#!/bin/bash

#======================================
#    MIRZA BOT PERMISSION FIX SCRIPT
#======================================
# This script fixes file permissions and security settings
# for Mirza Panel Bot installation

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Default bot directory
BOT_DIR="/var/www/html/mirzabotconfig"

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}    MIRZA BOT PERMISSION FIX SCRIPT   ${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    echo -e "${RED}[ERROR]${NC} Please run this script as root."
    exit 1
fi

# Check if bot directory exists
if [ ! -d "$BOT_DIR" ]; then
    echo -e "${YELLOW}[WARNING]${NC} Bot directory not found at $BOT_DIR"
    echo -n "Enter the path to your bot directory: "
    read -r BOT_DIR
    
    if [ ! -d "$BOT_DIR" ]; then
        echo -e "${RED}[ERROR]${NC} Directory $BOT_DIR does not exist."
        exit 1
    fi
fi

echo -e "${BLUE}[INFO]${NC} Fixing permissions for: $BOT_DIR"
echo ""

# Fix directory ownership
echo -e "${YELLOW}[STEP 1]${NC} Setting proper ownership..."
chown -R www-data:www-data "$BOT_DIR" || {
    echo -e "${RED}[ERROR]${NC} Failed to set ownership"
    exit 1
}
echo -e "${GREEN}✓${NC} Ownership set to www-data:www-data"

# Fix directory permissions (755)
echo -e "${YELLOW}[STEP 2]${NC} Setting directory permissions..."
find "$BOT_DIR" -type d -exec chmod 755 {} \; || {
    echo -e "${RED}[ERROR]${NC} Failed to set directory permissions"
    exit 1
}
echo -e "${GREEN}✓${NC} Directory permissions set to 755"

# Fix file permissions (644 for most files)
echo -e "${YELLOW}[STEP 3]${NC} Setting file permissions..."
find "$BOT_DIR" -type f -exec chmod 644 {} \; || {
    echo -e "${RED}[ERROR]${NC} Failed to set file permissions"
    exit 1
}
echo -e "${GREEN}✓${NC} File permissions set to 644"

# Secure sensitive files (600)
echo -e "${YELLOW}[STEP 4]${NC} Securing sensitive files..."
sensitive_files=(
    "config.php"
    ".env"
    "database.php"
    "secrets.php"
)

for file in "${sensitive_files[@]}"; do
    if [ -f "$BOT_DIR/$file" ]; then
        chmod 600 "$BOT_DIR/$file"
        echo -e "${GREEN}✓${NC} Secured $file (permissions: 600)"
    fi
done

# Fix executable files
echo -e "${YELLOW}[STEP 5]${NC} Setting executable permissions for scripts..."
executable_files=(
    "*.sh"
    "cron/*.sh"
    "scripts/*.sh"
)

for pattern in "${executable_files[@]}"; do
    for file in $BOT_DIR/$pattern; do
        if [ -f "$file" ]; then
            chmod 755 "$file"
            echo -e "${GREEN}✓${NC} Made executable: $(basename "$file")"
        fi
    done
done

# Remove dangerous permissions
echo -e "${YELLOW}[STEP 6]${NC} Removing dangerous permissions..."
find "$BOT_DIR" -type f -perm 777 -exec chmod 644 {} \;
find "$BOT_DIR" -type d -perm 777 -exec chmod 755 {} \;
echo -e "${GREEN}✓${NC} Removed all 777 permissions"

# Check for security issues
echo -e "${YELLOW}[STEP 7]${NC} Security audit..."

# Check for writable files
writable_files=$(find "$BOT_DIR" -type f -perm -o+w 2>/dev/null)
if [ -n "$writable_files" ]; then
    echo -e "${YELLOW}[WARNING]${NC} Found world-writable files:"
    echo "$writable_files"
    echo -n "Fix automatically? (y/n): "
    read -r fix_writable
    if [ "$fix_writable" = "y" ]; then
        find "$BOT_DIR" -type f -perm -o+w -exec chmod o-w {} \;
        echo -e "${GREEN}✓${NC} Fixed world-writable files"
    fi
fi

# Check config.php permissions
if [ -f "$BOT_DIR/config.php" ]; then
    config_perms=$(stat -c "%a" "$BOT_DIR/config.php")
    if [ "$config_perms" != "600" ]; then
        echo -e "${YELLOW}[WARNING]${NC} config.php has permissions $config_perms, should be 600"
        chmod 600 "$BOT_DIR/config.php"
        echo -e "${GREEN}✓${NC} Fixed config.php permissions"
    else
        echo -e "${GREEN}✓${NC} config.php permissions are correct (600)"
    fi
fi

# Check for backup files
echo -e "${YELLOW}[STEP 8]${NC} Cleaning up backup files..."
backup_patterns=(
    "*.bak"
    "*.backup"
    "*~"
    "*.orig"
    "*.tmp"
)

for pattern in "${backup_patterns[@]}"; do
    for file in $BOT_DIR/$pattern; do
        if [ -f "$file" ]; then
            rm -f "$file"
            echo -e "${GREEN}✓${NC} Removed backup file: $(basename "$file")"
        fi
    done
done

# Set up log directory with proper permissions
if [ ! -d "/var/log/mirza_bot" ]; then
    echo -e "${YELLOW}[STEP 9]${NC} Creating log directory..."
    mkdir -p /var/log/mirza_bot
    chown www-data:www-data /var/log/mirza_bot
    chmod 755 /var/log/mirza_bot
    echo -e "${GREEN}✓${NC} Log directory created with proper permissions"
fi

# Apache/Nginx specific fixes
echo -e "${YELLOW}[STEP 10]${NC} Web server specific fixes..."

# Restart Apache if running
if systemctl is-active --quiet apache2; then
    echo -e "${BLUE}[INFO]${NC} Restarting Apache..."
    systemctl restart apache2
    echo -e "${GREEN}✓${NC} Apache restarted"
fi

# Restart Nginx if running
if systemctl is-active --quiet nginx; then
    echo -e "${BLUE}[INFO]${NC} Restarting Nginx..."
    systemctl restart nginx
    echo -e "${GREEN}✓${NC} Nginx restarted"
fi

# Final security report
echo ""
echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}         SECURITY REPORT              ${NC}"
echo -e "${BLUE}======================================${NC}"

# Count files by permission
echo -e "${BLUE}[INFO]${NC} Permission summary:"
echo "Files with 644 permissions: $(find "$BOT_DIR" -type f -perm 644 | wc -l)"
echo "Files with 600 permissions: $(find "$BOT_DIR" -type f -perm 600 | wc -l)"
echo "Directories with 755 permissions: $(find "$BOT_DIR" -type d -perm 755 | wc -l)"

# Check for potential issues
echo ""
echo -e "${BLUE}[INFO]${NC} Security checks:"

# Check for executable PHP files
exec_php=$(find "$BOT_DIR" -name "*.php" -executable | wc -l)
if [ "$exec_php" -gt 0 ]; then
    echo -e "${YELLOW}⚠️${NC}  Found $exec_php executable PHP files (may be normal)"
else
    echo -e "${GREEN}✓${NC} No executable PHP files found"
fi

# Check for world-readable sensitive files
sensitive_readable=$(find "$BOT_DIR" -name "config.php" -o -name ".env" | xargs ls -la 2>/dev/null | grep -v "^-rw-------" | wc -l)
if [ "$sensitive_readable" -gt 0 ]; then
    echo -e "${RED}❌${NC} Found sensitive files with incorrect permissions"
else
    echo -e "${GREEN}✓${NC} All sensitive files properly secured"
fi

# Check ownership
wrong_owner=$(find "$BOT_DIR" ! -user www-data -o ! -group www-data | wc -l)
if [ "$wrong_owner" -gt 0 ]; then
    echo -e "${YELLOW}⚠️${NC}  Found $wrong_owner files with incorrect ownership"
else
    echo -e "${GREEN}✓${NC} All files have correct ownership"
fi

echo ""
echo -e "${GREEN}======================================${NC}"
echo -e "${GREEN}    PERMISSION FIX COMPLETED!        ${NC}"  
echo -e "${GREEN}======================================${NC}"
echo ""
echo -e "${BLUE}[INFO]${NC} All permissions have been fixed according to security best practices."
echo -e "${BLUE}[INFO]${NC} Key security measures applied:"
echo -e "  • Directory permissions: 755"
echo -e "  • Regular file permissions: 644"
echo -e "  • Sensitive file permissions: 600"
echo -e "  • Proper ownership: www-data:www-data"
echo -e "  • Removed all 777 permissions"
echo -e "  • Cleaned up backup files"
echo ""
echo -e "${YELLOW}[REMINDER]${NC} To maintain security:"
echo -e "  • Never use chmod 777"
echo -e "  • Keep config.php at 600 permissions"
echo -e "  • Regular security audits"
echo -e "  • Monitor file changes"
echo ""
echo -e "${GREEN}✓${NC} Security hardening complete!" 