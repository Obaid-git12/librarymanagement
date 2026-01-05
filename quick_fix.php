<?php
include "db.php";

echo "<h2>Quick Database Fix</h2>";
echo "<p>This will add missing columns to your database.</p>";

// Check if status column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding 'status' column...</p>";
    $sql = "ALTER TABLE users ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER role";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Status column added successfully<br>";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ Status column already exists<br>";
}

// Check if email column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'email'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding 'email' column...</p>";
    $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(100) AFTER status";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Email column added successfully<br>";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ Email column already exists<br>";
}

// Check if full_name column exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'full_name'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding 'full_name' column...</p>";
    $sql = "ALTER TABLE users ADD COLUMN full_name VARCHAR(100) AFTER email";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Full name column added successfully<br>";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ Full name column already exists<br>";
}

// Update existing users to approved
echo "<p>Updating existing users to 'approved' status...</p>";
$sql = "UPDATE users SET status = 'approved' WHERE status IS NULL OR status = ''";
if (mysqli_query($conn, $sql)) {
    echo "✅ Existing users updated<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Create borrow_requests table
$result = mysqli_query($conn, "SHOW TABLES LIKE 'borrow_requests'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Creating 'borrow_requests' table...</p>";
    $sql = "CREATE TABLE borrow_requests (
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
    )";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Borrow requests table created<br>";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ Borrow requests table already exists<br>";
}

// Create charges table
$result = mysqli_query($conn, "SHOW TABLES LIKE 'charges'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Creating 'charges' table...</p>";
    $sql = "CREATE TABLE charges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        charge_type ENUM('weekly', 'monthly', 'late_fee') NOT NULL,
        description VARCHAR(255),
        status ENUM('pending', 'paid') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id)
    )";
    if (mysqli_query($conn, $sql)) {
        echo "✅ Charges table created<br>";
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "✅ Charges table already exists<br>";
}

echo "<br><h3>✅ Database fix completed!</h3>";
echo "<p><a href='test_login.php'>Test Database</a> | <a href='signup.php'>Try Signup Again</a> | <a href='login.php'>Login</a></p>";
?>
