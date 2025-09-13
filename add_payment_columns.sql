-- Add payment columns to orders table
USE webcban_db;

-- Add payment_method column
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(20) DEFAULT 'cod';

-- Add vnpay_transaction_id column  
ALTER TABLE orders ADD COLUMN vnpay_transaction_id VARCHAR(100);

-- Add payment_date column
ALTER TABLE orders ADD COLUMN payment_date TIMESTAMP NULL;

-- Verify columns were added
DESCRIBE orders;
