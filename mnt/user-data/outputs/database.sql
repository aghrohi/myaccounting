-- Enhanced PHP Accounting App Database
-- Version 2.0 with improved structure and sample data
--
-- Database: accounting_app

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =========================================
-- Drop existing tables to prevent conflicts
-- =========================================
DROP TABLE IF EXISTS `transaction_attachments`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `account_holders`;
DROP TABLE IF EXISTS `currencies`;
DROP TABLE IF EXISTS `audit_log`;
DROP TABLE IF EXISTS `users`;

-- =========================================
-- Module 1a: Users and Rights Management
-- =========================================
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Encrypted using password_hash()',
  `full_name` varchar(100) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = normal user, 1 = admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0 = disabled, 1 = active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample users data
-- Default admin: username: admin, password: Admin@123
-- Default user: username: user1, password: User@123
INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `full_name`, `is_admin`, `is_active`) VALUES
(1, 'admin', 'admin@accounting.local', '$2y$10$YnN5Kz0xTWVhRldoaWNubeufHOSgpRnGvMFn8HKhJNBP0xelHKhGm', 'System Administrator', 1, 1),
(2, 'user1', 'john.doe@example.com', '$2y$10$0GVAY5Q9vYZGW1JxGJ0BLuKgPXBTz.7Q2FWVrLwX8oGQROcxoFQ/e', 'John Doe', 0, 1),
(3, 'jane_smith', 'jane.smith@example.com', '$2y$10$0GVAY5Q9vYZGW1JxGJ0BLuKgPXBTz.7Q2FWVrLwX8oGQROcxoFQ/e', 'Jane Smith', 0, 1);

-- =========================================
-- Module 1b: Currency Management
-- =========================================
CREATE TABLE `currencies` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(5) NOT NULL COMMENT 'ISO 4217 code',
  `currency_name` varchar(50) NOT NULL,
  `currency_symbol` varchar(10) DEFAULT NULL,
  `exchange_rate` decimal(10,4) DEFAULT 1.0000 COMMENT 'Exchange rate to base currency',
  `is_base_currency` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample currency data
INSERT INTO `currencies` (`currency_id`, `currency_code`, `currency_name`, `currency_symbol`, `exchange_rate`, `is_base_currency`) VALUES
(1, 'USD', 'US Dollar', '$', 1.0000, 1),
(2, 'EUR', 'Euro', '€', 0.9200, 0),
(3, 'GBP', 'British Pound', '£', 0.7900, 0),
(4, 'JPY', 'Japanese Yen', '¥', 149.5000, 0),
(5, 'CAD', 'Canadian Dollar', 'C$', 1.3600, 0);

-- =========================================
-- Module 1c: Account Holders
-- =========================================
CREATE TABLE `account_holders` (
  `holder_id` int(11) NOT NULL AUTO_INCREMENT,
  `holder_name` varchar(100) NOT NULL,
  `holder_type` enum('Personal','Joint','Business','Trust','Other') DEFAULT 'Personal',
  `tax_id` varchar(50) DEFAULT NULL COMMENT 'SSN/EIN for tax purposes',
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample account holders
INSERT INTO `account_holders` (`holder_id`, `holder_name`, `holder_type`, `tax_id`, `email`, `phone`) VALUES
(1, 'John Doe Personal', 'Personal', '123-45-6789', 'john.personal@example.com', '555-0101'),
(2, 'John & Jane Joint Account', 'Joint', NULL, 'joint@example.com', '555-0102'),
(3, 'Doe Consulting LLC', 'Business', '98-7654321', 'info@doeconsulting.com', '555-0103'),
(4, 'Doe Family Trust', 'Trust', '11-2233445', 'trust@doefamily.com', '555-0104');

-- =========================================
-- Module 2a: Account Management
-- =========================================
CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) DEFAULT NULL COMMENT 'Bank account number',
  `account_type` enum('Checking','Savings','Credit Card','Investment','Cash','Loan','Asset','Other') DEFAULT 'Checking',
  `holder_id` int(11) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `account_details` text DEFAULT NULL,
  `starting_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Calculated field',
  `creation_date` date NOT NULL,
  `currency_id` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `credit_limit` decimal(15,2) DEFAULT NULL COMMENT 'For credit cards',
  PRIMARY KEY (`account_id`),
  KEY `holder_id` (`holder_id`),
  KEY `currency_id` (`currency_id`),
  KEY `idx_account_type` (`account_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample accounts
