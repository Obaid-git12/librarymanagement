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
    $res = mysqli_query($conn, "SELECT * FROM managers WHERE username='$username'");
    if (mysqli_num_rows($res) > 0) {
        $error = "Username already exists!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // New managers need approval (status = pending)
        $result = mysqli_query($conn, "INSERT INTO managers (username, password, email, full_name, status) VALUES ('$username', '$hash', '$email', '$full_name', 'pending')");
        
        if ($result) {
            // Notify super admin
            mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, title, message, type, link) 
                                 SELECT id, 'manager', 'New Manager Registration', 'A new manager has registered and needs approval', 'warning', 'manage_managers.php'
                                 FROM managers WHERE is_super_admin=TRUE LIMIT 1");
            
            $success = "✅ Manager registration submitted successfully! Please wait for super admin approval before logging in.";
        } else {
            $error = "Error creating account: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manager Signup - Library Management</title>
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
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
<div class="container signup-container">
    <h2>👔 Manager Registration</h2>
    <p style="text-align: center; color: #666; margin-bottom: 20px;">Create your manager account</p>

    <div class="info-box">
        <strong>⚠️ Manager Account:</strong>
        <p style="margin: 10px 0 0 0; color: #666;">
            Manager accounts have full access to the library management system including book management, student approvals, and borrow requests.
        </p>
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
        <button type="submit" name="signup">👔 Create Manager Account</button>
    </form>

    <div style="text-align: center; margin-top: 25px;">
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <p style="margin-top: 10px;">
            <a href="signup.php">Register as Student</a>
        </p>
    </div>
</div>
</body>
</html>
