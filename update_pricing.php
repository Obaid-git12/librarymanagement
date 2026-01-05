<?php
include "db.php";

echo "<h2>🔄 Updating Pricing System</h2>";

// Step 1: Check if weekly_price exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM books LIKE 'weekly_price'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding weekly_price column...</p>";
    $sql = "ALTER TABLE books ADD COLUMN weekly_price DECIMAL(10,2) DEFAULT 5.00 AFTER quantity";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Weekly price column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Weekly price column exists</p>";
}

// Step 2: Check if monthly_price exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM books LIKE 'monthly_price'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding monthly_price column...</p>";
    $sql = "ALTER TABLE books ADD COLUMN monthly_price DECIMAL(10,2) DEFAULT 15.00 AFTER weekly_price";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Monthly price column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Monthly price column exists</p>";
}

// Step 3: Drop old borrow_price column if exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM books LIKE 'borrow_price'");
if (mysqli_num_rows($result) > 0) {
    echo "<p>Removing old borrow_price column...</p>";
    mysqli_query($conn, "ALTER TABLE books DROP COLUMN borrow_price");
    echo "<p style='color: green;'>✅ Old column removed</p>";
}

// Step 4: Update existing books with default prices
echo "<p>Setting default prices...</p>";
mysqli_query($conn, "UPDATE books SET weekly_price = 5.00 WHERE weekly_price IS NULL OR weekly_price = 0");
mysqli_query($conn, "UPDATE books SET monthly_price = 15.00 WHERE monthly_price IS NULL OR monthly_price = 0");
echo "<p style='color: green;'>✅ Default prices set</p>";

// Step 5: Check if borrow_duration exists in borrow_requests
$result = mysqli_query($conn, "SHOW COLUMNS FROM borrow_requests LIKE 'borrow_duration'");
if (mysqli_num_rows($result) == 0) {
    echo "<p>Adding borrow_duration column to borrow_requests...</p>";
    $sql = "ALTER TABLE borrow_requests ADD COLUMN borrow_duration ENUM('weekly', 'monthly') DEFAULT 'weekly' AFTER book_id";
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Borrow duration column added</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Borrow duration column exists</p>";
}

// Step 6: Set sample prices for existing books
echo "<p>Setting sample prices for books...</p>";
mysqli_query($conn, "UPDATE books SET weekly_price = 3.00, monthly_price = 10.00 WHERE book_name LIKE '%Great Gatsby%'");
mysqli_query($conn, "UPDATE books SET weekly_price = 3.50, monthly_price = 12.00 WHERE book_name LIKE '%Mockingbird%'");
mysqli_query($conn, "UPDATE books SET weekly_price = 4.00, monthly_price = 14.00 WHERE book_name LIKE '%1984%'");
mysqli_query($conn, "UPDATE books SET weekly_price = 2.50, monthly_price = 8.00 WHERE book_name LIKE '%Pride%'");
mysqli_query($conn, "UPDATE books SET weekly_price = 3.00, monthly_price = 10.00 WHERE book_name LIKE '%Catcher%'");
echo "<p style='color: green;'>✅ Sample prices set</p>";

// Show current books with prices
echo "<br><h3>Current Books with Pricing:</h3>";
$books = mysqli_query($conn, "SELECT id, book_name, author, quantity, weekly_price, monthly_price FROM books");

if (mysqli_num_rows($books) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #1e3c72; color: white;'><th>ID</th><th>Book Name</th><th>Author</th><th>Qty</th><th>Weekly</th><th>Monthly</th></tr>";
    while ($row = mysqli_fetch_assoc($books)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['book_name']}</td>";
        echo "<td>{$row['author']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td style='color: green; font-weight: bold;'>\$" . number_format($row['weekly_price'], 2) . "/week</td>";
        echo "<td style='color: blue; font-weight: bold;'>\$" . number_format($row['monthly_price'], 2) . "/month</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No books found.</p>";
}

echo "<br><br>";
echo "<h3>✅ Pricing System Updated Successfully!</h3>";
echo "<p><strong>Features:</strong></p>";
echo "<ul>";
echo "<li>✅ Weekly pricing (7 days)</li>";
echo "<li>✅ Monthly pricing (30 days)</li>";
echo "<li>✅ Students can choose duration when borrowing</li>";
echo "<li>✅ Charges calculated based on selected duration</li>";
echo "</ul>";

echo "<br>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Manager Dashboard</a>";
echo "<a href='student_dashboard.php' style='padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Student Dashboard</a></p>";
?>
