-- Add Location Fields to Cars Table
-- Run this SQL in phpMyAdmin or MySQL command line

USE autohavencars;

-- Add location columns to cars table
ALTER TABLE cars 
ADD COLUMN IF NOT EXISTS city VARCHAR(100) AFTER transmission,
ADD COLUMN IF NOT EXISTS state VARCHAR(50) AFTER city,
ADD COLUMN IF NOT EXISTS zip_code VARCHAR(20) AFTER state,
ADD COLUMN IF NOT EXISTS address VARCHAR(255) AFTER zip_code,
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) AFTER address,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) AFTER latitude;

-- Add index for location searches
ALTER TABLE cars 
ADD INDEX IF NOT EXISTS idx_location (city, state);

-- For MySQL versions that don't support IF NOT EXISTS, use this instead:
-- ALTER TABLE cars 
-- ADD COLUMN city VARCHAR(100) AFTER transmission,
-- ADD COLUMN state VARCHAR(50) AFTER city,
-- ADD COLUMN zip_code VARCHAR(20) AFTER state,
-- ADD COLUMN address VARCHAR(255) AFTER zip_code,
-- ADD COLUMN latitude DECIMAL(10, 8) AFTER address,
-- ADD COLUMN longitude DECIMAL(11, 8) AFTER latitude;
-- 
-- ALTER TABLE cars ADD INDEX idx_location (city, state);







