<?php
session_start();
include "db.php";

$search = $_GET['search'] ?? "";
if ($search) {
    $books = mysqli_query($conn,
        "SELECT * FROM books
         WHERE book_name LIKE '%$search%'
         OR author LIKE '%$search%'
         OR category LIKE '%$search%'"
    );
} else {
    $books = mysqli_query($conn, "SELECT * FROM books");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">📚 Library Inventory</h2>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: #666;">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <a href="logout.php" style="margin-left: 15px; padding: 8px 16px; background: #d32f2f; color: white; border-radius: 20px; text-decoration: none;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="padding: 8px 16px; background: #1e3c72; color: white; border-radius: 20px; text-decoration: none;">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <form method="get">
        <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <tr>
            <th>ID</th><th>Book</th><th>Author</th><th>Category</th><th>Year</th><th>Quantity</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($books)) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['book_name'] ?></td>
            <td><?= $row['author'] ?></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['year'] ?></td>
            <td><?= $row['quantity'] ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>