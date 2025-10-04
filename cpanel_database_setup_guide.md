# cPanel Database Setup - Step by Step Guide

## The Error Explained
Error `#1044 - Access denied for user 'a1e750tdxgba'@'localhost' to database 'a1e750tdxgba_cursor1'` means the database user doesn't have permission to access the database.

## Solution: Create Database and User in cPanel First

### Step 1: Create Database in cPanel
1. **Login to cPanel**
2. **Go to "MySQL Databases"** (in the Databases section)
3. **Create New Database:**
   - Database Name: `cursor1` (cPanel will add the prefix automatically)
   - Click "Create Database"
   - Note: cPanel will show the full name as `a1e750tdxgba_cursor1`

### Step 2: Create Database User
1. **In the same MySQL Databases section**
2. **Scroll down to "Add New User"**
3. **Create User:**
   - Username: `15Crossways` (cPanel will add the prefix automatically)
   - Password: `Crossways15!`
   - Click "Create User"
   - Note: cPanel will show the full username as `a1e750tdxgba_15Crossways`

### Step 3: Assign User to Database
1. **Scroll down to "Add User To Database"**
2. **Select:**
   - User: `a1e750tdxgba_15Crossways`
   - Database: `a1e750tdxgba_cursor1`
3. **Click "Add"**
4. **Set Privileges:**
   - Check "ALL PRIVILEGES"
   - Click "Make Changes"

### Step 4: Import Database Structure
1. **Go to phpMyAdmin** (in cPanel)
2. **Select the `a1e750tdxgba_cursor1` database**
3. **Click "Import" tab**
4. **Upload the `setup_database_cpanel.sql` file**
5. **Click "Go"**

## Alternative: Manual SQL Commands
If you prefer to run SQL commands manually, use these in phpMyAdmin:

```sql
-- First, make sure you're using the correct database
USE a1e750tdxgba_cursor1;

-- Create the formresponse table
CREATE TABLE IF NOT EXISTS formresponse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the login table
CREATE TABLE IF NOT EXISTS login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_email ON formresponse(email);
CREATE INDEX IF NOT EXISTS idx_created_at ON formresponse(created_at);
CREATE INDEX IF NOT EXISTS idx_username ON login(username);

-- Insert sample data
INSERT INTO formresponse (name, email, telephone, message) VALUES
('John Doe', 'john@example.com', '+1-555-0123', 'This is a sample message from John.'),
('Jane Smith', 'jane@example.com', '+1-555-0456', 'Another sample message from Jane.'),
('Bob Johnson', 'bob@example.com', '+1-555-0789', 'Sample message from Bob for testing purposes.');

-- Insert admin user (password: admin123)
INSERT INTO login (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
```

## Verification Steps
1. **Check Database Connection:**
   - Visit: `https://yourdomain.com/projects/cursor1/test-env.php`
   - Should show "Connected Successfully"

2. **Test Login:**
   - Go to: `https://yourdomain.com/projects/cursor1/login`
   - Username: `admin`
   - Password: `admin123`

3. **Test Contact Form:**
   - Go to: `https://yourdomain.com/projects/cursor1/contact`
   - Submit a test message
   - Check if it appears in admin dashboard

## Common Issues and Solutions

### Issue 1: "Access denied" error
**Solution:** Make sure you created the database and user in cPanel first, then assigned the user to the database with ALL PRIVILEGES.

### Issue 2: "Database doesn't exist" error
**Solution:** The database name in cPanel might be different. Check the exact database name in cPanel's MySQL Databases section.

### Issue 3: "User doesn't exist" error
**Solution:** Verify the username in cPanel's MySQL Databases section. cPanel adds a prefix to usernames.

## Important Notes
- cPanel automatically adds prefixes to database names and usernames
- Always create the database and user through cPanel interface first
- The user must be assigned to the database with proper privileges
- Test the connection using the test-env.php file before going live
