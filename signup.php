<?php
session_start();
include "db.php";

$error = "";
$success = "";

if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);

    // Check if username exists
    $res = mysqli_query($conn, "SELECT * FROM students WHERE username='$username'");
    if (mysqli_num_rows($res) > 0) {
        $error = "Username already exists!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $result = mysqli_query($conn, "INSERT INTO students (username, password, email, full_name, status) VALUES ('$username', '$hash', '$email', '$full_name', 'pending')");
        
        if ($result) {
            $success = "✅ Account request submitted successfully! Please wait for manager approval before logging in. You will be notified once approved.";
        } else {
            $error = "Error creating account: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Signup - Library Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .signup-container {
            max-width: 500px;
            margin: 60px auto;
        }
        .signup-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .signup-form input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .signup-form button {
            width: 100%;
            padding: 14px;
            font-size: 16px;
        }
        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #1e3c72;
        }
    </style>
</head>
<body>
<div class="container signup-container">
    <h2>🎓 Student Registration</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Create your student account</p>

    <div class="info-box">
        <strong>📋 Registration Process:</strong>
        <ul style="margin: 10px 0 0 20px; color: #666;">
            <li>Fill out the registration form</li>
            <li>Wait for manager approval</li>
            <li>Login once approved</li>
        </ul>
    </div>

    <?php if ($error): ?>
        <div class="message error-msg"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="message success-msg"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" class="signup-form">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="text" name="username" placeholder="Username" required minlength="4">
        <input type="password" name="password" placeholder="Password" required minlength="6">
        <button type="submit" name="signup">📝 Submit Registration</button>
    </form>

    <div style="text-align: center; margin-top: 25px;">
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <p style="margin-top: 10px;">
            <a href="manager_signup.php">Register as Manager</a>
        </p>
        <p style="margin-top: 15px;">
            <a href="student_inventory.php" style="color: #4caf50;">📚 View Library Inventory</a>
        </p>
    </div>
</div>
</body>
</html>