-- Professional Accounting System Database
-- Enhanced version with improved structure and sample data
-- Database: accounting_app

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Drop existing tables if they exist
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `account_holders`;
DROP TABLE IF EXISTS `currencies`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Encrypted password',
  `is_admin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = normal user, 1 = admin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample users (password for both is: password123)
INSERT INTO `users` (`user_id`, `username`, `password_hash`, `is_admin`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
(2, 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- --------------------------------------------------------

-- Table structure for table `currencies`
CREATE TABLE `currencies` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `currency_code` varchar(5) NOT NULL COMMENT 'e.g., USD, EUR',
  `currency_name` varchar(50) NOT NULL COMMENT 'e.g., US Dollar',
  `symbol` varchar(5) DEFAULT NULL COMMENT 'e.g., $, €',
  PRIMARY KEY (`currency_id`),
  UNIQUE KEY `currency_code` (`currency_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample currencies
INSERT INTO `currencies` (`currency_id`, `currency_code`, `currency_name`, `symbol`) VALUES
(1, 'USD', 'US Dollar', '$'),
(2, 'EUR', 'Euro', '€'),
(3, 'GBP', 'British Pound', '£'),
(4, 'JPY', 'Japanese Yen', '¥'),
(5, 'CAD', 'Canadian Dollar', 'C$'),
(6, 'AUD', 'Australian Dollar', 'A$');

-- --------------------------------------------------------

