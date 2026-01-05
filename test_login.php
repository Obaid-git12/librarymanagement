<?php
include "db.php";

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn) {
    echo "✅ Database connected successfully<br><br>";
} else {
    echo "❌ Database connection failed<br><br>";
    exit();
}

// Check if managers table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'managers'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ Managers table exists<br>";
} else {
    echo "❌ Managers table does not exist. Please run setup_database.sql<br>";
}

// Check if students table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'students'");
if (mysqli_num_rows($result) > 0) {
    echo "✅ Students table exists<br><br>";
} else {
    echo "❌ Students table does not exist. Please run setup_database.sql<br><br>";
}

// List all managers
echo "<h3>All Managers:</h3>";
$managers = mysqli_query($conn, "SELECT id, username, email, full_name, created_at FROM managers");

if ($managers && mysqli_num_rows($managers) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Created</th></tr>";
    while ($row = mysqli_fetch_assoc($managers)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>" . ($row['full_name'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['email'] ?? 'N/A') . "</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No managers found in database.<br>";
}

// List all students
echo "<br><h3>All Students:</h3>";
$students = mysqli_query($conn, "SELECT id, username, email, full_name, status, created_at FROM students");

if ($students && mysqli_num_rows($students) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Username</th><th>Full Name</th><th>Email</th><th>Status</th><th>Created</th></tr>";
    while ($row = mysqli_fetch_assoc($students)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>" . ($row['full_name'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['email'] ?? 'N/A') . "</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No students found in database.<br>";
}

echo "<br><br><a href='login.php'>Go to Login</a> | <a href='signup.php'>Student Signup</a> | <a href='manager_signup.php'>Manager Signup</a>";
?>
