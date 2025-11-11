# 💰 PHP Accounting Application v2.0

> **Professional Financial Management System**

A modern, secure, and feature-rich accounting application built with PHP and MySQL. This system provides comprehensive financial tracking with a beautiful, responsive interface.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Version](https://img.shields.io/badge/Version-2.0-blue?style=flat-square)

---

## 📸 Screenshots

*Add your application screenshots here*

---

## ✨ Features

### 🎯 Core Functionality

- ✅ **Multi-Account Management** - Track multiple bank accounts, credit cards, investments
- 💸 **Transaction Tracking** - Record income and expenses with detailed categorization
- 📊 **Double-Entry Accounting** - Proper accounting with source and destination accounts
- 🔄 **Real-time Balance Updates** - Automatic balance calculations
- 🌍 **Multi-Currency Support** - Handle transactions in different currencies

### 🚀 Advanced Features

- 👥 **User Management** - Role-based access control (Admin/User)
- 📝 **Audit Trail** - Complete logging of all system activities
- 📈 **Financial Reports** - Income/Expense, Cash Flow, Balance Sheet
- ⚡ **AJAX Reconciliation** - Mark transactions as reconciled without page reload
- 💾 **Database Backup** - Admins can download full .sql database backup
- 📤 **CSV Export** - Export filtered transaction lists to CSV file
- 🌓 **Dark Mode** - Modern light/dark theme toggle

### 🔒 Security Features

- 🔐 **Secure Authentication** - Password hashing with bcrypt
- 🛡️ **Session Management** - Secure, HttpOnly, and SameSite session handling
- 💉 **SQL Injection Prevention** - Prepared statements used throughout
- 🧹 **XSS Protection** - Input sanitization and output encoding
- 📋 **Audit Logging** - Track all user activities

---

## 📋 Requirements

| Requirement | Version |
|------------|---------|
| PHP | 7.4 or higher |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| Web Server | Apache / Nginx |
| mysqldump | Required for backup feature |
| Browser | Modern (Chrome, Firefox, Safari, Edge) |

---

## 🚀 Installation

### Step 1️⃣: Database Setup

Create a new MySQL database:

```sql
CREATE DATABASE accounting_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import the database schema:

```bash
mysql -u your_username -p accounting_app < database.sql
```

### Step 2️⃣: Configure Database Connection

Edit `db_connect.php` with your database credentials:

```php
$db_config = [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'accounting_app',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset'  => 'utf8mb4',
];
```

### Step 3️⃣: File Structure

Upload the application files to your web server (e.g., `/var/www/html/accounting/`):

```
📁 accounting/
├── 📄 index.php             # Main Controller/Router
├── 📄 db_connect.php        # Database Connection & Config
├── 📄 functions.php         # Core Application Functions
├── 📄 backup.php            # Database Backup Handler
├── 📄 export_csv.php        # Transaction CSV Export Handler
├── 🎨 style.css             # Main Stylesheet
├── 🗄️ database.sql          # Database Schema
├── 📖 README.md             # Documentation
├── 📜 LICENSE               # MIT License
└── 📁 pages/
    ├── 📄 404.php           # Not Found Page
    ├── 📄 accounts.php      # Accounts Management
    ├── 📄 audit.php         # Admin Audit Log
    ├── 📄 categories.php    # Category Management
    ├── 📄 currencies.php    # Admin Currency Management
    ├── 📄 dashboard.php     # Main Dashboard
    ├── 📄 holders.php       # Admin Account Holders
    ├── 📄 login.php         # Login Page
    ├── 📄 profile.php       # User Profile
    ├── 📄 reports.php       # Financial Reports
    ├── 📄 settings.php      # User & Admin Settings
    ├── 📄 transactions.php  # Transactions List
    └── 📄 users.php         # Admin User Management
```

### Step 4️⃣: Set Permissions

Ensure proper file permissions:

```bash
# Set directory permissions
chmod 755 /var/www/html/accounting/
chmod 755 /var/www/html/accounting/pages/

# Set file permissions
chmod 644 /var/www/html/accounting/*.php
chmod 644 /var/www/html/accounting/pages/*.php
chmod 644 /var/www/html/accounting/style.css

# Secure sensitive files
chmod 600 /var/www/html/accounting/db_connect.php
```

### Step 5️⃣: Access the Application

Navigate to: `http://your-server/accounting/`

---

## 👤 Default Login Credentials

> ⚠️ **Important:** Change these passwords immediately after your first login!

| Account Type | Username | Password |
|-------------|----------|----------|
| 🔑 Administrator | `admin` | `Admin@123` |
| 👤 Regular User | `user1` | `User@123` |

---

## 🏗️ Technical Architecture

### Code Structure

The application uses a simple and effective "MVC-like" structure:

- **`index.php`** - Acts as the main controller, handling all routing, authentication, and AJAX requests
- **`db_connect.php`** - Manages the PDO database connection and environment configuration
- **`functions.php`** - Library of helper functions for authentication, validation, formatting, and database queries
- **`backup.php`** / **`export_csv.php`** - Standalone scripts for file generation and download
- **`pages/`** - Contains all view templates included by the controller based on `?page=` URL parameter

### Database Schema

The application uses the following main tables:

- **`users`** - User accounts and authentication
- **`accounts`** - Bank accounts and financial accounts
- **`transactions`** - Financial transactions (double-entry)
- **`categories`** - Transaction categories
- **`currencies`** - Supported currencies
- **`account_holders`** - Account holder information
- **`audit_log`** - System activity tracking

---

## 🔧 Configuration

### Environment Settings

Edit `db_connect.php` to configure:

```php
// Database connection
$db_config = [...];

// Application settings
define('APP_NAME', 'Accounting App');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('ENABLE_AUDIT_LOG', true);
```

### Web Server Configuration

#### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?page=$1 [L,QSA]
```

#### Nginx

```nginx
location /accounting/ {
    try_files $uri $uri/ /accounting/index.php?$args;
}
```

---

## 🐛 Troubleshooting

### ❌ Database Connection Error

- ✅ Check database credentials in `db_connect.php`
- ✅ Ensure MySQL service is running: `sudo systemctl status mysql`
- ✅ Verify database exists: `SHOW DATABASES;`
- ✅ Check user permissions: `SHOW GRANTS FOR 'username'@'localhost';`

### ❌ Login Issues

- ✅ Verify default credentials: `admin` / `Admin@123`
- ✅ Clear browser cookies and session
- ✅ Check if users table is populated: `SELECT * FROM users;`

### ❌ Backup Fails

- ✅ Ensure `mysqldump` is installed: `which mysqldump`
- ✅ Check PHP functions not disabled: `passthru()`, `exec()`
- ✅ Verify database user has sufficient privileges

### ❌ Permission Denied Errors

```bash
# Fix file ownership
sudo chown -R www-data:www-data /var/www/html/accounting/

# Fix permissions
sudo chmod -R 755 /var/www/html/accounting/
```

---

## 🎯 Usage Guide

### Adding a Transaction

1. Navigate to **Transactions** → **Add New**
2. Select transaction type (Income/Expense/Transfer)
3. Choose source and destination accounts
4. Enter amount, date, and description
5. Assign category and add notes
6. Click **Save Transaction**

### Generating Reports

1. Go to **Reports** page
2. Select report type (Income/Expense, Cash Flow, Balance Sheet)
3. Choose date range
4. Apply filters (optional)
5. View or export report

### Creating Database Backup

1. Login as administrator
2. Navigate to **Settings** → **Admin Settings**
3. Click **Download Database Backup**
4. Save the `.sql` file securely

---

## 🗺️ Roadmap

### 📅 Version 2.1 (Planned)

- [ ] 📎 Receipt attachment uploads
- [ ] 🔄 Recurring transactions
- [ ] 🔍 Advanced search filters
- [ ] 📱 Mobile app API
- [ ] 📧 Email notifications

### 📅 Version 2.2 (Future)

- [ ] 🏦 Bank sync integration
- [ ] 📊 Advanced reporting dashboard
- [ ] 🌐 Multi-language support
- [ ] 🔐 Two-factor authentication
- [ ] 📈 Budget planning module

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 Changelog

### Version 2.0 (Current)
- ✨ Complete UI redesign with dark mode
- 🔒 Enhanced security features
- 📊 Advanced reporting system
- 💾 Database backup functionality
- 📤 CSV export feature
- ⚡ AJAX-based reconciliation

### Version 1.0
- 🎉 Initial release
- 💰 Basic accounting features
- 👥 User management
- 📝 Transaction tracking

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 PHP Accounting Application

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction...
```

---

## 👨‍💻 Authors & Contributors

- **Project Lead** - Initial work and maintenance

---

## 🙏 Acknowledgments

- Built with ❤️ using PHP and MySQL
- Icons from various open-source icon libraries
- Inspired by modern accounting software

---

## 📞 Support

Need help? Have questions?

- 📧 **Email:** support@example.com
- 🐛 **Bug Reports:** [GitHub Issues](https://github.com/yourusername/accounting-app/issues)
- 💬 **Discussions:** [GitHub Discussions](https://github.com/yourusername/accounting-app/discussions)
- 📖 **Documentation:** [Wiki](https://github.com/yourusername/accounting-app/wiki)

---

## ⭐ Show Your Support

If this project helped you, please give it a ⭐ star on GitHub!

---

<div align="center">

**Made with ❤️ by developers, for developers**

[⬆ Back to Top](#-php-accounting-application-v20)

</div>
