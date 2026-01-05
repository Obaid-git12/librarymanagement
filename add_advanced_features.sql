-- Advanced Features Database Setup
USE library_db;

-- 1. Add fields for book return system
ALTER TABLE borrow_requests ADD COLUMN IF NOT EXISTS return_requested BOOLEAN DEFAULT FALSE AFTER actual_return_date;
ALTER TABLE borrow_requests ADD COLUMN IF NOT EXISTS return_request_date DATETIME DEFAULT NULL AFTER return_requested;

-- 2. Add late fee tracking
ALTER TABLE charges ADD COLUMN IF NOT EXISTS late_fee_days INT DEFAULT 0 AFTER charge_type;
ALTER TABLE charges ADD COLUMN IF NOT EXISTS is_late_fee BOOLEAN DEFAULT FALSE AFTER late_fee_days;

-- 3. Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_role ENUM('manager', 'student') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Create book reservations table
CREATE TABLE IF NOT EXISTS book_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('active', 'notified', 'fulfilled', 'cancelled') DEFAULT 'active',
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notified_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- 5. Add book details fields
ALTER TABLE books ADD COLUMN IF NOT EXISTS isbn VARCHAR(20) DEFAULT NULL AFTER book_name;
ALTER TABLE books ADD COLUMN IF NOT EXISTS publisher VARCHAR(255) DEFAULT NULL AFTER author;
ALTER TABLE books ADD COLUMN IF NOT EXISTS language VARCHAR(50) DEFAULT 'English' AFTER category;
ALTER TABLE books ADD COLUMN IF NOT EXISTS pages INT DEFAULT NULL AFTER year;
ALTER TABLE books ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL AFTER pages;
ALTER TABLE books ADD COLUMN IF NOT EXISTS cover_image VARCHAR(255) DEFAULT NULL AFTER description;
ALTER TABLE books ADD COLUMN IF NOT EXISTS condition_status ENUM('new', 'good', 'fair', 'poor') DEFAULT 'good' AFTER cover_image;
ALTER TABLE books ADD COLUMN IF NOT EXISTS times_borrowed INT DEFAULT 0 AFTER condition_status;

-- 6. Create book tags table
CREATE TABLE IF NOT EXISTS book_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id),
    INDEX idx_tag (tag_name)
);

-- 7. Create book series table
CREATE TABLE IF NOT EXISTS book_series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    series_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE books ADD COLUMN IF NOT EXISTS series_id INT DEFAULT NULL AFTER category;
ALTER TABLE books ADD COLUMN IF NOT EXISTS series_order INT DEFAULT NULL AFTER series_id;

-- 8. Add borrowing limits to students
ALTER TABLE students ADD COLUMN IF NOT EXISTS max_books INT DEFAULT 3 AFTER status;
ALTER TABLE students ADD COLUMN IF NOT EXISTS membership_tier ENUM('free', 'silver', 'gold') DEFAULT 'free' AFTER max_books;

-- 9. Create announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('info', 'warning', 'success') DEFAULT 'info',
    is_pinned BOOLEAN DEFAULT FALSE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES managers(id)
);

-- 10. Create activity logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_role ENUM('manager', 'student') NOT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_role),
    INDEX idx_created (created_at)
);

-- 11. Add theme preference
ALTER TABLE students ADD COLUMN IF NOT EXISTS theme_preference ENUM('light', 'dark') DEFAULT 'light' AFTER membership_tier;
ALTER TABLE managers ADD COLUMN IF NOT EXISTS theme_preference ENUM('light', 'dark') DEFAULT 'light' AFTER address;

-- 12. Create book damage reports
CREATE TABLE IF NOT EXISTS book_damage_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    borrow_request_id INT DEFAULT NULL,
    damage_description TEXT NOT NULL,
    damage_severity ENUM('minor', 'moderate', 'severe') NOT NULL,
    charge_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('reported', 'reviewed', 'charged', 'resolved') DEFAULT 'reported',
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (borrow_request_id) REFERENCES borrow_requests(id)
);

-- 13. Add discount codes table
CREATE TABLE IF NOT EXISTS discount_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    max_uses INT DEFAULT NULL,
    times_used INT DEFAULT 0,
    valid_from DATE DEFAULT NULL,
    valid_until DATE DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 14. Track discount usage
CREATE TABLE IF NOT EXISTS discount_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discount_code_id INT NOT NULL,
    student_id INT NOT NULL,
    charge_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discount_code_id) REFERENCES discount_codes(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (charge_id) REFERENCES charges(id)
);

SELECT 'Advanced features database setup completed!' as message;