INSERT INTO `accounts` (`account_id`, `account_name`, `account_number`, `account_type`, `holder_id`, `bank_name`, `account_details`, `starting_balance`, `current_balance`, `creation_date`, `currency_id`) VALUES
(1, 'Chase Personal Checking', '****1234', 'Checking', 1, 'Chase Bank', 'Primary checking account for daily expenses', 2500.00, 2500.00, '2025-01-01', 1),
(2, 'Chase High-Yield Savings', '****5678', 'Savings', 1, 'Chase Bank', 'Emergency fund account - 3.5% APY', 15000.00, 15000.00, '2025-01-01', 1),
(3, 'Amex Business Gold', '****9012', 'Credit Card', 3, 'American Express', 'Business credit card for company expenses', 0.00, -2341.50, '2025-01-15', 1),
(4, 'Petty Cash', NULL, 'Cash', 1, NULL, 'Cash on hand for small purchases', 250.00, 250.00, '2025-01-01', 1),
(5, 'Vanguard Investment', '****3456', 'Investment', 4, 'Vanguard', 'Index fund investments', 50000.00, 52315.25, '2024-06-01', 1),
(6, 'Wells Fargo Joint Checking', '****7890', 'Checking', 2, 'Wells Fargo', 'Joint account for shared expenses', 5000.00, 5000.00, '2025-01-01', 1);

-- =========================================
-- Module 2b: Category Management
-- =========================================
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_type` enum('credit','debit') NOT NULL COMMENT 'Credit = Income, Debit = Expense',
  `parent_category_id` int(11) DEFAULT NULL COMMENT 'For subcategories',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Icon class for UI',
  `color` varchar(7) DEFAULT NULL COMMENT 'Hex color for charts',
  `budget_amount` decimal(15,2) DEFAULT NULL COMMENT 'Monthly budget',
  `is_tax_deductible` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`category_id`),
  KEY `parent_category_id` (`parent_category_id`),
  KEY `idx_type` (`category_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample categories with better organization
INSERT INTO `categories` (`category_id`, `category_name`, `category_type`, `parent_category_id`, `icon`, `color`, `budget_amount`, `is_tax_deductible`) VALUES
-- Income Categories
(1, 'Salary & Wages', 'credit', NULL, 'briefcase', '#4CAF50', NULL, 0),
(2, 'Freelance & Contract', 'credit', NULL, 'laptop', '#8BC34A', NULL, 0),
(3, 'Investment Income', 'credit', NULL, 'trending-up', '#CDDC39', NULL, 0),
(4, 'Interest & Dividends', 'credit', 3, 'percent', '#FFEB3B', NULL, 0),
(5, 'Capital Gains', 'credit', 3, 'bar-chart', '#FFC107', NULL, 0),
(6, 'Other Income', 'credit', NULL, 'plus-circle', '#FF9800', NULL, 0),
-- Expense Categories
(7, 'Housing', 'debit', NULL, 'home', '#F44336', 2500.00, 0),
(8, 'Rent/Mortgage', 'debit', 7, 'key', '#E91E63', 2000.00, 1),
(9, 'Utilities', 'debit', 7, 'zap', '#9C27B0', 300.00, 0),
(10, 'Food & Dining', 'debit', NULL, 'utensils', '#673AB7', 800.00, 0),
(11, 'Groceries', 'debit', 10, 'shopping-cart', '#3F51B5', 500.00, 0),
(12, 'Restaurants', 'debit', 10, 'coffee', '#2196F3', 300.00, 0),
(13, 'Transportation', 'debit', NULL, 'car', '#03A9F4', 400.00, 0),
(14, 'Gas/Fuel', 'debit', 13, 'fuel', '#00BCD4', 200.00, 1),
(15, 'Auto Maintenance', 'debit', 13, 'tool', '#009688', 100.00, 1),
(16, 'Healthcare', 'debit', NULL, 'heart', '#4CAF50', 300.00, 1),
(17, 'Entertainment', 'debit', NULL, 'tv', '#8BC34A', 200.00, 0),
(18, 'Business Expenses', 'debit', NULL, 'briefcase', '#CDDC39', NULL, 1),
(19, 'Internal Transfer', 'debit', NULL, 'refresh-cw', '#607D8B', NULL, 0);

-- =========================================
-- Module 3: Transactions
-- =========================================
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_ref` varchar(50) DEFAULT NULL COMMENT 'Reference number',
  `source_account_id` int(11) DEFAULT NULL COMMENT 'NULL = External Income',
  `dest_account_id` int(11) DEFAULT NULL COMMENT 'NULL = External Expense',
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'User who entered transaction',
  `transaction_date` datetime NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `details` text DEFAULT NULL,
  `notes` text DEFAULT NULL COMMENT 'Internal notes',
  `is_reconciled` tinyint(1) DEFAULT 0,
  `reconciled_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`),
  KEY `source_account_id` (`source_account_id`),
  KEY `dest_account_id` (`dest_account_id`),
  KEY `idx_date` (`transaction_date`),
  KEY `idx_reconciled` (`is_reconciled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample transactions
