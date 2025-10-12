-- Add users table and indexes to existing database
-- Run this if you already have the formresponse table

USE cursor1;

-- Create the users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add index for users table (only if it doesn't exist)
CREATE INDEX IF NOT EXISTS idx_username ON users(username);

-- Insert sample admin user (password: admin123)
-- Only insert if no users exist yet
INSERT IGNORE INTO users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

