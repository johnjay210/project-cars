-- Car Images Table for Multiple Photos
-- Run this SQL in phpMyAdmin or MySQL command line

USE autohavencars;

-- Car images table
CREATE TABLE IF NOT EXISTS car_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_type ENUM('exterior', 'interior', 'engine', 'other') DEFAULT 'exterior',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    INDEX idx_car_id (car_id),
    INDEX idx_display_order (display_order)
);





