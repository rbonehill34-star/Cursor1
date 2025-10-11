-- Jobs Database Setup for Cursor1
-- Run this in phpMyAdmin to add jobs functionality

-- Create the state table for job states
CREATE TABLE IF NOT EXISTS state (
    id INT AUTO_INCREMENT PRIMARY KEY,
    state_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the jobs table
CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    client_reference VARCHAR(50),
    task_id INT NOT NULL,
    description VARCHAR(100),
    budget_hours DECIMAL(10,2),
    state_id INT NOT NULL,
    urgent BOOLEAN DEFAULT FALSE,
    partner_id INT,
    manager_id INT,
    preparer_id INT,
    deadline_date DATE,
    expected_completion_date DATE,
    received_date DATE,
    assigned_date DATE,
    completed_date DATE,
    reviewed_date DATE,
    sent_to_client_date DATE,
    approved_date DATE,
    submitted_date DATE,
    archived_date DATE,
    comments VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (state_id) REFERENCES state(id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES login(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_id) REFERENCES login(id) ON DELETE SET NULL,
    FOREIGN KEY (preparer_id) REFERENCES login(id) ON DELETE SET NULL
);

-- Insert job states
INSERT IGNORE INTO state (state_name) VALUES
('Outstanding'),
('Received'),
('Prepare'),
('Returned'),
('Review'),
('With Client'),
('Paid not approved'),
('Approved not paid'),
('Submit'),
('Completed'),
('Other'),
('Archived');

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_jobs_client ON jobs(client_id);
CREATE INDEX IF NOT EXISTS idx_jobs_state ON jobs(state_id);
CREATE INDEX IF NOT EXISTS idx_jobs_urgent ON jobs(urgent);
CREATE INDEX IF NOT EXISTS idx_jobs_deadline ON jobs(deadline_date);
CREATE INDEX IF NOT EXISTS idx_jobs_created ON jobs(created_at);
CREATE INDEX IF NOT EXISTS idx_state_name ON state(state_name);

-- Show completion message
SELECT 'Jobs database setup completed successfully!' as Status;