INSERT INTO `transactions` (`transaction_ref`, `source_account_id`, `dest_account_id`, `category_id`, `user_id`, `transaction_date`, `amount`, `details`, `is_reconciled`) VALUES
-- Income transactions
('SAL-202501-001', NULL, 1, 1, 1, '2025-01-01 09:00:00', 5500.00, 'January Salary', 1),
('SAL-202501-002', NULL, 6, 1, 1, '2025-01-01 09:00:00', 3500.00, 'Jane January Salary', 1),
('FRL-202501-001', NULL, 1, 2, 1, '2025-01-05 14:30:00', 1250.00, 'Website Design Project', 1),
('INT-202501-001', NULL, 2, 4, 1, '2025-01-31 23:59:00', 43.75, 'Monthly Interest', 1),
-- Expense transactions
('EXP-202501-001', 1, NULL, 8, 1, '2025-01-02 10:00:00', 2000.00, 'January Rent', 1),
('EXP-202501-002', 1, NULL, 11, 1, '2025-01-03 17:45:00', 142.36, 'Walmart Groceries', 1),
('EXP-202501-003', 1, NULL, 11, 2, '2025-01-07 18:20:00', 89.54, 'Whole Foods', 0),
('EXP-202501-004', 4, NULL, 12, 2, '2025-01-08 12:30:00', 45.00, 'Lunch with client', 0),
('EXP-202501-005', 3, NULL, 18, 1, '2025-01-10 09:00:00', 599.00, 'Office supplies and software', 0),
('EXP-202501-006', 1, NULL, 14, 1, '2025-01-12 16:00:00', 65.00, 'Gas - Shell Station', 0),
('EXP-202501-007', 6, NULL, 9, 2, '2025-01-15 08:00:00', 125.43, 'Electric bill', 0),
('EXP-202501-008', 6, NULL, 9, 2, '2025-01-15 08:15:00', 68.99, 'Internet bill', 0),
-- Internal transfers
('TRF-202501-001', 1, 2, 19, 1, '2025-01-10 10:00:00', 1000.00, 'Monthly savings transfer', 1),
('TRF-202501-002', 6, 1, 19, 2, '2025-01-20 11:00:00', 500.00, 'Transfer for shared expenses', 0);

-- =========================================
-- Module 4: Audit Log
-- =========================================
CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, LOGIN',
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` JSON DEFAULT NULL,
  `new_values` JSON DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Transaction Attachments (for receipts)
-- =========================================
CREATE TABLE `transaction_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attachment_id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `uploaded_by` (`uploaded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- Add Foreign Key Constraints
-- =========================================
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_account_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`currency_id`),
  ADD CONSTRAINT `fk_account_holder` FOREIGN KEY (`holder_id`) REFERENCES `account_holders` (`holder_id`);

ALTER TABLE `categories`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `fk_tx_dest_account` FOREIGN KEY (`dest_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tx_source_account` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

ALTER TABLE `transaction_attachments`
  ADD CONSTRAINT `fk_attachment_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_attachment_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`);

-- =========================================
-- Create Views for Reporting
-- =========================================
CREATE VIEW `v_account_balances` AS
SELECT 
    a.account_id,
    a.account_name,
    a.account_type,
    ah.holder_name,
    c.currency_symbol,
    a.starting_balance,
    COALESCE(SUM(CASE 
        WHEN t.dest_account_id = a.account_id THEN t.amount
        WHEN t.source_account_id = a.account_id THEN -t.amount
        ELSE 0 
    END), 0) as transaction_total,
    (a.starting_balance + COALESCE(SUM(CASE 
        WHEN t.dest_account_id = a.account_id THEN t.amount
        WHEN t.source_account_id = a.account_id THEN -t.amount
        ELSE 0 
    END), 0)) as current_balance
FROM accounts a
LEFT JOIN account_holders ah ON a.holder_id = ah.holder_id
LEFT JOIN currencies c ON a.currency_id = c.currency_id
LEFT JOIN transactions t ON (t.source_account_id = a.account_id OR t.dest_account_id = a.account_id)
WHERE a.is_active = 1
GROUP BY a.account_id;

-- Update account balances trigger
DELIMITER $$
CREATE TRIGGER update_account_balance AFTER INSERT ON transactions
FOR EACH ROW
BEGIN
    IF NEW.source_account_id IS NOT NULL THEN
        UPDATE accounts 
        SET current_balance = current_balance - NEW.amount 
        WHERE account_id = NEW.source_account_id;
    END IF;
    
    IF NEW.dest_account_id IS NOT NULL THEN
        UPDATE accounts 
        SET current_balance = current_balance + NEW.amount 
        WHERE account_id = NEW.dest_account_id;
    END IF;
END$$
DELIMITER ;

COMMIT;

-- =========================================
-- Grant Permissions (adjust as needed)
-- =========================================
-- GRANT ALL PRIVILEGES ON accounting_app.* TO 'accounting_user'@'localhost' IDENTIFIED BY 'secure_password';
-- FLUSH PRIVILEGES;