-- DSA Lead Management System - Database Schema
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS `dsa_lead_mgmt` 
    DEFAULT CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE `dsa_lead_mgmt`;

-- =============================================
-- USERS TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'manager', 'agent') DEFAULT 'agent',
    `phone` VARCHAR(20) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =============================================
-- LEADS TABLE (Core)
-- =============================================
CREATE TABLE IF NOT EXISTS `leads` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_source` VARCHAR(100) DEFAULT 'Other',
    `customer_name` VARCHAR(255) NOT NULL,
    `phone_number` VARCHAR(20) DEFAULT NULL,
    `alt_phone` VARCHAR(20) DEFAULT NULL,
    `email_address` VARCHAR(255) DEFAULT NULL,
    `dob` DATE DEFAULT NULL,
    `gender` ENUM('Male','Female','Other') DEFAULT NULL,
    
    -- Address
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `pincode` VARCHAR(10) DEFAULT NULL,
    
    -- Financial
    `loan_type` VARCHAR(100) DEFAULT NULL,
    `loan_amount` DECIMAL(15,2) DEFAULT 0.00,
    `monthly_income` DECIMAL(15,2) DEFAULT 0.00,
    `employer` VARCHAR(255) DEFAULT NULL,
    `employment_type` ENUM('Salaried','Self-Employed','Business','Retired','Other') DEFAULT NULL,
    
    -- Banking
    `existing_loans` TEXT DEFAULT NULL,
    `credit_score` INT DEFAULT NULL,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    
    -- Lead tracking
    `status` ENUM('New','Contacted','Documentation','Submitted','Approved','Disbursed','Rejected') DEFAULT 'New',
    `lead_score` INT DEFAULT 0,
    `lead_grade` ENUM('Hot','Warm','Cold') DEFAULT 'Cold',
    `assigned_to` INT DEFAULT NULL,
    `remarks` TEXT DEFAULT NULL,
    `follow_up_date` DATE DEFAULT NULL,
    
    -- Import tracking
    `import_batch_id` INT DEFAULT NULL,
    
    -- Timestamps
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_status` (`status`),
    INDEX `idx_lead_grade` (`lead_grade`),
    INDEX `idx_assigned_to` (`assigned_to`),
    INDEX `idx_phone` (`phone_number`),
    INDEX `idx_city` (`city`),
    INDEX `idx_loan_type` (`loan_type`),
    INDEX `idx_follow_up` (`follow_up_date`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- ACTIVITY LOG TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lead_id` INT NOT NULL,
    `user_id` INT DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `old_value` TEXT DEFAULT NULL,
    `new_value` TEXT DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_lead_id` (`lead_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- IMPORT BATCHES TABLE
-- =============================================
CREATE TABLE IF NOT EXISTS `import_batches` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `total_rows` INT DEFAULT 0,
    `imported_rows` INT DEFAULT 0,
    `skipped_rows` INT DEFAULT 0,
    `error_rows` INT DEFAULT 0,
    `column_mapping` JSON DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `status` ENUM('processing','completed','failed') DEFAULT 'processing',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =============================================
-- DEFAULT ADMIN USER (password: admin123)
-- =============================================
INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES
('Admin', 'admin@dsa.com', '$2y$12$LJ3m4yPndR8YwTHlYhVZxOQvRNGvG9r9VpJgq0BbQzK8kZ5qhWOHi', 'admin'),
('Manager Demo', 'manager@dsa.com', '$2y$12$LJ3m4yPndR8YwTHlYhVZxOQvRNGvG9r9VpJgq0BbQzK8kZ5qhWOHi', 'manager'),
('Agent Demo', 'agent@dsa.com', '$2y$12$LJ3m4yPndR8YwTHlYhVZxOQvRNGvG9r9VpJgq0BbQzK8kZ5qhWOHi', 'agent');

-- =============================================
-- SAMPLE LEADS FOR DEMO
-- =============================================
INSERT INTO `leads` (`lead_source`, `customer_name`, `phone_number`, `email_address`, `city`, `state`, `loan_type`, `loan_amount`, `monthly_income`, `employer`, `employment_type`, `status`, `lead_score`, `lead_grade`, `assigned_to`) VALUES
('Referral', 'Rajesh Kumar', '9876543210', 'rajesh.k@email.com', 'Mumbai', 'Maharashtra', 'Home Loan', 5000000.00, 120000.00, 'TCS', 'Salaried', 'Documentation', 85, 'Hot', 1),
('Website', 'Priya Sharma', '9812345678', 'priya.s@email.com', 'Delhi', 'Delhi', 'Personal Loan', 300000.00, 65000.00, 'Infosys', 'Salaried', 'Contacted', 60, 'Warm', 2),
('Walk-in', 'Amit Patel', '9798765432', 'amit.p@email.com', 'Ahmedabad', 'Gujarat', 'Business Loan', 2000000.00, 200000.00, 'Self', 'Self-Employed', 'Submitted', 75, 'Hot', 1),
('Phone Inquiry', 'Sneha Reddy', '9654321098', NULL, 'Hyderabad', 'Telangana', 'Gold Loan', 500000.00, 45000.00, NULL, NULL, 'New', 35, 'Cold', NULL),
('Social Media', 'Vikram Singh', '9543210987', 'vikram@email.com', 'Jaipur', 'Rajasthan', 'Vehicle Loan', 800000.00, 55000.00, 'Wipro', 'Salaried', 'Approved', 70, 'Hot', 2),
('Campaign', 'Anjali Nair', '9432109876', NULL, 'Kochi', 'Kerala', 'Education Loan', 1500000.00, 0.00, NULL, NULL, 'New', 25, 'Cold', NULL),
('Partner', 'Mohammed Ali', '9321098765', 'mali@email.com', 'Chennai', 'Tamil Nadu', 'Loan Against Property', 8000000.00, 180000.00, 'HCL', 'Salaried', 'Disbursed', 90, 'Hot', 1),
('Referral', 'Deepa Gupta', '9210987654', 'deepa.g@email.com', 'Pune', 'Maharashtra', 'Personal Loan', 200000.00, 35000.00, 'Freelance', 'Self-Employed', 'Rejected', 45, 'Warm', 3);
