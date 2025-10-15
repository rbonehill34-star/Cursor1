-- SQL Script to add 4 new fields to users table
-- This script adds: user_forename, user_surname, user_internal, user_email
-- Fields are added after account_type column

-- Add user_forename column after account_type
ALTER TABLE users ADD COLUMN user_forename VARCHAR(100) DEFAULT NULL AFTER account_type;

-- Add user_surname column after user_forename
ALTER TABLE users ADD COLUMN user_surname VARCHAR(100) DEFAULT NULL AFTER user_forename;

-- Add user_internal column after user_surname
ALTER TABLE users ADD COLUMN user_internal VARCHAR(100) DEFAULT NULL AFTER user_surname;

-- Add user_signature column after user_internal (used for email signature)
ALTER TABLE users ADD COLUMN user_signature VARCHAR(255) DEFAULT NULL AFTER user_internal;

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
