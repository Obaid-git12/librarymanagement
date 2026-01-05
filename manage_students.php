<?php
session_start();

// Check if user is logged in as manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit();
}

include "db.php";

// Approve student
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    mysqli_query($conn, "UPDATE students SET status='approved' WHERE id=$id");
    header("Location: index.php");
    exit();
}

// Reject student
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    mysqli_query($conn, "UPDATE students SET status='rejected' WHERE id=$id");
    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();
?>
