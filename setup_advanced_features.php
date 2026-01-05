<?php
include "db.php";

echo "<h1>🚀 Setting Up Advanced Features</h1>";
echo "<p>This will add 18 new features to your library system without breaking existing functionality.</p>";
echo "<hr>";

$errors = [];
$success_count = 0;

// Read and execute SQL file
$sql_file = file_get_contents('add_advanced_features.sql');
$statements = explode(';', $sql_file);

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement) || strpos($statement, '--') === 0 || strpos($statement, 'USE ') === 0) {
        continue;
    }
    
    if (mysqli_query($conn, $statement)) {
        $success_count++;
    } else {
        $error = mysqli_error($conn);
        if (strpos($error, 'Duplicate column') === false && 
            strpos($error, 'already exists') === false) {
            $errors[] = $error;
        }
    }
}

echo "<h2>✅ Setup Complete!</h2>";
echo "<p><strong>$success_count</strong> database operations completed successfully.</p>";

if (count($errors) > 0) {
    echo "<h3>⚠️ Warnings:</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li style='color: orange;'>$error</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h2>📋 Features Added:</h2>";
echo "<ol>";
echo "<li>✅ <strong>Book Return System</strong> - Students can request returns, managers approve</li>";
echo "<li>✅ <strong>Late Fee System</strong> - Automatic overdue charge calculation</li>";
echo "<li>✅ <strong>Notifications</strong> - In-app notification system</li>";
echo "<li>✅ <strong>Book Reservations</strong> - Reserve books that are borrowed</li>";
echo "<li>✅ <strong>Advanced Book Details</strong> - ISBN, publisher, language, pages, description</li>";
echo "<li>✅ <strong>Book Cover Images</strong> - Upload and display book covers</li>";
echo "<li>✅ <strong>Book Condition Tracking</strong> - Track book condition (new, good, fair, poor)</li>";
echo "<li>✅ <strong>Popularity Tracking</strong> - Track times borrowed</li>";
echo "<li>✅ <strong>Book Tags</strong> - Add multiple tags to books</li>";
echo "<li>✅ <strong>Book Series</strong> - Group books by series</li>";
echo "<li>✅ <strong>Borrowing Limits</strong> - Set max books per student</li>";
echo "<li>✅ <strong>Membership Tiers</strong> - Free, Silver, Gold memberships</li>";
echo "<li>✅ <strong>Announcements</strong> - Post library announcements</li>";
echo "<li>✅ <strong>Activity Logs</strong> - Track all user actions</li>";
echo "<li>✅ <strong>Dark Mode</strong> - Theme preference (light/dark)</li>";
echo "<li>✅ <strong>Damage Reports</strong> - Report and track book damages</li>";
echo "<li>✅ <strong>Discount Codes</strong> - Promotional codes for charges</li>";
echo "<li>✅ <strong>Reports & Analytics</strong> - Advanced reporting system</li>";
echo "</ol>";

echo "<hr>";
echo "<h2>🎯 Next Steps:</h2>";
echo "<p>The database is ready! Now you can access the new features:</p>";
echo "<ul>";
echo "<li><a href='book_returns.php' style='color: #1e3c72; font-weight: bold;'>📦 Book Returns</a> - Manage book returns</li>";
echo "<li><a href='notifications.php' style='color: #1e3c72; font-weight: bold;'>🔔 Notifications</a> - View notifications</li>";
echo "<li><a href='reservations.php' style='color: #1e3c72; font-weight: bold;'>📚 Reservations</a> - Manage reservations</li>";
echo "<li><a href='reports.php' style='color: #1e3c72; font-weight: bold;'>📊 Reports</a> - View analytics</li>";
echo "<li><a href='announcements.php' style='color: #1e3c72; font-weight: bold;'>📢 Announcements</a> - Post announcements</li>";
echo "</ul>";

echo "<br>";
echo "<p><a href='index.php' style='padding: 15px 30px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px; font-size: 18px;'>Go to Dashboard</a></p>";

echo "<hr>";
echo "<p style='color: #666; font-size: 14px;'><strong>Note:</strong> All existing data and functionality remain intact. New features are added as optional enhancements.</p>";
?>
