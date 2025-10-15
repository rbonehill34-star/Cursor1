-- SQL query to create email_templates table and insert default templates for live database
-- Run this on your live cPanel database

-- Create email_templates table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(100) NOT NULL UNIQUE,
    subject TEXT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default email templates
INSERT INTO email_templates (task_name, subject, body) VALUES
('Year End', 'Information needed for Accounts for the Period Ended {period_end}', 'Hi {contact_forename}\n\nPlease can you send the data for the accounts as soon as possible.\n\nKind regards\nRob'),
('VAT returns', 'Information needed for VAT Return for the Period Ended {period_end}', 'Hi {contact_forename}\n\nPlease can you send the data for the VAT return as soon as possible.\n\nKind regards\nRob'),
('Other default', 'Information needed for {task_name} for the Period Ended {period_end}', 'Hi {contact_forename}\n\nPlease can you send the data for the {task_name} as soon as possible.\n\nKind regards\nRob')
ON DUPLICATE KEY UPDATE
    subject = VALUES(subject),
    body = VALUES(body),
    updated_at = CURRENT_TIMESTAMP;
