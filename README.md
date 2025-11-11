# PHP Accounting Application v2.0
## Professional Financial Management System

A modern, secure, and feature-rich accounting application built with PHP and MySQL. This system provides comprehensive financial tracking with a beautiful, responsive interface.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg)
![MySQL](https://img.shields.io/badge/MySQL-%3E%3D5.7-4479A1.svg)

## 🌟 Features

### Core Functionality
- **Multi-Account Management**: Track multiple bank accounts, credit cards, investments
- **Transaction Tracking**: Record income and expenses with detailed categorization
- **Double-Entry Accounting**: Proper accounting with source and destination accounts
- **Real-time Balance Updates**: Automatic balance calculations
- **Multi-Currency Support**: Handle transactions in different currencies

### Advanced Features
- **User Management**: Role-based access control (Admin/User)
- **Audit Trail**: Complete logging of all system activities
- **Financial Reports**: Income/Expense, Cash Flow, Balance Sheet
- **Budget Management**: Set and track category budgets
- **Tax Tracking**: Mark tax-deductible expenses
- **Data Export**: Export reports to CSV format
- **Dark Mode**: Modern light/dark theme toggle

### Security Features
- **Secure Authentication**: Password hashing with bcrypt
- **Session Management**: Secure session handling
- **CSRF Protection**: Token-based form protection
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Input sanitization and output encoding
- **Audit Logging**: Track all user activities

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache/Nginx)
- Modern web browser

## 🚀 Installation

### Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE accounting_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p accounting_app < database.sql
```

### Step 2: Configure Database Connection

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

### Step 3: File Setup

1. Copy all files to your web server directory:
```bash
/var/www/html/accounting/
├── index.php
├── db_connect.php
├── functions.php
├── style.css
├── pages/
│   ├── login.php
│   ├── dashboard.php
│   ├── transactions.php
│   ├── accounts.php
│   ├── categories.php
│   ├── reports.php
│   ├── users.php
│   ├── currencies.php
│   ├── holders.php
│   ├── audit.php
│   ├── settings.php
│   └── 404.php
└── database.sql
```

2. Set appropriate permissions:
```bash
chmod 755 /var/www/html/accounting
chmod 644 /var/www/html/accounting/*.php
```

### Step 4: Access the Application

Navigate to: `http://your-server/accounting/`

## 👤 Default Login Credentials

### Administrator Account
- **Username**: admin
- **Password**: Admin@123

### Regular User Account
- **Username**: user1
- **Password**: User@123

⚠️ **Important**: Change these passwords immediately after first login!

## 📱 User Guide

### Getting Started

1. **Login**: Use the provided credentials to access the system
2. **Dashboard**: View your financial overview and recent transactions
3. **Add Accounts**: Set up your bank accounts, credit cards, etc.
4. **Create Categories**: Define income and expense categories
5. **Record Transactions**: Start tracking your financial activities

### Account Types

- **Checking**: Regular bank checking accounts
- **Savings**: Savings accounts
- **Credit Card**: Credit card accounts (negative balance)
- **Investment**: Investment and brokerage accounts
- **Cash**: Physical cash tracking
- **Loan**: Loan accounts
- **Asset**: Fixed assets
- **Other**: Custom account types

### Transaction Types

- **Income**: Money coming in (salary, freelance, interest)
- **Expense**: Money going out (rent, groceries, utilities)
- **Transfer**: Moving money between accounts

### Reports

- **Income & Expense Report**: Monthly breakdown by category
- **Cash Flow Report**: Daily money movement analysis
- **Balance Sheet**: Assets vs. liabilities overview
- **Tax Report**: Tax-deductible expense tracking

## 🛠️ Technical Architecture

### Database Schema

The application uses a normalized MySQL database with the following main tables:

- `users`: User accounts and authentication
- `currencies`: Multi-currency support
- `account_holders`: Account owner information
- `accounts`: Bank accounts and financial accounts
- `categories`: Income/expense categories
- `transactions`: Financial transactions
- `audit_log`: System activity tracking

### Security Implementation

- **Password Security**: Using PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: `htmlspecialchars()` for all output
- **CSRF Protection**: Token validation on forms
- **Session Security**: Secure session configuration

### Code Structure

```
MVC-like Architecture:
- index.php: Main controller and router
- db_connect.php: Database connection and configuration
- functions.php: Helper functions and utilities
- pages/: Individual page views
- style.css: Complete styling with CSS variables
```

## 🎨 UI Features

- **Responsive Design**: Works on desktop, tablet, and mobile
- **Modern Interface**: Clean, professional design
- **Dark Mode**: Toggle between light and dark themes
- **Interactive Charts**: Visual representation of financial data
- **Real-time Search**: Quick transaction and account search
- **Pagination**: Efficient handling of large datasets

## 🔧 Configuration

### Environment Variables

Set these in `db_connect.php`:

```php
define('ENVIRONMENT', 'production'); // or 'development'
```

### Customization

- **Theme Colors**: Edit CSS variables in `style.css`
- **Currency**: Add new currencies in the admin panel
- **Categories**: Customize income/expense categories
- **Account Types**: Modify available account types in code

## 📊 API Endpoints (Future Development)

The application structure supports future API development:

- `/api/accounts` - Account management
- `/api/transactions` - Transaction operations
- `/api/reports` - Report generation
- `/api/auth` - Authentication

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `db_connect.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **Login Issues**
   - Check password requirements (8+ chars, mixed case, numbers)
   - Verify user is active in database
   - Clear browser cookies/session

3. **Balance Discrepancies**
   - Run balance recalculation query
   - Check for unreconciled transactions
   - Verify starting balances

### Debug Mode

Enable debug mode in `db_connect.php`:

```php
define('ENVIRONMENT', 'development');
```

## 🚦 Roadmap

### Version 2.1 (Planned)
- [ ] Receipt attachment support
- [ ] Recurring transactions
- [ ] Advanced search filters
- [ ] Mobile app API
- [ ] Email notifications

### Version 2.2 (Future)
- [ ] Bank sync integration
- [ ] Advanced reporting dashboard
- [ ] Multi-language support
- [ ] Backup scheduling
- [ ] Two-factor authentication

## 📄 License

This project is licensed under the MIT License - see below for details:

```
MIT License

Copyright (c) 2025 PHP Accounting App

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 💬 Support

For support, please create an issue in the repository or contact the development team.

## 🙏 Acknowledgments

- Font Awesome for icons
- Chart.js for data visualization
- Inter font family for typography

---

**Built with ❤️ using PHP and MySQL**