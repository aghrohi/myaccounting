# 📁 File Structure Documentation

This document describes the complete file structure of the PHP Accounting Application.

---

## Directory Structure

```
accounting-app/
│
├── 📄 index.php                    # Main controller and router
├── 📄 db_connect.php               # Database configuration
├── 📄 functions.php                # Helper functions library
├── 📄 backup.php                   # Database backup handler
├── 📄 export_csv.php               # CSV export handler
├── 🎨 style.css                    # Main stylesheet
│
├── 🗄️ database.sql                 # Database schema
│
├── 📖 README.md                    # Main documentation
├── 📋 CHANGELOG.md                 # Version history
├── 📦 INSTALLATION.md              # Installation guide
├── 🚀 DEPLOYMENT.md                # Deployment guide
├── 🤝 CONTRIBUTING.md              # Contribution guidelines
├── 🔒 SECURITY.md                  # Security policy
├── 📜 LICENSE                      # MIT License
├── 🙈 .gitignore                   # Git ignore file
│
└── 📁 pages/                       # View templates directory
    ├── 📄 404.php                  # Not found page
    ├── 📄 accounts.php             # Accounts management
    ├── 📄 audit.php                # Audit log (admin)
    ├── 📄 categories.php           # Category management
    ├── 📄 currencies.php           # Currency management (admin)
    ├── 📄 dashboard.php            # Main dashboard
    ├── 📄 holders.php              # Account holders (admin)
    ├── 📄 login.php                # Login page
    ├── 📄 profile.php              # User profile
    ├── 📄 reports.php              # Financial reports
    ├── 📄 settings.php             # Settings page
    ├── 📄 transactions.php         # Transaction list
    └── 📄 users.php                # User management (admin)
```

---

## Core Files

### `index.php`
**Purpose:** Main application controller and router

**Responsibilities:**
- Route handling based on `?page=` parameter
- User authentication and session management
- AJAX request handling
- Page rendering and template inclusion
- Global error handling

**Key Sections:**
```php
// Session & authentication
// Database connection
// Routing logic
// AJAX handlers
// Page rendering
```

---

### `db_connect.php`
**Purpose:** Database connection and configuration

**Contains:**
- PDO database connection
- Database credentials
- Environment configuration
- Error handling settings
- Security settings

**Security:** This file should have `chmod 600` permissions

---

### `functions.php`
**Purpose:** Reusable helper functions library

**Function Categories:**
- Authentication functions
- Validation functions
- Formatting functions (dates, currency)
- Database query helpers
- Calculation functions
- Utility functions

**Example Functions:**
```php
is_logged_in()
check_permission()
format_currency()
format_date()
calculate_account_balance()
get_user_by_id()
sanitize_input()
```

---

### `backup.php`
**Purpose:** Database backup generation and download

**Features:**
- Admin-only access check
- mysqldump execution
- SQL file generation
- Secure file download
- Temporary file cleanup

**Usage:** Called via AJAX from settings page

---

### `export_csv.php`
**Purpose:** Transaction CSV export

**Features:**
- Filter-aware export
- Proper CSV formatting
- UTF-8 BOM for Excel compatibility
- Secure download headers
- Permission checking

**Output Format:**
```csv
Date,Description,Category,Amount,Account,Type
2024-01-15,Grocery shopping,Food,-150.00,Checking,Expense
```

---

### `style.css`
**Purpose:** Main application stylesheet

**Sections:**
- Reset and base styles
- Layout and grid
- Navigation and header
- Forms and inputs
- Tables and lists
- Cards and panels
- Modals and dialogs
- Dark mode variables
- Responsive breakpoints
- Utility classes

**Features:**
- CSS variables for theming
- Dark mode support
- Responsive design
- Print styles
- Accessibility considerations

---

## Page Templates (`pages/` directory)

### `dashboard.php`
**Purpose:** Main dashboard with statistics and charts

**Displays:**
- Account balances summary
- Recent transactions
- Income vs Expense charts
- Monthly spending trends
- Quick action buttons

---

### `transactions.php`
**Purpose:** Transaction list and management

**Features:**
- Paginated transaction list
- Add/Edit/Delete transactions
- Filter by date, account, category
- Search functionality
- Bulk actions
- CSV export button

---

### `accounts.php`
**Purpose:** Bank accounts management

**Features:**
- List all accounts with balances
- Add/Edit/Delete accounts
- Account types (Checking, Savings, Credit Card, etc.)
- Currency selection
- Opening balance setup

---

### `categories.php`
**Purpose:** Transaction categories management

**Features:**
- Category list (Income/Expense)
- Add/Edit/Delete categories
- Category icons/colors
- Usage statistics

---

### `reports.php`
**Purpose:** Financial reports generation

**Available Reports:**
- Income & Expense Report
- Cash Flow Statement
- Balance Sheet
- Category Analysis
- Account Activity

**Features:**
- Date range selection
- Account filtering
- Export to PDF/CSV
- Print functionality

---

### `settings.php`
**Purpose:** User and admin settings

