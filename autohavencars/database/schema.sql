-- AutoHavenCars Database Schema
-- Run this SQL in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS autohavencars;
USE autohavencars;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    mileage INT NOT NULL,
    color VARCHAR(30),
    fuel_type VARCHAR(20),
    transmission VARCHAR(20),
    description TEXT,
    image_path VARCHAR(255),
    status ENUM('available', 'sold', 'pending') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_make_model (make, model)
);

-- Insert sample data
INSERT INTO users (username, email, password, phone) VALUES
('admin', 'admin@autohavencars.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123-456-7890'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '555-1234');

-- Note: Password is 'password' (hashed with bcrypt)
-- You should change this in production!

INSERT INTO cars (user_id, make, model, year, price, mileage, color, fuel_type, transmission, description, status) VALUES
(1, 'Toyota', 'Camry', 2020, 25000.00, 15000, 'Silver', 'Gasoline', 'Automatic', 'Well maintained, single owner, all service records available.', 'available'),
(2, 'Honda', 'Civic', 2019, 22000.00, 20000, 'Blue', 'Gasoline', 'Manual', 'Great condition, sporty and fuel efficient.', 'available'),
(1, 'Ford', 'F-150', 2021, 35000.00, 10000, 'Black', 'Gasoline', 'Automatic', 'Powerful truck, perfect for work or recreation.', 'available'),
(2, 'Tesla', 'Model 3', 2022, 45000.00, 5000, 'White', 'Electric', 'Automatic', 'Latest model, excellent range, autopilot included.', 'available'),
(1, 'BMW', '3 Series', 2021, 38000.00, 12000, 'Black', 'Gasoline', 'Automatic', 'Luxury sedan with premium features and excellent performance.', 'available'),
(2, 'Mercedes-Benz', 'C-Class', 2020, 42000.00, 18000, 'Silver', 'Gasoline', 'Automatic', 'Elegant design, comfortable interior, well-maintained.', 'available');

