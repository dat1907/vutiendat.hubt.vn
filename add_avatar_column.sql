-- Add avatar column to users table
USE webcban_db;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) AFTER address;

-- Verify the column was added
DESCRIBE users;
