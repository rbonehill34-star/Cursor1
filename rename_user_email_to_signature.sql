-- SQL Script to rename user_email column to user_signature
-- This script renames the column to better reflect its purpose as an email signature

-- Rename user_email column to user_signature
ALTER TABLE users CHANGE COLUMN user_email user_signature VARCHAR(255) DEFAULT NULL;

-- Verify the changes
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    ORDINAL_POSITION
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' 
AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;
