-- Database setup script for cPanel hosting
-- Run this in cPanel phpMyAdmin to create the database and tables

-- Create database (if it doesn't exist)
-- Note: In cPanel, you may need to create the database through the cPanel interface first
-- Database name: a1e750tdxgba_cursor1

-- Use the database
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

-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    account_type ENUM('Administrator', 'Manager', 'Basic') NOT NULL DEFAULT 'Basic',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
CREATE INDEX IF NOT EXISTS idx_email ON formresponse(email);
CREATE INDEX IF NOT EXISTS idx_created_at ON formresponse(created_at);
CREATE INDEX IF NOT EXISTS idx_username ON users(username);

-- Insert sample form data (optional)
INSERT INTO formresponse (name, email, telephone, message) VALUES
('John Doe', 'john@example.com', '+1-555-0123', 'This is a sample message from John.'),
('Jane Smith', 'jane@example.com', '+1-555-0456', 'Another sample message from Jane.'),
('Bob Johnson', 'bob@example.com', '+1-555-0789', 'Sample message from Bob for testing purposes.');

-- Insert sample users (password: admin123)
INSERT INTO users (username, password, account_type) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Manager'),
('basic', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Basic');

-- Insert sample clients
INSERT INTO clients (reference, name, contact, email, phone, year_end) VALUES
('CLI001', 'ABC Company Ltd', 'John Smith', 'john@abccompany.com', '+44 1234 567890', '2024-03-31'),
('CLI002', 'XYZ Corporation', 'Jane Doe', 'jane@xyzcorp.com', '+44 9876 543210', '2024-12-31'),
('CLI003', 'Tech Solutions Inc', 'Bob Johnson', 'bob@techsolutions.com', '+44 5555 123456', '2024-06-30');

-- Insert sample tasks
INSERT INTO tasks (task_name, description) VALUES
('Bookkeeping', 'General bookkeeping and record keeping'),
('VAT Returns', 'Preparation and submission of VAT returns'),
('Payroll', 'Monthly payroll processing'),
('Tax Returns', 'Annual tax return preparation'),
('Audit', 'Financial audit and review'),
('Consultation', 'General business consultation');
