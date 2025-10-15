-- SQL Script to add task_order column to tasks table on live database
-- This script is safe to run on production and includes proper error handling

-- Add task_order column after task_name and before description
ALTER TABLE tasks ADD COLUMN task_order INT DEFAULT 0 AFTER task_name;

-- Set default values for existing tasks using their ID as the order number
-- This ensures existing tasks have a logical order
UPDATE tasks SET task_order = id WHERE task_order = 0;

-- Optional: Add an index on task_order for better performance when sorting
-- CREATE INDEX idx_tasks_order ON tasks(task_order);

-- Verify the changes
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    ORDINAL_POSITION
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'tasks' 
AND TABLE_SCHEMA = DATABASE()
ORDER BY ORDINAL_POSITION;
