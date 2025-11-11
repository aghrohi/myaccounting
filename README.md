PHP Accounting Application v2.0Professional Financial Management SystemA modern, secure, and feature-rich accounting application built with PHP and MySQL. This system provides comprehensive financial tracking with a beautiful, responsive interface.🌟 FeaturesCore FunctionalityMulti-Account Management: Track multiple bank accounts, credit cards, investmentsTransaction Tracking: Record income and expenses with detailed categorizationDouble-Entry Accounting: Proper accounting with source and destination accountsReal-time Balance Updates: Automatic balance calculationsMulti-Currency Support: Handle transactions in different currenciesAdvanced FeaturesUser Management: Role-based access control (Admin/User)Audit Trail: Complete logging of all system activitiesFinancial Reports: Income/Expense, Cash Flow, Balance SheetAJAX Reconciliation: Mark transactions as reconciled without a page reload.Database Backup: Admins can download a full .sql database backup from the settings panel.CSV Export: Export filtered transaction lists to a CSV file.Dark Mode: Modern light/dark theme toggleSecurity FeaturesSecure Authentication: Password hashing with bcryptSession Management: Secure, HttpOnly, and Samesite session handlingSQL Injection Prevention: Prepared statements used throughoutXSS Protection: Input sanitization and output encodingAudit Logging: Track all user activities📋 RequirementsPHP 7.4 or higherMySQL 5.7+ or MariaDB 10.2+Web server (Apache/Nginx)mysqldump utility on the server (for the backup feature)Modern web browser🚀 InstallationStep 1: Database SetupCreate a new MySQL database:CREATE DATABASE accounting_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
Import the database schema using the provided database.sql file:mysql -u your_username -p accounting_app < database.sql
Step 2: Configure Database ConnectionEdit db_connect.php with your database credentials:$db_config = [
    'host'     => '127.0.0.1',
    'port'     => '3306',
    'database' => 'accounting_app',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset'  => 'utf8mb4',
];
Step 3: File StructureUpload the application files to your web server (e.g., /var/www/html/accounting/) with the following structure:/accounting/
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
Step 4: Access the ApplicationNavigate to: http://your-server/accounting/👤 Default Login CredentialsImportant: Change these passwords immediately after your first login!Account TypeUsernamePasswordAdministratoradminAdmin@123Regular Useruser1User@123🛠️ Technical ArchitectureCode StructureThe application uses a simple and effective "MVC-like" structure:index.php: Acts as the main controller, handling all routing, authentication, and AJAX requests.db_connect.php: Manages the PDO database connection and environment configuration.functions.php: A library of helper functions for authentication, validation, formatting, and database queries.backup.php / export_csv.php: Standalone scripts that handle file generation and download.pages/: Contains all view "templates." The index.php controller includes the correct file from this directory based on the ?page= URL parameter.🐛 TroubleshootingDatabase Connection ErrorCheck database credentials in db_connect.php.Ensure your MySQL service is running.Verify the database accounting_app exists and the user has permissions.Login IssuesVerify you are using the correct default credentials (admin / Admin@123).Clear your browser cookies and session.Backup FailsEnsure the mysqldump command-line utility is installed on your web server and accessible in the system's PATH.Check that the PHP passthru() function is not disabled in your php.ini.🚦 RoadmapVersion 2.1 (Planned)[ ] Receipt attachment uploads[ ] Recurring transactions[ ] Advanced search filters[ ] Mobile app API[ ] Email notificationsVersion 2.2 (Future)[ ] Bank sync integration[ ] Advanced reporting dashboard[ ] Multi-language support[ ] Two-factor authentication📄 LicenseThis project is licensed under the MIT License. See the LICENSE file for details.
