<?php
include "db.php";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

/* FETCH BOOK */
$res = mysqli_query($conn, "SELECT * FROM books WHERE id=$id");
$book = mysqli_fetch_assoc($res);

/* UPDATE BOOK */
if (isset($_POST['update'])) {
    $name = $_POST['book'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $year = $_POST['year'];
    $quantity = $_POST['quantity'];

    mysqli_query($conn,
        "UPDATE books SET
        book_name='$name',
        author='$author',
        category='$category',
        year='$year',
        quantity='$quantity'
        WHERE id=$id"
    );

    header("Location: index.php");
}

/* DELETE BOOK */
if (isset($_POST['delete'])) {
    mysqli_query($conn, "DELETE FROM books WHERE id=$id");
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Book</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Edit / Manage Book</h2>

    <form method="post">
        <input type="text" name="book" value="<?= $book['book_name'] ?>" required>
        <input type="text" name="author" value="<?= $book['author'] ?>" required>
        <input type="text" name="category" value="<?= $book['category'] ?>" required>
        <input type="number" name="year" value="<?= $book['year'] ?>" required>
        <input type="number" name="quantity" value="<?= $book['quantity'] ?>" required>

        <button type="submit" name="update">Update Book</button>
        <button type="submit" name="delete"
                onclick="return confirm('Are you sure you want to delete this book?')"
                style="background:red;">
            Delete Book
        </button>
    </form>

    <br>
    <a href="index.php">⬅ Back to List</a>
</div>

</body>
</html>