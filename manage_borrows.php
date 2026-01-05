<?php
session_start();

// Check if user is logged in as manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit();
}

include "db.php";

// Approve borrow request
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    
    // Get borrow request details
    $borrow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM borrow_requests WHERE id=$id"));
    $book_id = $borrow['book_id'];
    $student_id = $borrow['student_id'];
    $duration = $borrow['borrow_duration'];
    
    // Get book price based on duration
    $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT weekly_price, monthly_price FROM books WHERE id=$book_id"));
    
    if ($duration == 'weekly') {
        $price = $book['weekly_price'];
        $days = 7;
        $description = 'Weekly book rental fee (7 days)';
    } else {
        $price = $book['monthly_price'];
        $days = 30;
        $description = 'Monthly book rental fee (30 days)';
    }
    
    // Update borrow request
    $borrow_date = date('Y-m-d');
    $return_date = date('Y-m-d', strtotime("+$days days"));
    
    mysqli_query($conn, "UPDATE borrow_requests SET status='approved', borrow_date='$borrow_date', return_date='$return_date' WHERE id=$id");
    
    // Decrease book quantity
    mysqli_query($conn, "UPDATE books SET quantity = quantity - 1 WHERE id=$book_id");
    
    // Add charge based on duration
    mysqli_query($conn, "INSERT INTO charges (student_id, amount, charge_type, description) VALUES ($student_id, $price, '$duration', '$description')");
    
    header("Location: index.php");
    exit();
}

// Reject borrow request
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    mysqli_query($conn, "UPDATE borrow_requests SET status='rejected' WHERE id=$id");
    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();
?>
