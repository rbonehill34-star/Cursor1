-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS cursor1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE cursor1;

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add some indexes for better performance (only if they don't exist)
CREATE INDEX IF NOT EXISTS idx_email ON formresponse(email);
CREATE INDEX IF NOT EXISTS idx_created_at ON formresponse(created_at);
CREATE INDEX IF NOT EXISTS idx_username ON users(username);

-- Insert some sample data (optional)
INSERT INTO formresponse (name, email, telephone, message) VALUES
('John Doe', 'john@example.com', '+1-555-0123', 'This is a sample message from John.'),
('Jane Smith', 'jane@example.com', '+1-555-0456', 'Another sample message from Jane.'),
('Bob Johnson', 'bob@example.com', '+1-555-0789', 'Sample message from Bob for testing purposes.');

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
