-- Run this if you already have the database and need to add new columns
USE library_db;

-- Add status column to users table if it doesn't exist
ALTER TABLE users ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER role;

-- Add email and full_name columns if they don't exist
ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER status;
ALTER TABLE users ADD COLUMN full_name VARCHAR(100) AFTER email;

-- Update existing users to approved status
UPDATE users SET status = 'approved' WHERE role = 'manager' OR role = 'student';

-- Create borrow_requests table if not exists
CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
    borrow_date DATE,
    return_date DATE,
    actual_return_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Create charges table if not exists
CREATE TABLE IF NOT EXISTS charges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    charge_type ENUM('weekly', 'monthly', 'late_fee') NOT NULL,
    description VARCHAR(255),
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id)
);

SELECT 'Database updated successfully!' as message;
