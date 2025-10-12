-- Migration: Rename login table to users
-- Run this script to rename the login table to users throughout the database
-- This script is safe to run on both local and production databases

-- Use the appropriate database
-- For local development: USE cursor1;
-- For cPanel: USE a1e750tdxgba_cursor1;

-- Step 1: Drop foreign key constraints that reference the login table
-- These will be recreated after the rename

-- Drop timesheet foreign keys
ALTER TABLE timesheet DROP FOREIGN KEY timesheet_ibfk_1;

-- Drop jobs foreign keys (if jobs table exists)
ALTER TABLE jobs DROP FOREIGN KEY IF EXISTS jobs_ibfk_4;
ALTER TABLE jobs DROP FOREIGN KEY IF EXISTS jobs_ibfk_5;
ALTER TABLE jobs DROP FOREIGN KEY IF EXISTS jobs_ibfk_6;

-- Step 2: Rename the login table to users
RENAME TABLE login TO users;

-- Step 3: Recreate foreign key constraints with the new table name

-- Recreate timesheet foreign key
ALTER TABLE timesheet 
    ADD CONSTRAINT timesheet_ibfk_1 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Recreate jobs foreign keys (if jobs table exists)
ALTER TABLE jobs 
    ADD CONSTRAINT jobs_ibfk_4 
    FOREIGN KEY (partner_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE jobs 
    ADD CONSTRAINT jobs_ibfk_5 
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE jobs 
    ADD CONSTRAINT jobs_ibfk_6 
    FOREIGN KEY (preparer_id) REFERENCES users(id) ON DELETE SET NULL;

-- Show completion message
SELECT 'Successfully renamed login table to users!' as Status;

