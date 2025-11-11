PHP Accounting Application v2.0
Professional Financial Management System
A modern, secure, and feature-rich accounting application built with PHP and MySQL. This system provides comprehensive financial tracking with a beautiful, responsive interface.
🌟 Features
Core Functionality
Multi-Account Management: Track multiple bank accounts, credit cards, investments
Transaction Tracking: Record income and expenses with detailed categorization
Double-Entry Accounting: Proper accounting with source and destination accounts
Real-time Balance Updates: Automatic balance calculations
Multi-Currency Support: Handle transactions in different currencies
Advanced Features
User Management: Role-based access control (Admin/User)
Audit Trail: Complete logging of all system activities
Financial Reports: Income/Expense, Cash Flow, Balance Sheet
AJAX Reconciliation: Mark transactions as reconciled without a page reload.
Database Backup: Admins can download a full .sql database backup from the settings panel.
CSV Export: Export filtered transaction lists to a CSV file.
Dark Mode: Modern light/dark theme toggle
Security Features
Secure Authentication: Password hashing with bcrypt
Session Management: Secure, HttpOnly, and Samesite session handling
SQL Injection Prevention: Prepared statements used throughout
XSS Protection: Input sanitization and output encoding
Audit Logging: Track all user activities
📋 Requirements
PHP 7.4 or higher
MySQL 5.7+ or MariaDB 10.2+
Web server (Apache/Nginx)
mysqldump utility on the server (for the backup feature)
Modern web browser
🚀 Installation
Step 1: Database Setup
Create a new MySQL database:
CREATE DATABASE accounting_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;



Import the database schema using the provided database.sql file:
mysql -u your_username -p accounting_app < database.sql



Step 2: Configure Database Connection
Edit db_connect.php with your database credentials:
$db_config = [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'accounting_app',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset'  => 'utf8mb4',
];



Step 3: File Structure
Upload the application files to your web server (e.g., /var/www/html/accounting/) with the following structure:
/accounting/
├── index.php             (Main Controller/Router)
├── db_connect.php        (Database Connection & Config)
├── functions.php         (Core Application Functions)
├── backup.php            (Database Backup Handler)
├── export_csv.php        (Transaction CSV Export Handler)
├── style.css             (Main Stylesheet)
├── database.sql          (Database Schema)
├── README.md             (This File)
├── LICENSE               (MIT License File)
└── pages/
    ├── 404.php           (Not Found Page)
    ├── accounts.php      (Accounts Management)
    ├── audit.php         (Admin Audit Log)
    ├── categories.php    (Category Management)
    ├── currencies.php    (Admin Currency Management)
    ├── dashboard.php     (Main Dashboard)
    ├── holders.php       (Admin Account Holders)
    ├── login.php         (Login Page)
    ├── profile.php       (User Profile)
    ├── reports.php       (Financial Reports)
    ├── settings.php      (User & Admin Settings)
    ├── transactions.php  (Transactions List)
    └── users.php         (Admin User Management)



Step 4: Access the Application
Navigate to: http://your-server/accounting/
👤 Default Login Credentials
Important: Change these passwords immediately after your first login!
| Account Type | Username | Password |
| Administrator | admin | Admin@123 |
| Regular User | user1 | User@123 |
🛠️ Technical Architecture
Code Structure
The application uses a simple and effective "MVC-like" structure:
index.php: Acts as the main controller, handling all routing, authentication, and AJAX requests.
db_connect.php: Manages the PDO database connection and environment configuration.
functions.php: A library of helper functions for authentication, validation, formatting, and database queries.
backup.php / export_csv.php: Standalone scripts that handle file generation and download.
pages/: Contains all view "templates." The index.php controller includes the correct file from this directory based on the ?page= URL parameter.
🐛 Troubleshooting
Database Connection Error
Check database credentials in db_connect.php.
Ensure your MySQL service is running.
Verify the database accounting_app exists and the user has permissions.
Login Issues
Verify you are using the correct default credentials (admin / Admin@123).
Clear your browser cookies and session.
Backup Fails
Ensure the mysqldump command-line utility is installed on your web server and accessible in the system's PATH.
Check that the PHP passthru() function is not disabled in your php.ini.
🚦 Roadmap
Version 2.1 (Planned)
$$ $$
Receipt attachment uploads
$$ $$
Recurring transactions
$$ $$
Advanced search filters
$$ $$
Mobile app API
$$ $$
Email notifications
Version 2.2 (Future)
$$ $$
Bank sync integration
$$ $$
Advanced reporting dashboard
$$ $$
Multi-language support
$$ $$
Two-factor authentication
📄 License
This project is licensed under the MIT License. See the LICENSE file for details.
