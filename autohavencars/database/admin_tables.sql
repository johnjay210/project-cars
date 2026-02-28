-- Admin Dashboard Tables
-- Run this SQL in phpMyAdmin or MySQL command line

USE autohavencars;

-- Add role column to users table if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS role ENUM('user', 'admin') DEFAULT 'user' AFTER password;

-- Make the first admin user an admin (if exists)
UPDATE users SET role = 'admin' WHERE email = 'admin@autohavencars.com' LIMIT 1;

-- Analytics table for tracking page views and traffic
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(255) NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referrer VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_page (page),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
);

-- Sales tracking table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    seller_id INT NOT NULL,
    buyer_id INT NULL,
    sale_price DECIMAL(10, 2) NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sale_date (sale_date),
    INDEX idx_status (status)
);

-- For MySQL versions that don't support IF NOT EXISTS:
-- ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER password;
-- UPDATE users SET role = 'admin' WHERE email = 'admin@autohavencars.com' LIMIT 1;





