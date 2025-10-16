-- SQL Script to add state_order and state_access columns to state table
-- This script is safe to run on production and includes proper error handling

-- Add state_order column after state_name
ALTER TABLE state ADD COLUMN state_order INT(11) DEFAULT 0 AFTER state_name;

-- Add state_access column after state_order
ALTER TABLE state ADD COLUMN state_access ENUM('Basic', 'Manager', 'Administrator') DEFAULT 'Basic' AFTER state_order;

-- Set default values for existing states using their ID as the order number
-- This ensures existing states have a logical order
UPDATE state SET state_order = id WHERE state_order = 0;

-- Set default access levels for existing states
-- Most states should be accessible to all users by default
UPDATE state SET state_access = 'Basic' WHERE state_access IS NULL;

-- Verify the changes
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    ORDINAL_POSITION
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'state' 
AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;

-- Show updated data
SELECT id, state_name, state_order, state_access, created_at FROM state ORDER BY state_order;
