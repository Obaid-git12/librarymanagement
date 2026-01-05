-- Update books table to have weekly and monthly pricing
USE library_db;

-- Drop old borrow_price column if exists
ALTER TABLE books DROP COLUMN IF EXISTS borrow_price;

-- Add weekly and monthly price columns
ALTER TABLE books ADD COLUMN IF NOT EXISTS weekly_price DECIMAL(10,2) DEFAULT 5.00 AFTER quantity;
ALTER TABLE books ADD COLUMN IF NOT EXISTS monthly_price DECIMAL(10,2) DEFAULT 15.00 AFTER weekly_price;

-- Update existing books with default prices
UPDATE books SET weekly_price = 5.00 WHERE weekly_price IS NULL;
UPDATE books SET monthly_price = 15.00 WHERE monthly_price IS NULL;

-- Add borrow_duration column to borrow_requests table
ALTER TABLE borrow_requests ADD COLUMN IF NOT EXISTS borrow_duration ENUM('weekly', 'monthly') DEFAULT 'weekly' AFTER book_id;

-- Set sample prices for existing books
UPDATE books SET weekly_price = 3.00, monthly_price = 10.00 WHERE book_name = 'The Great Gatsby';
UPDATE books SET weekly_price = 3.50, monthly_price = 12.00 WHERE book_name = 'To Kill a Mockingbird';
UPDATE books SET weekly_price = 4.00, monthly_price = 14.00 WHERE book_name = '1984';
UPDATE books SET weekly_price = 2.50, monthly_price = 8.00 WHERE book_name = 'Pride and Prejudice';
UPDATE books SET weekly_price = 3.00, monthly_price = 10.00 WHERE book_name = 'The Catcher in the Rye';

-- Verify the changes
SELECT id, book_name, author, quantity, weekly_price, monthly_price FROM books;
