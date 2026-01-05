<?php
include "db.php";

echo "<h2>Student Account Check</h2>";

// Check if students table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'students'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ Students table does NOT exist!</p>";
    echo "<p><strong>Solution:</strong> Visit <a href='reset_database.php'>reset_database.php</a> to create the tables.</p>";
    exit();
} else {
    echo "<p style='color: green;'>✅ Students table exists</p>";
}

// Check if student exists
$result = mysqli_query($conn, "SELECT * FROM students WHERE username='student'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ Student account does NOT exist!</p>";
    echo "<p><strong>Creating student account now...</strong></p>";
    
    // Create student account
    $hash = password_hash('student123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO students (username, password, email, full_name, status) VALUES ('student', '$hash', 'student@example.com', 'John Doe', 'approved')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Student account created successfully!</p>";
        echo "<p><strong>Username:</strong> student</p>";
        echo "<p><strong>Password:</strong> student123</p>";
        echo "<p><strong>Status:</strong> approved</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating student: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Student account exists</p>";
    
    // Show student details
    $student = mysqli_fetch_assoc($result);
    echo "<h3>Student Account Details:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><td>{$student['id']}</td></tr>";
    echo "<tr><th>Username</th><td>{$student['username']}</td></tr>";
    echo "<tr><th>Email</th><td>" . ($student['email'] ?? 'N/A') . "</td></tr>";
    echo "<tr><th>Full Name</th><td>" . ($student['full_name'] ?? 'N/A') . "</td></tr>";
    echo "<tr><th>Status</th><td><strong>{$student['status']}</strong></td></tr>";
    echo "<tr><th>Password Hash</th><td>" . substr($student['password'], 0, 30) . "...</td></tr>";
    echo "</table>";
    
    // Check status
    if ($student['status'] != 'approved') {
        echo "<p style='color: orange;'>⚠️ Student status is '{$student['status']}' - changing to 'approved'...</p>";
        mysqli_query($conn, "UPDATE students SET status='approved' WHERE username='student'");
        echo "<p style='color: green;'>✅ Status updated to 'approved'</p>";
    }
    
    // Test password
    echo "<h3>Password Test:</h3>";
    if (password_verify('student123', $student['password'])) {
        echo "<p style='color: green;'>✅ Password 'student123' is CORRECT!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password 'student123' does NOT match!</p>";
        echo "<p><strong>Fixing password now...</strong></p>";
        
        $new_hash = password_hash('student123', PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE students SET password='$new_hash' WHERE username='student'");
        echo "<p style='color: green;'>✅ Password reset to 'student123'</p>";
    }
}

echo "<br><br>";
echo "<h3>Try Login Now:</h3>";
echo "<p><a href='login.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
echo "<p><strong>Username:</strong> student</p>";
echo "<p><strong>Password:</strong> student123</p>";
echo "<p><strong>Role:</strong> Select 'Student' (NOT Manager)</p>";
?>
