<?php
include "db.php";

echo "<h2>🔍 Checking Book Returns Setup</h2>";

// Check return_requested column
$result = mysqli_query($conn, "SHOW COLUMNS FROM borrow_requests LIKE 'return_requested'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ return_requested column is MISSING</p>";
    echo "<p><strong>Run this SQL:</strong></p>";
    echo "<pre>ALTER TABLE borrow_requests ADD COLUMN return_requested BOOLEAN DEFAULT FALSE;</pre>";
} else {
    echo "<p style='color: green;'>✅ return_requested column exists</p>";
}

// Check return_request_date column
$result = mysqli_query($conn, "SHOW COLUMNS FROM borrow_requests LIKE 'return_request_date'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ return_request_date column is MISSING</p>";
    echo "<p><strong>Run this SQL:</strong></p>";
    echo "<pre>ALTER TABLE borrow_requests ADD COLUMN return_request_date DATETIME DEFAULT NULL;</pre>";
} else {
    echo "<p style='color: green;'>✅ return_request_date column exists</p>";
}

// Check notifications table
$result = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ notifications table is MISSING</p>";
} else {
    echo "<p style='color: green;'>✅ notifications table exists</p>";
}

echo "<hr>";
echo "<h3>Quick Fix:</h3>";
echo "<p>Click the button below to automatically fix all issues:</p>";
echo "<form method='post'>";
echo "<button type='submit' name='auto_fix' style='padding: 15px 30px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>🔧 Auto Fix Now</button>";
echo "</form>";

if (isset($_POST['auto_fix'])) {
    echo "<hr>";
    echo "<h3>Running Auto Fix...</h3>";
    
    // Fix 1
    mysqli_query($conn, "ALTER TABLE borrow_requests ADD COLUMN return_requested BOOLEAN DEFAULT FALSE");
    echo "<p>✅ Added return_requested</p>";
    
    // Fix 2
    mysqli_query($conn, "ALTER TABLE borrow_requests ADD COLUMN return_request_date DATETIME DEFAULT NULL");
    echo "<p>✅ Added return_request_date</p>";
    
    // Fix 3
    mysqli_query($conn, "ALTER TABLE charges ADD COLUMN is_late_fee BOOLEAN DEFAULT FALSE");
    echo "<p>✅ Added is_late_fee</p>";
    
    // Fix 4
    mysqli_query($conn, "ALTER TABLE charges ADD COLUMN late_fee_days INT DEFAULT 0");
    echo "<p>✅ Added late_fee_days</p>";
    
    // Fix 5
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_role ENUM('manager', 'student') NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
        is_read BOOLEAN DEFAULT FALSE,
        link VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p>✅ Created notifications table</p>";
    
    // Fix 6
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS book_reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT NOT NULL,
        student_id INT NOT NULL,
        status ENUM('active', 'notified', 'fulfilled', 'cancelled') DEFAULT 'active',
        reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notified_at DATETIME DEFAULT NULL,
        expires_at DATETIME DEFAULT NULL,
        FOREIGN KEY (book_id) REFERENCES books(id),
        FOREIGN KEY (student_id) REFERENCES students(id)
    )");
    echo "<p>✅ Created book_reservations table</p>";
    
    echo "<br><h3 style='color: green;'>✅ All Fixed!</h3>";
    echo "<p><a href='book_returns.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px;'>Go to Book Returns</a></p>";
}

echo "<br><hr>";
echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
?>
