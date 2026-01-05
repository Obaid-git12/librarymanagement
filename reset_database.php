<?php
include "db.php";

echo "<h2>🔄 Database Reset</h2>";
echo "<p>This will drop old tables and create new ones.</p>";

// Drop old tables
echo "<h3>Dropping old tables...</h3>";
mysqli_query($conn, "DROP TABLE IF EXISTS charges");
echo "✅ Dropped charges table<br>";

mysqli_query($conn, "DROP TABLE IF EXISTS borrow_requests");
echo "✅ Dropped borrow_requests table<br>";

mysqli_query($conn, "DROP TABLE IF EXISTS users");
echo "✅ Dropped users table<br>";

// Create managers table
echo "<br><h3>Creating new tables...</h3>";
$sql = "CREATE TABLE IF NOT EXISTS managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    echo "✅ Managers table created<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Create students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (mysqli_query($conn, $sql)) {
    echo "✅ Students table created<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Create borrow_requests table
$sql = "CREATE TABLE IF NOT EXISTS borrow_requests (
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
)";
if (mysqli_query($conn, $sql)) {
    echo "✅ Borrow requests table created<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Create charges table
$sql = "CREATE TABLE IF NOT EXISTS charges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    charge_type ENUM('weekly', 'monthly', 'late_fee') NOT NULL,
    description VARCHAR(255),
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id)
)";
if (mysqli_query($conn, $sql)) {
    echo "✅ Charges table created<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Insert default manager
echo "<br><h3>Creating default accounts...</h3>";
$sql = "INSERT INTO managers (username, password, email, full_name) VALUES 
('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@library.com', 'System Administrator')";
if (mysqli_query($conn, $sql)) {
    echo "✅ Default manager created (username: admin, password: admin123)<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

// Insert default student
$sql = "INSERT INTO students (username, password, email, full_name, status) VALUES 
('student', '\$2y\$10\$8K1p/a0dL3LKzjqCd5Wj.O92Ej1pcZKACO.b/8wBvZnz4zfRk4OO6', 'student@example.com', 'John Doe', 'approved')";
if (mysqli_query($conn, $sql)) {
    echo "✅ Default student created (username: student, password: student123)<br>";
} else {
    echo "❌ Error: " . mysqli_error($conn) . "<br>";
}

echo "<br><h3>✅ Database reset completed!</h3>";
echo "<p><a href='test_login.php'>Test Database</a> | <a href='login.php'>Login</a></p>";
?>
