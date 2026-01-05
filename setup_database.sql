-- Create database
CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- Drop old users table if exists
DROP TABLE IF EXISTS charges;
DROP TABLE IF EXISTS borrow_requests;
DROP TABLE IF EXISTS users;

-- Create managers table
CREATE TABLE IF NOT EXISTS managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_name VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    year INT NOT NULL,
    quantity INT NOT NULL,
    weekly_price DECIMAL(10,2) DEFAULT 5.00,
    monthly_price DECIMAL(10,2) DEFAULT 15.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create borrow_requests table
CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
    borrow_date DATE,
    return_date DATE,
    actual_return_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Create charges table
CREATE TABLE IF NOT EXISTS charges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    charge_type ENUM('weekly', 'monthly', 'late_fee') NOT NULL,
    description VARCHAR(255),
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Insert sample manager account (username: admin, password: admin123)
INSERT INTO managers (username, password, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@library.com', 'System Administrator');

-- Insert sample student account (username: student, password: student123)
INSERT INTO students (username, password, email, full_name, status) VALUES 
('student', '$2y$10$8K1p/a0dL3LKzjqCd5Wj.O92Ej1pcZKACO.b/8wBvZnz4zfRk4OO6', 'student@example.com', 'John Doe', 'approved');

-- Insert sample books
INSERT INTO books (book_name, author, category, year, quantity, weekly_price, monthly_price) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 'Fiction', 1925, 5, 3.00, 10.00),
('To Kill a Mockingbird', 'Harper Lee', 'Fiction', 1960, 3, 3.50, 12.00),
('1984', 'George Orwell', 'Science Fiction', 1949, 4, 4.00, 14.00),
('Pride and Prejudice', 'Jane Austen', 'Romance', 1813, 6, 2.50, 8.00),
('The Catcher in the Rye', 'J.D. Salinger', 'Fiction', 1951, 2, 3.00, 10.00);


-- Create borrow_requests table
CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
    borrow_date DATE,
    return_date DATE,
    actual_return_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Create charges table
CREATE TABLE IF NOT EXISTS charges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    charge_type ENUM('weekly', 'monthly', 'late_fee') NOT NULL,
    description VARCHAR(255),
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
);
