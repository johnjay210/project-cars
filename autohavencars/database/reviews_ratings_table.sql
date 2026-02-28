-- Reviews and Ratings Table
-- Run this SQL in phpMyAdmin or MySQL command line

USE autohavencars;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reviewer_id INT NOT NULL,
    seller_id INT NOT NULL,
    car_id INT, -- Optional: link review to specific car purchase
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
    UNIQUE KEY unique_review (reviewer_id, seller_id, car_id),
    INDEX idx_seller_id (seller_id),
    INDEX idx_rating (rating)
);

-- For MySQL versions that don't support CHECK constraint, use this instead:
-- CREATE TABLE IF NOT EXISTS reviews (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     reviewer_id INT NOT NULL,
--     seller_id INT NOT NULL,
--     car_id INT,
--     rating INT NOT NULL,
--     title VARCHAR(255),
--     comment TEXT,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--     FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
--     FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
--     FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL,
--     UNIQUE KEY unique_review (reviewer_id, seller_id, car_id),
--     INDEX idx_seller_id (seller_id),
--     INDEX idx_rating (rating)
-- );






