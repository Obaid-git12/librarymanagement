<?php
include "db.php";

echo "<h2>Adding Borrow Price Column to Books Table</h2>";

// Check if column already exists
$result = mysqli_query($conn, "SHOW COLUMNS FROM books LIKE 'borrow_price'");

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: green;'>✅ Borrow price column already exists!</p>";
} else {
    echo "<p>Adding borrow_price column...</p>";
    
    $sql = "ALTER TABLE books ADD COLUMN borrow_price DECIMAL(10,2) DEFAULT 5.00 AFTER quantity";
    
    if (mysqli_query($conn, $sql)) {
        echo "<p style='color: green;'>✅ Borrow price column added successfully!</p>";
        
        // Update existing books with default prices
        echo "<p>Setting default prices for existing books...</p>";
        mysqli_query($conn, "UPDATE books SET borrow_price = 5.00 WHERE borrow_price IS NULL");
        echo "<p style='color: green;'>✅ Default prices set!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}

// Show current books with prices
echo "<br><h3>Current Books with Prices:</h3>";
$books = mysqli_query($conn, "SELECT * FROM books");

if (mysqli_num_rows($books) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Book Name</th><th>Author</th><th>Quantity</th><th>Borrow Price</th></tr>";
    while ($row = mysqli_fetch_assoc($books)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['book_name']}</td>";
        echo "<td>{$row['author']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>\$" . number_format($row['borrow_price'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No books found.</p>";
}

echo "<br><br>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #1e3c72; color: white; text-decoration: none; border-radius: 5px;'>Go to Manager Dashboard</a></p>";
echo "<p><a href='student_dashboard.php' style='padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 5px;'>Go to Student Dashboard</a></p>";
?>