**User Settings:**
- Profile information
- Password change
- Notification preferences
- Display preferences

**Admin Settings:**
- Application configuration
- Database backup
- System information
- Audit log access

---

### `users.php` (Admin Only)
**Purpose:** User management interface

**Features:**
- User list
- Add/Edit/Delete users
- Role assignment
- Password reset
- User activity logs

---

### `audit.php` (Admin Only)
**Purpose:** System audit log viewer

**Displays:**
- All user activities
- Timestamp and user info
- Action details
- IP addresses
- Filtering and search

---

### `login.php`
**Purpose:** User authentication page

**Features:**
- Username/password form
- "Remember me" option
- Error messages
- Password reset link (if implemented)
- Clean, focused design

---

### `profile.php`
**Purpose:** User profile management

**Features:**
- Personal information
- Password change form
- Profile picture (if implemented)
- Activity summary
- Preference settings

---

### `holders.php` (Admin Only)
**Purpose:** Account holder management

**Features:**
- Holder information (name, contact, etc.)
- Associated accounts
- Add/Edit/Delete holders
- Document storage (if implemented)

---

### `currencies.php` (Admin Only)
**Purpose:** Currency management

**Features:**
- Supported currencies list
- Add/Edit currencies
- Exchange rates (if implemented)
- Default currency setting

---

### `404.php`
**Purpose:** Not found error page

**Displays:**
- User-friendly error message
- Navigation links
- Search functionality
- Return to dashboard link

---

## Database Schema (`database.sql`)

### Tables Overview

```sql
users              -- User accounts
accounts           -- Bank/financial accounts
transactions       -- Financial transactions
categories         -- Transaction categories
currencies         -- Supported currencies
account_holders    -- Account holder information
audit_log          -- System activity log
```

### Key Relationships

```
users ──┬──> transactions (created_by)
        └──> audit_log (user_id)

accounts ──┬──> transactions (source_account, dest_account)
           └──> account_holders (holder_id)

categories ──> transactions (category_id)

currencies ──> accounts (currency_id)
```

---

## Asset Files (Not Included)

If you add assets in the future, recommended structure:

```
assets/
├── css/
│   ├── style.css
│   └── dark-mode.css
├── js/
│   ├── main.js
│   └── charts.js
├── images/
│   ├── logo.png
│   └── icons/
└── fonts/
    └── custom-fonts/
```

---

## Configuration Files

### `.gitignore`
Prevents committing sensitive files:
- `db_connect.php`
- `*.sql` (backups)
- `.env` files
- IDE files
- Log files

### `.htaccess` (Apache)
```apache
# Redirect to index.php
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?page=$1 [L,QSA]

# Security headers
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
```

---

## File Permissions

### Recommended Permissions

```bash
# Directories
chmod 755 accounting/
chmod 755 accounting/pages/

# PHP files
chmod 644 *.php
chmod 644 pages/*.php

# Sensitive config
chmod 600 db_connect.php

# CSS/static files
chmod 644 style.css

# Ownership
chown -R www-data:www-data accounting/
```

---

## File Size Guidelines

| File Type | Typical Size | Max Recommended |
|-----------|--------------|-----------------|
| PHP files | 10-100 KB | 500 KB |
| CSS files | 20-100 KB | 200 KB |
| JS files | 10-50 KB | 150 KB |
| SQL dump | Varies | N/A |
| Images | 10-100 KB | 500 KB |

---

## Code Organization Best Practices

### Naming Conventions

**Files:**
- PHP: `snake_case.php` (e.g., `db_connect.php`)
- CSS: `kebab-case.css` (e.g., `dark-mode.css`)
- JS: `camelCase.js` (e.g., `mainController.js`)

**Functions:**
- PHP: `snake_case()` or `camelCase()`
- JavaScript: `camelCase()`

**Variables:**
- PHP: `$snake_case` or `$camelCase`
- JavaScript: `camelCase`

---

## Adding New Files

### Adding a New Page

1. Create `pages/new_feature.php`
2. Add route in `index.php`:
   ```php
   case 'new-feature':
       require 'pages/new_feature.php';
       break;
   ```
3. Add permission check if needed
4. Add navigation link in menu
5. Update documentation

### Adding New Functions

1. Add to `functions.php` in appropriate section
2. Document with PHPDoc comments:
   ```php
   /**
    * Brief description
    *
    * @param string $param1 Description
    * @return mixed Description
    */
   function my_new_function($param1) {
       // Implementation
   }
   ```

---

## Maintenance

### Regular Tasks

**Weekly:**
- Review error logs
- Check disk space
- Monitor database size

**Monthly:**
- Database optimization
- Backup verification
- Security updates

**Quarterly:**
- Code review
- Performance audit
- Documentation update

---

## Additional Resources

- [Installation Guide](INSTALLATION.md)
- [Deployment Guide](DEPLOYMENT.md)
- [Contributing Guidelines](CONTRIBUTING.md)
- [Security Policy](SECURITY.md)

---

[⬆ Back to README](README.md)
