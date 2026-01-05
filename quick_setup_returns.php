<?php
include "db.php";

echo "<h2>🔧 Quick Setup for Book Returns</h2>";

// Add return_requested column
echo "<p>Adding return_requested column...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM borrow_requests LIKE 'return_requested'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE borrow_requests ADD COLUMN return_requested BOOLEAN DEFAULT FALSE AFTER actual_return_date";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ return_requested column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ return_requested column already exists</p>";
}

// Add return_request_date column
echo "<p>Adding return_request_date column...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM borrow_requests LIKE 'return_request_date'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE borrow_requests ADD COLUMN return_request_date DATETIME DEFAULT NULL AFTER return_requested";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ return_request_date column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ return_request_date column already exists</p>";
}

// Add late fee fields to charges
echo "<p>Adding late fee fields to charges...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM charges LIKE 'is_late_fee'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE charges ADD COLUMN is_late_fee BOOLEAN DEFAULT FALSE AFTER charge_type";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ is_late_fee column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ is_late_fee column already exists</p>";
}

$result = mysqli_query($conn, "SHOW COLUMNS FROM charges LIKE 'late_fee_days'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE charges ADD COLUMN late_fee_days INT DEFAULT 0 AFTER is_late_fee";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ late_fee_days column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ late_fee_days column already exists</p>";
}

// Create notifications table (simplified)
echo "<p>Creating notifications table...</p>";
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_role ENUM('manager', 'student') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ Notifications table created</p>";
} else {
    if (strpos(mysqli_error($conn), 'already exists') !== false) {
        echo "<p style='color: green;'>✅ Notifications table already exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}

// Create book_reservations table (simplified)
echo "<p>Creating book_reservations table...</p>";
$sql = "CREATE TABLE IF NOT EXISTS book_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('active', 'notified', 'fulfilled', 'cancelled') DEFAULT 'active',
    reserved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notified_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ Book reservations table created</p>";
} else {
    if (strpos(mysqli_error($conn), 'already exists') !== false) {
        echo "<p style='color: green;'>✅ Book reservations table already exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}

echo "<br><h3>✅ Setup Complete!</h3>";
echo "<p><strong>Book Return System is now ready to use!</strong></p>";

echo "<br>";
echo "<p><a href='book_returns.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Open Book Returns</a>";
echo "<a href='index.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px;'>Manager Dashboard</a></p>";
?>
