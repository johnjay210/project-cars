-- Add Location Fields to Cars Table

USE autohavencars;

-- AddING location columns to cars table
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









