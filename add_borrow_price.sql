-- Add borrow_price column to books table
USE library_db;

-- Add the column
ALTER TABLE books ADD COLUMN borrow_price DECIMAL(10,2) DEFAULT 5.00 AFTER quantity;

-- Update existing books with default price
UPDATE books SET borrow_price = 5.00 WHERE borrow_price IS NULL;

-- Optional: Set custom prices for specific books
UPDATE books SET borrow_price = 3.00 WHERE book_name = 'The Great Gatsby';
UPDATE books SET borrow_price = 3.50 WHERE book_name = 'To Kill a Mockingbird';
UPDATE books SET borrow_price = 4.00 WHERE book_name = '1984';
UPDATE books SET borrow_price = 2.50 WHERE book_name = 'Pride and Prejudice';
UPDATE books SET borrow_price = 3.00 WHERE book_name = 'The Catcher in the Rye';

-- Verify the changes
SELECT id, book_name, author, quantity, borrow_price FROM books;
