-- Add More Car Specifications
-- Run this SQL in phpMyAdmin or MySQL command line

USE autohavencars;

-- Add additional specification columns to cars table
ALTER TABLE cars 
ADD COLUMN IF NOT EXISTS engine_size VARCHAR(50) AFTER transmission,
ADD COLUMN IF NOT EXISTS engine_type VARCHAR(50) AFTER engine_size,
ADD COLUMN IF NOT EXISTS doors INT AFTER engine_type,
ADD COLUMN IF NOT EXISTS seats INT AFTER doors,
ADD COLUMN IF NOT EXISTS drive_type ENUM('FWD', 'RWD', 'AWD', '4WD') AFTER seats,
ADD COLUMN IF NOT EXISTS vin_number VARCHAR(17) AFTER drive_type,
ADD COLUMN IF NOT EXISTS condition_status ENUM('excellent', 'good', 'fair', 'poor') AFTER vin_number,
ADD COLUMN IF NOT EXISTS previous_owners INT DEFAULT 1 AFTER condition_status,
ADD COLUMN IF NOT EXISTS accident_history ENUM('none', 'minor', 'moderate', 'major') DEFAULT 'none' AFTER previous_owners,
ADD COLUMN IF NOT EXISTS service_history BOOLEAN DEFAULT 0 AFTER accident_history,
ADD COLUMN IF NOT EXISTS features TEXT AFTER service_history;

-- For MySQL versions that don't support IF NOT EXISTS:
-- ALTER TABLE cars 
-- ADD COLUMN engine_size VARCHAR(50) AFTER transmission,
-- ADD COLUMN engine_type VARCHAR(50) AFTER engine_size,
-- ADD COLUMN doors INT AFTER engine_type,
-- ADD COLUMN seats INT AFTER doors,
-- ADD COLUMN drive_type ENUM('FWD', 'RWD', 'AWD', '4WD') AFTER seats,
-- ADD COLUMN vin_number VARCHAR(17) AFTER drive_type,
-- ADD COLUMN condition_status ENUM('excellent', 'good', 'fair', 'poor') AFTER vin_number,
-- ADD COLUMN previous_owners INT DEFAULT 1 AFTER condition_status,
-- ADD COLUMN accident_history ENUM('none', 'minor', 'moderate', 'major') DEFAULT 'none' AFTER previous_owners,
-- ADD COLUMN service_history BOOLEAN DEFAULT 0 AFTER accident_history,
-- ADD COLUMN features TEXT AFTER service_history;





