-- Migration script to update email_templates table to use template_name instead of task_name
-- This allows for better data integrity by linking to task IDs instead of task names

-- Drop existing table if it exists (for clean migration)
DROP TABLE IF EXISTS email_templates;

-- Create new email_templates table with template_name field
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL UNIQUE,
    subject TEXT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default email templates
-- These templates are mapped to specific task IDs in the application logic:
-- - Year End template (for task ID 1)
-- - VAT Returns template (for task ID 2) 
-- - Other default template (for all other task IDs)
INSERT INTO email_templates (template_name, subject, body) VALUES
('Year End', 'Information needed for Accounts for the Period Ended {period_end}', 'Hi {contact_forename}\n\nPlease can you send the data for the accounts as soon as possible.\n\nThe deadline for submission is {deadline_date}.\n\nKind regards\n{user_signature}\n{username}'),
('VAT Returns', 'Information needed for VAT Return for the Period Ended {period_end}', 'Hi {contact_forename}\n\nPlease can you send the data for the VAT return as soon as possible.\n\nThe deadline for submission is {deadline_date}.\n\nKind regards\n{user_signature}\n{username}'),
('Other default', 'Information needed for {task_name} for the Period Ended {period_end}', 'Hi {contact_forename}\n\nPlease can you send the data for the {task_name} as soon as possible.\n\nThe deadline for submission is {deadline_date}.\n\nKind regards\n{user_signature}\n{username}');
