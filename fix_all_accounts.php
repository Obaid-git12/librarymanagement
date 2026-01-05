<?php
include "db.php";

echo "<h1>🔧 Fix All Accounts</h1>";
echo "<p>This will check and fix both manager and student accounts.</p>";
echo "<hr>";

// Check managers table
echo "<h2>1. Checking Managers Table...</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'managers'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ Managers table missing!</p>";
    echo "<p><a href='reset_database.php' style='padding: 10px; background: red; color: white; text-decoration: none;'>Click here to create all tables</a></p>";
} else {
    echo "<p style='color: green;'>✅ Managers table exists</p>";
    
    // Check/create admin
    $result = mysqli_query($conn, "SELECT * FROM managers WHERE username='admin'");
    if (mysqli_num_rows($result) == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO managers (username, password, email, full_name) VALUES ('admin', '$hash', 'admin@library.com', 'System Administrator')");
        echo "<p style='color: green;'>✅ Created admin account</p>";
    } else {
        $admin = mysqli_fetch_assoc($result);
        if (!password_verify('admin123', $admin['password'])) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE managers SET password='$hash' WHERE username='admin'");
            echo "<p style='color: orange;'>⚠️ Fixed admin password</p>";
        } else {
            echo "<p style='color: green;'>✅ Admin account OK</p>";
        }
    }
}

// Check students table
echo "<h2>2. Checking Students Table...</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'students'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ Students table missing!</p>";
    echo "<p><a href='reset_database.php' style='padding: 10px; background: red; color: white; text-decoration: none;'>Click here to create all tables</a></p>";
} else {
    echo "<p style='color: green;'>✅ Students table exists</p>";
    
    // Check/create student
    $result = mysqli_query($conn, "SELECT * FROM students WHERE username='student'");
    if (mysqli_num_rows($result) == 0) {
        $hash = password_hash('student123', PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO students (username, password, email, full_name, status) VALUES ('student', '$hash', 'student@example.com', 'John Doe', 'approved')");
        echo "<p style='color: green;'>✅ Created student account</p>";
    } else {
        $student = mysqli_fetch_assoc($result);
        
        $fixed = false;
        
        // Fix password
        if (!password_verify('student123', $student['password'])) {
            $hash = password_hash('student123', PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE students SET password='$hash' WHERE username='student'");
            echo "<p style='color: orange;'>⚠️ Fixed student password</p>";
            $fixed = true;
        }
        
        // Fix status
        if ($student['status'] != 'approved') {
            mysqli_query($conn, "UPDATE students SET status='approved' WHERE username='student'");
            echo "<p style='color: orange;'>⚠️ Fixed student status to 'approved'</p>";
            $fixed = true;
        }
        
        if (!$fixed) {
            echo "<p style='color: green;'>✅ Student account OK</p>";
        }
    }
}

echo "<hr>";
echo "<h2>✅ All Done!</h2>";
echo "<h3>Test Accounts:</h3>";
echo "<table border='1' cellpadding='10' style='margin: 20px 0;'>";
echo "<tr><th>Role</th><th>Username</th><th>Password</th><th>Status</th></tr>";
echo "<tr><td><strong>Manager</strong></td><td>admin</td><td>admin123</td><td style='color: green;'>Ready</td></tr>";
echo "<tr><td><strong>Student</strong></td><td>student</td><td>student123</td><td style='color: green;'>Ready</td></tr>";
echo "</table>";

echo "<p><a href='login.php' style='padding: 15px 30px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px; font-size: 18px;'>Go to Login Page</a></p>";

echo "<h3>Important:</h3>";
echo "<ul>";
echo "<li>Make sure to select the correct <strong>role</strong> when logging in</li>";
echo "<li>Manager login: Select 'Manager' button</li>";
echo "<li>Student login: Select 'Student' button</li>";
echo "</ul>";
?>
