<?php
session_start();
include "db.php";

$error = "";

// Handle login
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role == 'manager') {
        $res = mysqli_query($conn, "SELECT * FROM managers WHERE username='$username'");
    } else {
        $res = mysqli_query($conn, "SELECT * FROM students WHERE username='$username'");
    }
    
    if (mysqli_num_rows($res) > 0) {
        $user = mysqli_fetch_assoc($res);
        
        if (password_verify($password, $user['password'])) {
            // Check if account is approved
            if (isset($user['status']) && $user['status'] == 'pending') {
                $error = "⏳ Your account is pending approval. Please wait for " . ($role == 'manager' ? 'super admin' : 'manager') . " to approve.";
            } elseif (isset($user['status']) && $user['status'] == 'rejected') {
                $error = "❌ Your account has been rejected. Please contact the administrator.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $role;
                $_SESSION['is_super_admin'] = $user['is_super_admin'] ?? false;
                
                if ($role == 'manager') {
                    header("Location: index.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit();
            }
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ User not found or incorrect role selected!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: url('https://thumbs.dreamstime.com/b/aesthetic-library-beautifully-designed-serene-space-featuring-cozy-seating-areas-rows-books-310702416.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            min-height: 100vh;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }
        
        .container {
            position: relative;
            z-index: 1;
        }
        
        .login-container {
            max-width: 450px;
            margin: 80px auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .role-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .role-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #1e3c72;
            background: white;
            color: #1e3c72;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            font-size: 16px;
        }
        .role-btn:hover {
            background: #f0f4ff;
            transform: translateY(-2px);
        }
        .role-btn.active {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .login-form input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .login-form button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
        }
        .error-msg {
            color: #d32f2f;
            background: #ffebee;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        .signup-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .divider {
            text-align: center;
            margin: 25px 0;
            color: #999;
            position: relative;
        }
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #ddd;
        }
        .divider::before { left: 0; }
        .divider::after { right: 0; }
    </style>
</head>
<body>
<div class="container login-container">
    <h2>🔐 Library Management System</h2>
    <p style="text-align: center; color: #666; margin-bottom: 25px;">Please login to continue</p>

    <?php if ($error): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="login-form" id="loginForm">
        <div class="role-selector">
            <button type="button" class="role-btn active" data-role="manager" onclick="selectRole('manager')">
                👔 Manager
            </button>
            <button type="button" class="role-btn" data-role="student" onclick="selectRole('student')">
                🎓 Student
            </button>
        </div>

        <input type="hidden" name="role" id="roleInput" value="manager">
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <button type="submit" name="login">Login</button>
    </form>

    <div class="divider">OR</div>

    <div class="signup-link">
        <p>Don't have an account?</p>
        <p>
            <a href="signup.php">Sign up as Student</a> | 
            <a href="manager_signup.php">Sign up as Manager</a>
        </p>
    </div>

    <div class="divider">Quick Access</div>
    
    <div style="text-align: center; margin-top: 15px;">
        <a href="student_inventory.php" style="display: inline-block; padding: 10px 20px; background: #4caf50; color: white; border-radius: 20px; text-decoration: none;">
            📚 View Library Inventory (No Login Required)
        </a>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="test_login.php" style="color: #999; font-size: 12px;">🔧 Test Database Connection</a>
    </div>
</div>

<script>
function selectRole(role) {
    // Update hidden input
    document.getElementById('roleInput').value = role;
    
    // Update button styles
    document.querySelectorAll('.role-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-role="${role}"]`).classList.add('active');
}
</script>
</body>
</html>
