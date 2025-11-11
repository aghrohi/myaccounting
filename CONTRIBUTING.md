# 🤝 Contributing to PHP Accounting Application

First off, thank you for considering contributing to PHP Accounting Application! It's people like you that make this project better for everyone.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)

---

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inspiring community for all. Please be respectful and constructive in your interactions.

### Expected Behavior

- ✅ Be respectful and inclusive
- ✅ Welcome newcomers and help them learn
- ✅ Be open to constructive criticism
- ✅ Focus on what is best for the community
- ✅ Show empathy towards other community members

### Unacceptable Behavior

- ❌ Harassment or discriminatory language
- ❌ Trolling or insulting comments
- ❌ Publishing others' private information
- ❌ Other unprofessional conduct

---

## How Can I Contribute?

### 🐛 Reporting Bugs

Before creating bug reports, please check existing issues. When creating a bug report, include:

- **Clear title** describing the issue
- **Steps to reproduce** the behavior
- **Expected behavior** vs actual behavior
- **Screenshots** if applicable
- **Environment details:**
  - PHP version
  - MySQL version
  - Web server (Apache/Nginx)
  - Browser and version
  - Operating system

**Example:**

```markdown
**Bug:** Transaction total calculation incorrect

**Steps to Reproduce:**
1. Create a new expense transaction
2. Enter amount: 100.50
3. Click save
4. View account balance

**Expected:** Balance should decrease by 100.50
**Actual:** Balance decreases by 100.00

**Environment:**
- PHP 7.4.3
- MySQL 8.0.23
- Apache 2.4.41
- Chrome 96.0.4664.110
- Ubuntu 20.04
```

### 💡 Suggesting Enhancements

Enhancement suggestions are welcome! Please provide:

- **Clear title** for the enhancement
- **Detailed description** of the proposed feature
- **Use cases** explaining why this would be useful
- **Possible implementation** ideas (optional)
- **Screenshots/mockups** if applicable

### 📝 Contributing Code

1. **Fork the repository**
2. **Create a feature branch** from `develop`
3. **Make your changes**
4. **Test thoroughly**
5. **Submit a pull request**

### 📖 Improving Documentation

Documentation improvements are always welcome:

- Fix typos or clarify instructions
- Add examples or use cases
- Translate documentation
- Create video tutorials
- Write blog posts

---

## Development Setup

### Prerequisites

- PHP 7.4+ with extensions: `pdo_mysql`, `mbstring`, `json`
- MySQL 5.7+ or MariaDB 10.2+
- Composer (for dependencies, if any)
- Git

### Setup Steps

1. **Fork and clone:**
   ```bash
   git clone https://github.com/YOUR_USERNAME/accounting-app.git
   cd accounting-app
   ```

2. **Create database:**
   ```bash
   mysql -u root -p
   ```
   ```sql
   CREATE DATABASE accounting_app_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'dev_user'@'localhost' IDENTIFIED BY 'dev_password';
   GRANT ALL PRIVILEGES ON accounting_app_dev.* TO 'dev_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Import schema:**
   ```bash
   mysql -u dev_user -p accounting_app_dev < database.sql
   ```

4. **Configure:**
   ```bash
   cp db_connect.example.php db_connect.php
   nano db_connect.php  # Edit with your credentials
   ```

5. **Enable error reporting** (development only):
   ```php
   // Add to index.php during development
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

6. **Start development server:**
   ```bash
   php -S localhost:8000
   ```

### Project Structure

```
accounting-app/
├── index.php           # Main controller/router
├── db_connect.php      # Database configuration
├── functions.php       # Helper functions
├── backup.php          # Backup handler
├── export_csv.php      # CSV export handler
├── style.css           # Main stylesheet
├── database.sql        # Database schema
└── pages/              # View templates
    ├── dashboard.php
    ├── transactions.php
    ├── accounts.php
    └── ...
```

---

## Coding Standards

### PHP Standards

We follow **PSR-12** coding standards with some project-specific conventions.

#### General Guidelines

```php
<?php
// ✅ GOOD: Clear, descriptive names
function calculateAccountBalance($accountId) {
    // Implementation
}

// ❌ BAD: Unclear abbreviations
function calcAccBal($aid) {
    // Implementation
}
```

#### Naming Conventions

- **Variables:** `$camelCase`
- **Functions:** `camelCase()`
- **Constants:** `UPPER_CASE`
- **Database tables:** `snake_case`
- **Classes:** `PascalCase` (if using OOP in future)

#### Code Formatting

