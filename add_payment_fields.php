<?php
include "db.php";

echo "<h2>💳 Adding Payment Management Fields</h2>";

// Add payment_method column
echo "<p>Adding payment_method column...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM charges LIKE 'payment_method'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE charges ADD COLUMN payment_method ENUM('cash', 'card', 'online') DEFAULT NULL AFTER status";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Payment method column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Payment method column already exists</p>";
}

// Add payment_date column
echo "<p>Adding payment_date column...</p>";
$result = mysqli_query($conn, "SHOW COLUMNS FROM charges LIKE 'payment_date'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE charges ADD COLUMN payment_date DATETIME DEFAULT NULL AFTER payment_method";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Payment date column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Payment date column already exists</p>";
}

echo "<br><h3>✅ Payment Management System Ready!</h3>";
echo "<p><strong>Features Added:</strong></p>";
echo "<ul>";
echo "<li>✅ Payment method tracking (Cash, Card, Online)</li>";
echo "<li>✅ Payment date recording</li>";
echo "<li>✅ Revenue statistics</li>";
echo "<li>✅ Payment method breakdown</li>";
echo "</ul>";

echo "<br>";
echo "<p><a href='manage_payments.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Open Payment Management</a>";
echo "<a href='index.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px;'>Manager Dashboard</a></p>";
?>
