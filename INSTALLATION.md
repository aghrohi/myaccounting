# 📦 Installation Guide

This guide provides detailed step-by-step instructions for installing the PHP Accounting Application on various environments.

---

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation Methods](#installation-methods)
  - [Method 1: Manual Installation](#method-1-manual-installation)
  - [Method 2: Docker Installation](#method-2-docker-installation)
  - [Method 3: XAMPP/WAMP Installation](#method-3-xamppwamp-installation)
- [Post-Installation](#post-installation)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before installation, ensure you have:

- ✅ PHP 7.4 or higher with extensions:
  - `pdo_mysql`
  - `mbstring`
  - `json`
  - `session`
- ✅ MySQL 5.7+ or MariaDB 10.2+
- ✅ Web server (Apache or Nginx)
- ✅ `mysqldump` utility (for backup feature)
- ✅ Command-line access (for initial setup)

---

## Installation Methods

### Method 1: Manual Installation

#### Step 1: Download and Extract

```bash
# Download the application
wget https://github.com/yourusername/accounting-app/archive/main.zip

# Extract
unzip main.zip
mv accounting-app-main /var/www/html/accounting

# Set ownership
sudo chown -R www-data:www-data /var/www/html/accounting
```

#### Step 2: Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE accounting_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create user (optional but recommended)
CREATE USER 'accounting_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON accounting_app.* TO 'accounting_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 3: Import Schema

```bash
cd /var/www/html/accounting
mysql -u accounting_user -p accounting_app < database.sql
```

#### Step 4: Configure Application

```bash
# Copy example config (if provided)
cp db_connect.example.php db_connect.php

# Edit configuration
nano db_connect.php
```

Edit the database credentials:

```php
$db_config = [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'accounting_app',
    'username' => 'accounting_user',
    'password' => 'strong_password_here',
    'charset'  => 'utf8mb4',
];
```

#### Step 5: Set Permissions

```bash
# Set directory permissions
chmod 755 /var/www/html/accounting
chmod 755 /var/www/html/accounting/pages

# Set file permissions
chmod 644 /var/www/html/accounting/*.php
chmod 644 /var/www/html/accounting/pages/*.php
chmod 644 /var/www/html/accounting/style.css

# Secure sensitive config
chmod 600 /var/www/html/accounting/db_connect.php
```

#### Step 6: Configure Web Server

**For Apache:**

Create `/etc/apache2/sites-available/accounting.conf`:

```apache
<VirtualHost *:80>
    ServerName accounting.example.com
    DocumentRoot /var/www/html/accounting
    
    <Directory /var/www/html/accounting>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/accounting_error.log
    CustomLog ${APACHE_LOG_DIR}/accounting_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite accounting.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

**For Nginx:**

Create `/etc/nginx/sites-available/accounting`:

```nginx
server {
    listen 80;
    server_name accounting.example.com;
    root /var/www/html/accounting;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/accounting /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

### Method 2: Docker Installation

#### Step 1: Create Docker Compose File

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  web:
    image: php:7.4-apache
    container_name: accounting-web
    ports:
      - "8080:80"
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=accounting_app
      - DB_USER=root
      - DB_PASS=rootpassword

  db:
    image: mysql:8.0
    container_name: accounting-db
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: accounting_app
    volumes:
      - db_data:/var/lib/mysql
      - ./database.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"

volumes:
  db_data:
```

#### Step 2: Start Containers

```bash
docker-compose up -d
```

#### Step 3: Access Application

Navigate to: `http://localhost:8080`

---

### Method 3: XAMPP/WAMP Installation

#### For XAMPP (Windows/Linux/Mac):

1. **Install XAMPP** from [apachefriends.org](https://www.apachefriends.org/)

2. **Extract Application:**
   - Place files in `C:\xampp\htdocs\accounting\` (Windows)
   - Or `/opt/lampp/htdocs/accounting/` (Linux)

3. **Start Services:**
   - Open XAMPP Control Panel
   - Start Apache and MySQL

4. **Create Database:**
   - Navigate to `http://localhost/phpmyadmin`
   - Create new database: `accounting_app`
   - Import `database.sql` file

5. **Configure:**
   - Edit `db_connect.php`:
     ```php
     $db_config = [
         'host'     => 'localhost',
         'port'     => '3306',
         'database' => 'accounting_app',
         'username' => 'root',
         'password' => '', // Empty for XAMPP default
         'charset'  => 'utf8mb4',
     ];
     ```

6. **Access:**
   - Navigate to `http://localhost/accounting/`

#### For WAMP (Windows):

Similar to XAMPP:
1. Install WAMP from [wampserver.com](https://www.wampserver.com/)
2. Place files in `C:\wamp64\www\accounting\`
3. Follow steps 3-6 from XAMPP instructions

---

## Post-Installation

### Step 1: First Login

Navigate to your installation URL and login with:

- **Username:** `admin`
- **Password:** `Admin@123`

### Step 2: Change Default Passwords

⚠️ **CRITICAL:** Immediately change default passwords!

1. Login as `admin`
2. Go to **Settings** → **Profile**
3. Change password
4. Repeat for `user1` account

### Step 3: Configure Settings

1. Navigate to **Settings** → **Admin Settings**
2. Configure:
   - Application name
   - Default currency
   - Date format
   - Time zone

### Step 4: Add Initial Data

1. Add account holders
2. Create bank accounts
3. Set up categories
4. Add initial transactions

### Step 5: Test Backup Feature

1. Go to **Settings** → **Admin Settings**
2. Click **Download Database Backup**
3. Verify `.sql` file downloads correctly

---

## Troubleshooting

### Database Connection Failed

**Error:** "Could not connect to database"

**Solution:**
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u your_username -p -h 127.0.0.1

# Verify credentials in db_connect.php
```

### Permission Denied Errors

**Error:** "Permission denied" when accessing files

**Solution:**
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/html/accounting/

# Fix permissions
sudo chmod -R 755 /var/www/html/accounting/
sudo chmod 600 /var/www/html/accounting/db_connect.php
```

### Blank Page After Installation

**Error:** White screen or blank page

**Solution:**
```bash
# Enable error reporting temporarily
# Edit index.php and add at the top:
error_reporting(E_ALL);
ini_set('display_errors', 1);

# Check error logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

### mysqldump Not Found

**Error:** Backup fails with "mysqldump not found"

**Solution:**
```bash
# Install MySQL client tools
sudo apt-get install mysql-client  # Debian/Ubuntu
sudo yum install mysql             # RHEL/CentOS

# Add to PATH
export PATH=$PATH:/usr/bin
```

### Sessions Not Working

**Error:** "Cannot start session" or repeated logouts

**Solution:**
```bash
# Check session directory permissions
ls -la /var/lib/php/sessions/

# Fix if needed
sudo chown -R www-data:www-data /var/lib/php/sessions/
sudo chmod 1733 /var/lib/php/sessions/
```

---

## Security Recommendations

After installation:

1. ✅ Change all default passwords
2. ✅ Set `db_connect.php` permissions to 600
3. ✅ Enable HTTPS (SSL/TLS)
4. ✅ Keep PHP and MySQL updated
5. ✅ Regular database backups
6. ✅ Monitor audit logs
7. ✅ Disable directory listing
8. ✅ Use strong passwords (12+ characters)

---

## Getting Help

If you encounter issues:

- 📖 Check the [README.md](README.md)
- 🐛 Search [GitHub Issues](https://github.com/yourusername/accounting-app/issues)
- 💬 Ask in [Discussions](https://github.com/yourusername/accounting-app/discussions)
- 📧 Contact support

---

## Next Steps

After successful installation:

1. Read the [User Guide](USER_GUIDE.md)
2. Configure your accounts and categories
3. Start tracking transactions
4. Generate your first reports

---

**Installation complete! 🎉**

[⬆ Back to README](README.md)
