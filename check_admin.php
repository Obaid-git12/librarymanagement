<?php
include "db.php";

echo "<h2>Admin Account Check</h2>";

// Check if managers table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'managers'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ Managers table does NOT exist!</p>";
    echo "<p><strong>Solution:</strong> Visit <a href='reset_database.php'>reset_database.php</a> to create the tables.</p>";
    exit();
} else {
    echo "<p style='color: green;'>✅ Managers table exists</p>";
}

// Check if admin exists
$result = mysqli_query($conn, "SELECT * FROM managers WHERE username='admin'");
if (mysqli_num_rows($result) == 0) {
    echo "<p style='color: red;'>❌ Admin account does NOT exist!</p>";
    echo "<p><strong>Creating admin account now...</strong></p>";
    
    // Create admin account
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO managers (username, password, email, full_name) VALUES ('admin', '$hash', 'admin@library.com', 'System Administrator')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Admin account created successfully!</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        echo "<p style='color: red;'>❌ Error creating admin: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Admin account exists</p>";
    
    // Show admin details
    $admin = mysqli_fetch_assoc($result);
    echo "<h3>Admin Account Details:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><td>{$admin['id']}</td></tr>";
    echo "<tr><th>Username</th><td>{$admin['username']}</td></tr>";
    echo "<tr><th>Email</th><td>" . ($admin['email'] ?? 'N/A') . "</td></tr>";
    echo "<tr><th>Full Name</th><td>" . ($admin['full_name'] ?? 'N/A') . "</td></tr>";
    echo "<tr><th>Password Hash</th><td>" . substr($admin['password'], 0, 30) . "...</td></tr>";
    echo "</table>";
    
    // Test password
    echo "<h3>Password Test:</h3>";
    if (password_verify('admin123', $admin['password'])) {
        echo "<p style='color: green;'>✅ Password 'admin123' is CORRECT!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password 'admin123' does NOT match!</p>";
        echo "<p><strong>Fixing password now...</strong></p>";
        
        $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE managers SET password='$new_hash' WHERE username='admin'");
        echo "<p style='color: green;'>✅ Password reset to 'admin123'</p>";
    }
}

echo "<br><br>";
echo "<h3>Try Login Now:</h3>";
echo "<p><a href='login.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
echo "<p><strong>Username:</strong> admin</p>";
echo "<p><strong>Password:</strong> admin123</p>";
echo "<p><strong>Role:</strong> Select 'Manager'</p>";
?>
