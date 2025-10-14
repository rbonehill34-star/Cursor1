-- Migration script to update clients table contact fields on LIVE database
-- This script renames 'contact' to 'contact_forename' and adds 'contact_surname'

-- Step 1: Add the new contact_surname column
ALTER TABLE clients ADD COLUMN contact_surname VARCHAR(255) NULL AFTER contact;

-- Step 2: Rename the existing contact column to contact_forename
ALTER TABLE clients CHANGE COLUMN contact contact_forename VARCHAR(255) NULL;

-- Step 3: Update the column order to place contact_surname between contact_forename and email
-- (This is optional but matches the user's requirement)
ALTER TABLE clients MODIFY COLUMN contact_surname VARCHAR(255) NULL AFTER contact_forename;

-- Verify the changes
DESCRIBE clients;