-- Table structure for table `account_holders`
CREATE TABLE `account_holders` (
  `holder_id` int(11) NOT NULL AUTO_INCREMENT,
  `holder_name` varchar(100) NOT NULL,
  `holder_type` enum('personal','business','joint') DEFAULT 'personal',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`holder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample account holders
INSERT INTO `account_holders` (`holder_id`, `holder_name`, `holder_type`) VALUES
(1, 'Personal Account', 'personal'),
(2, 'Joint Family Account', 'joint'),
(3, 'Business Operations', 'business'),
(4, 'Freelance Work', 'business');

-- --------------------------------------------------------

-- Table structure for table `accounts`
CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(100) NOT NULL,
  `holder_id` int(11) NOT NULL,
  `account_type` enum('checking','savings','credit_card','cash','investment') DEFAULT 'checking',
  `account_details` text DEFAULT NULL,
  `starting_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `creation_date` date NOT NULL,
  `currency_id` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`account_id`),
  KEY `holder_id` (`holder_id`),
  KEY `currency_id` (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample accounts
INSERT INTO `accounts` (`account_id`, `account_name`, `holder_id`, `account_type`, `account_details`, `starting_balance`, `creation_date`, `currency_id`, `is_active`) VALUES
(1, 'Main Checking', 1, 'checking', 'Primary checking account for daily expenses', 5000.00, '2025-01-01', 1, 1),
(2, 'High-Yield Savings', 1, 'savings', 'Emergency fund and savings', 25000.00, '2025-01-01', 1, 1),
(3, 'Business Credit Card', 3, 'credit_card', 'Business expenses and travel', -2500.00, '2025-01-01', 1, 1),
(4, 'Cash Wallet', 1, 'cash', 'Physical cash on hand', 500.00, '2025-01-01', 1, 1),
(5, 'Investment Account', 2, 'investment', 'Stocks and mutual funds', 50000.00, '2025-01-01', 1, 1),
(6, 'Joint Checking', 2, 'checking', 'Shared household expenses', 8000.00, '2025-01-01', 1, 1);

-- --------------------------------------------------------

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_type` enum('credit','debit') NOT NULL COMMENT 'Credit = Income, Debit = Expense',
  `parent_category` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample categories with icons and colors
INSERT INTO `categories` (`category_id`, `category_name`, `category_type`, `parent_category`, `icon`, `color`) VALUES
-- Income Categories
(1, 'Salary', 'credit', NULL, 'fa-money-check', '#4CAF50'),
(2, 'Freelance Income', 'credit', NULL, 'fa-laptop', '#8BC34A'),
(3, 'Investment Income', 'credit', NULL, 'fa-chart-line', '#CDDC39'),
(4, 'Interest Income', 'credit', NULL, 'fa-percentage', '#FFC107'),
(5, 'Other Income', 'credit', NULL, 'fa-dollar-sign', '#FFD54F'),

-- Expense Categories
(6, 'Housing', 'debit', NULL, 'fa-home', '#F44336'),
(7, 'Rent/Mortgage', 'debit', 6, 'fa-key', '#E91E63'),
(8, 'Utilities', 'debit', 6, 'fa-bolt', '#9C27B0'),
(9, 'Maintenance', 'debit', 6, 'fa-tools', '#673AB7'),

(10, 'Transportation', 'debit', NULL, 'fa-car', '#3F51B5'),
(11, 'Gas/Fuel', 'debit', 10, 'fa-gas-pump', '#2196F3'),
(12, 'Public Transit', 'debit', 10, 'fa-bus', '#03A9F4'),
(13, 'Car Maintenance', 'debit', 10, 'fa-wrench', '#00BCD4'),

(14, 'Food & Dining', 'debit', NULL, 'fa-utensils', '#009688'),
(15, 'Groceries', 'debit', 14, 'fa-shopping-basket', '#4CAF50'),
(16, 'Restaurants', 'debit', 14, 'fa-pizza-slice', '#8BC34A'),
(17, 'Coffee & Snacks', 'debit', 14, 'fa-coffee', '#CDDC39'),

(18, 'Shopping', 'debit', NULL, 'fa-shopping-bag', '#FF9800'),
(19, 'Clothing', 'debit', 18, 'fa-tshirt', '#FF5722'),
(20, 'Electronics', 'debit', 18, 'fa-tv', '#795548'),

(21, 'Healthcare', 'debit', NULL, 'fa-heartbeat', '#607D8B'),
(22, 'Entertainment', 'debit', NULL, 'fa-film', '#9E9E9E'),
(23, 'Education', 'debit', NULL, 'fa-graduation-cap', '#455A64'),
(24, 'Savings', 'debit', NULL, 'fa-piggy-bank', '#37474F'),
(25, 'Transfer', 'debit', NULL, 'fa-exchange-alt', '#78909C');

-- --------------------------------------------------------

-- Table structure for table `transactions`
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_account_id` int(11) DEFAULT NULL COMMENT 'NULL = External Income',
  `dest_account_id` int(11) DEFAULT NULL COMMENT 'NULL = External Expense',
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Entry created by',
  `transaction_date` datetime NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `details` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `is_reconciled` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`),
  KEY `source_account_id` (`source_account_id`),
  KEY `dest_account_id` (`dest_account_id`),
  KEY `transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample transactions for the current month
INSERT INTO `transactions` (`source_account_id`, `dest_account_id`, `category_id`, `user_id`, `transaction_date`, `amount`, `details`, `is_reconciled`) VALUES
-- Income
(NULL, 1, 1, 1, '2025-10-01 09:00:00', 5500.00, 'October Salary', 1),
(NULL, 1, 2, 1, '2025-10-05 14:00:00', 1200.00, 'Website Development Project', 1),
(NULL, 2, 4, 1, '2025-10-10 10:00:00', 125.50, 'Quarterly Interest', 1),
(NULL, 5, 3, 1, '2025-10-15 11:00:00', 850.00, 'Dividend Payment', 0),

-- Expenses
(1, NULL, 7, 1, '2025-10-01 08:00:00', 1800.00, 'October Rent', 1),
(1, NULL, 8, 1, '2025-10-03 10:30:00', 245.75, 'Electric & Water Bill', 1),
(1, NULL, 15, 1, '2025-10-02 17:30:00', 156.43, 'Weekly Groceries - Walmart', 1),
(1, NULL, 16, 1, '2025-10-04 19:00:00', 85.50, 'Dinner at Italian Restaurant', 0),
(1, NULL, 11, 1, '2025-10-06 14:15:00', 65.00, 'Gas Station - Full Tank', 1),
(3, NULL, 20, 1, '2025-10-08 11:00:00', 999.99, 'New Laptop for Business', 0),
(1, NULL, 17, 1, '2025-10-09 08:30:00', 4.50, 'Morning Coffee', 0),
(1, NULL, 15, 1, '2025-10-10 18:00:00', 234.20, 'Weekly Groceries - Target', 1),
(6, NULL, 19, 1, '2025-10-12 14:00:00', 150.00, 'New Clothes Shopping', 0),
(1, NULL, 21, 1, '2025-10-13 10:00:00', 75.00, 'Doctor Visit Copay', 1),
(1, NULL, 22, 1, '2025-10-14 20:00:00', 50.00, 'Movie Tickets', 0),
(4, NULL, 16, 1, '2025-10-15 13:00:00', 25.00, 'Lunch with Friends', 0),
(1, NULL, 13, 1, '2025-10-16 09:00:00', 450.00, 'Car Service & Oil Change', 1),
(1, NULL, 17, 1, '2025-10-17 15:30:00', 6.75, 'Afternoon Snack', 0),
(1, NULL, 15, 1, '2025-10-18 17:00:00', 189.50, 'Weekly Groceries - Costco', 1),

-- Transfers
(1, 2, 24, 1, '2025-10-03 10:00:00', 1000.00, 'Monthly Savings Transfer', 1),
(1, 5, 25, 1, '2025-10-07 11:00:00', 500.00, 'Investment Account Deposit', 1),
(6, 1, 25, 1, '2025-10-11 14:00:00', 300.00, 'Transfer from Joint Account', 1),
(1, 4, 25, 1, '2025-10-20 16:00:00', 200.00, 'ATM Cash Withdrawal', 0);

-- Add Foreign Key Constraints
ALTER TABLE `accounts`
  ADD CONSTRAINT `fk_account_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`currency_id`),
  ADD CONSTRAINT `fk_account_holder` FOREIGN KEY (`holder_id`) REFERENCES `account_holders` (`holder_id`);

ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `fk_tx_dest_account` FOREIGN KEY (`dest_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tx_source_account` FOREIGN KEY (`source_account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

COMMIT;
