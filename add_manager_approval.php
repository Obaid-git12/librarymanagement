<?php
include "db.php";

echo "<h2>🔧 Adding Manager Approval System</h2>";

// Add status column to managers
echo "<p>Adding status column to managers table...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM managers LIKE 'status'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE managers ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER password";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Status column added to managers</p>";
        
        // Update existing managers to approved
        mysqli_query($conn, "UPDATE managers SET status='approved'");
        echo "<p style='color: green;'>✅ Existing managers set to approved</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Status column already exists</p>";
}

// Add is_super_admin column
echo "<p>Adding is_super_admin column...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM managers LIKE 'is_super_admin'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE managers ADD COLUMN is_super_admin BOOLEAN DEFAULT FALSE AFTER status";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ is_super_admin column added</p>";
        
        // Set first manager (admin) as super admin
        mysqli_query($conn, "UPDATE managers SET is_super_admin=TRUE WHERE id=1");
        echo "<p style='color: green;'>✅ First manager set as super admin</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ is_super_admin column already exists</p>";
}

echo "<br><h3>✅ Manager Approval System Ready!</h3>";
echo "<p><strong>Features:</strong></p>";
echo "<ul>";
echo "<li>✅ New managers need approval from super admin</li>";
echo "<li>✅ First manager (admin) is automatically super admin</li>";
echo "<li>✅ Super admin can approve/reject manager registrations</li>";
echo "</ul>";

echo "<br>";
echo "<p><a href='manager_signup.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Test Manager Signup</a>";
echo "<a href='index.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
?>