```php
<?php
// ✅ GOOD: Proper spacing and indentation
if ($user['role'] === 'admin') {
    echo "Welcome, Administrator!";
} else {
    echo "Welcome, User!";
}

// ✅ GOOD: Clear SQL with prepared statements
$stmt = $pdo->prepare("
    SELECT * FROM transactions 
    WHERE account_id = ? 
    AND date BETWEEN ? AND ?
    ORDER BY date DESC
");
$stmt->execute([$accountId, $startDate, $endDate]);

// ❌ BAD: Poor spacing and no preparation
$result=mysqli_query($conn,"SELECT * FROM transactions WHERE account_id=".$id);
```

#### Security Requirements

```php
// ✅ ALWAYS use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// ✅ ALWAYS sanitize output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// ✅ ALWAYS validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email");
}

// ❌ NEVER use raw SQL with user input
$query = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

### HTML/CSS Standards

```html
<!-- ✅ GOOD: Semantic HTML with proper indentation -->
<div class="transaction-card">
    <h3 class="transaction-title"><?= htmlspecialchars($title) ?></h3>
    <p class="transaction-amount">$<?= number_format($amount, 2) ?></p>
</div>

<!-- ❌ BAD: Non-semantic, inline styles -->
<div style="color:red;">
    <span><?=$title?></span>
</div>
```

### JavaScript Standards

```javascript
// ✅ GOOD: Modern ES6+ syntax
const submitForm = async (formData) => {
    try {
        const response = await fetch('/api/endpoint', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
};

// ❌ BAD: Old syntax, no error handling
function submitForm() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/endpoint');
    xhr.send();
}
```

### SQL Standards

```sql
-- ✅ GOOD: Clear, formatted SQL
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE RESTRICT,
    INDEX idx_account_date (account_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ❌ BAD: Unclear, poor formatting
CREATE TABLE trans (id int, acc int, amt decimal(10,2), desc text);
```

---

## Commit Guidelines

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, no logic change)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```bash
# Good commit messages
git commit -m "feat(transactions): add CSV export functionality"
git commit -m "fix(auth): resolve session timeout issue"
git commit -m "docs(readme): update installation instructions"

# Bad commit messages
git commit -m "fixed stuff"
git commit -m "updates"
git commit -m "WIP"
```

### Detailed Commit

```
feat(reports): add balance sheet report

- Implement balance sheet calculation logic
- Add new report template
- Create date range selector
- Add export to PDF option

Closes #123
```

---

## Pull Request Process

### Before Submitting

- ✅ Test your changes thoroughly
- ✅ Update documentation if needed
- ✅ Ensure code follows style guidelines
- ✅ Write clear commit messages
- ✅ Rebase on latest `develop` branch

### Pull Request Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
How has this been tested?

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No new warnings generated
- [ ] Tests added/updated
- [ ] All tests passing

## Screenshots (if applicable)
Add screenshots here

## Related Issues
Closes #123
```

### Review Process

1. **Automated checks** run (if configured)
2. **Code review** by maintainers
3. **Requested changes** (if needed)
4. **Approval** from at least one maintainer
5. **Merge** into appropriate branch

### After Merge

- Your branch will be deleted
- Changes will be included in next release
- You'll be added to contributors list
- Thank you! 🎉

---

## Branch Strategy

```
main (production)
  └── develop (integration)
       ├── feature/new-feature
       ├── fix/bug-fix
       └── docs/documentation-update
```

- **`main`**: Production-ready code
- **`develop`**: Integration branch
- **`feature/*`**: New features
- **`fix/*`**: Bug fixes
- **`docs/*`**: Documentation updates

---

## Testing Guidelines

### Manual Testing

Before submitting, test:

1. **Core Functionality:**
   - Create/edit/delete transactions
   - Account balance calculations
   - Report generation
   - User authentication

2. **Edge Cases:**
   - Empty inputs
   - Special characters
   - Large numbers
   - Concurrent users

3. **Browsers:**
   - Chrome
   - Firefox
   - Safari
   - Edge

### Security Testing

- SQL injection attempts
- XSS vulnerability checks
- CSRF token validation
- Session handling

---

## Getting Help

- 💬 **Discussions:** Ask questions in GitHub Discussions
- 📧 **Email:** contribute@example.com
- 📖 **Documentation:** Read the wiki
- 🐛 **Issues:** Search existing issues first

---

## Recognition

Contributors are recognized in:

- README.md contributors section
- Release notes
- GitHub contributors page

---

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

---

**Thank you for contributing! 🎉**

Every contribution, no matter how small, makes a difference.

[⬆ Back to README](README.md)
