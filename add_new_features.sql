-- Add new features: Reviews, Ratings, and Profile fields
USE library_db;

-- Create book_reviews table
CREATE TABLE IF NOT EXISTS book_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    UNIQUE KEY unique_review (book_id, student_id)
);

-- Add phone number to students table
ALTER TABLE students ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email;

-- Add phone number to managers table
ALTER TABLE managers ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email;

-- Add address to students table
ALTER TABLE students ADD COLUMN IF NOT EXISTS address TEXT AFTER phone;

-- Add address to managers table
ALTER TABLE managers ADD COLUMN IF NOT EXISTS address TEXT AFTER phone;

SELECT 'New features added successfully!' as message;
