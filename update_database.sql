-- Database Update Script for Cursor1
-- Run this in phpMyAdmin to add new tables and features

USE a1e750tdxgba_cursor1;

-- Add account_type column to existing users table
ALTER TABLE users ADD COLUMN account_type ENUM('Administrator', 'Manager', 'Basic') NOT NULL DEFAULT 'Basic' AFTER password;

-- Create the clients table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    contact VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    year_end DATE,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the tasks table
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create the timesheet table
CREATE TABLE IF NOT EXISTS timesheet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    client_id INT NOT NULL,
    task_id INT NOT NULL,
    time_spent TIME NOT NULL,
    description VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_account_type ON users(account_type);
CREATE INDEX IF NOT EXISTS idx_client_reference ON clients(reference);
CREATE INDEX IF NOT EXISTS idx_timesheet_user_date ON timesheet(user_id, date);
CREATE INDEX IF NOT EXISTS idx_timesheet_client ON timesheet(client_id);
CREATE INDEX IF NOT EXISTS idx_timesheet_task ON timesheet(task_id);

-- Update existing admin user to Administrator
UPDATE users SET account_type = 'Administrator' WHERE username = 'admin';

-- Insert sample users if they don't exist
INSERT IGNORE INTO users (username, password, account_type) VALUES
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager'),
('basic', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Basic');

-- Insert sample clients
INSERT IGNORE INTO clients (reference, name, contact, email, phone, year_end) VALUES
('CLI001', 'ABC Company Ltd', 'John Smith', 'john@abccompany.com', '+44 1234 567890', '2024-03-31'),
('CLI002', 'XYZ Corporation', 'Jane Doe', 'jane@xyzcorp.com', '+44 9876 543210', '2024-12-31'),
('CLI003', 'Tech Solutions Inc', 'Bob Johnson', 'bob@techsolutions.com', '+44 5555 123456', '2024-06-30');

-- Insert sample tasks
INSERT IGNORE INTO tasks (task_name, description) VALUES
('Bookkeeping', 'General bookkeeping and record keeping'),
('VAT Returns', 'Preparation and submission of VAT returns'),
('Payroll', 'Monthly payroll processing'),
('Tax Returns', 'Annual tax return preparation'),
('Audit', 'Financial audit and review'),
('Consultation', 'General business consultation');

-- Show completion message
SELECT 'Database update completed successfully!' as Status;
