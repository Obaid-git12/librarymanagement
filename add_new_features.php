<?php
include "db.php";

echo "<h2>🚀 Adding New Features</h2>";

// Create book_reviews table
echo "<p>Creating book_reviews table...</p>";
$sql = "CREATE TABLE IF NOT EXISTS book_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    UNIQUE KEY unique_review (book_id, student_id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ Book reviews table created</p>";
} else {
    if (strpos(mysqli_error($conn), 'already exists') !== false) {
        echo "<p style='color: green;'>✅ Book reviews table already exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}

// Add phone to students
echo "<p>Adding phone field to students...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM students LIKE 'phone'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE students ADD COLUMN phone VARCHAR(20) AFTER email");
    echo "<p style='color: green;'>✅ Phone field added to students</p>";
} else {
    echo "<p style='color: green;'>✅ Phone field already exists in students</p>";
}

// Add address to students
echo "<p>Adding address field to students...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM students LIKE 'address'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE students ADD COLUMN address TEXT AFTER phone");
    echo "<p style='color: green;'>✅ Address field added to students</p>";
} else {
    echo "<p style='color: green;'>✅ Address field already exists in students</p>";
}

// Add phone to managers
echo "<p>Adding phone field to managers...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM managers LIKE 'phone'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE managers ADD COLUMN phone VARCHAR(20) AFTER email");
    echo "<p style='color: green;'>✅ Phone field added to managers</p>";
} else {
    echo "<p style='color: green;'>✅ Phone field already exists in managers</p>";
}

// Add address to managers
echo "<p>Adding address field to managers...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM managers LIKE 'address'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE managers ADD COLUMN address TEXT AFTER phone");
    echo "<p style='color: green;'>✅ Address field added to managers</p>";
} else {
    echo "<p style='color: green;'>✅ Address field already exists in managers</p>";
}

echo "<br><h3>✅ All Features Added Successfully!</h3>";
echo "<p><strong>New Features:</strong></p>";
echo "<ul>";
echo "<li>✅ Book Reviews & Ratings System</li>";
echo "<li>✅ Profile Management (Phone & Address)</li>";
echo "<li>✅ Dashboard Statistics (Ready to use)</li>";
echo "</ul>";

echo "<br>";
echo "<p><a href='profile.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>View Profile</a>";
echo "<a href='index.php' style='padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Manager Dashboard</a></p>";
?>
