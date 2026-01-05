<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$success = "";
$error = "";

// Get user data
if ($role == 'manager') {
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM managers WHERE id=$user_id"));
} else {
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$user_id"));
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if ($role == 'manager') {
        $sql = "UPDATE managers SET full_name='$full_name', email='$email', phone='$phone', address='$address' WHERE id=$user_id";
    } else {
        $sql = "UPDATE students SET full_name='$full_name', email='$email', phone='$phone', address='$address' WHERE id=$user_id";
    }
    
    if (mysqli_query($conn, $sql)) {
        $success = "✅ Profile updated successfully!";
        // Refresh user data
        if ($role == 'manager') {
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM managers WHERE id=$user_id"));
        } else {
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM students WHERE id=$user_id"));
        }
    } else {
        $error = "❌ Error updating profile: " . mysqli_error($conn);
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                if ($role == 'manager') {
                    mysqli_query($conn, "UPDATE managers SET password='$hash' WHERE id=$user_id");
                } else {
                    mysqli_query($conn, "UPDATE students SET password='$hash' WHERE id=$user_id");
                }
                
                $success = "✅ Password changed successfully!";
            } else {
                $error = "❌ Password must be at least 6 characters!";
            }
        } else {
            $error = "❌ New passwords don't match!";
        }
    } else {
        $error = "❌ Current password is incorrect!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
        }
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .profile-header {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
    </style>
</head>
<body>
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?= $role == 'manager' ? '👔' : '🎓' ?>
        </div>
        <h2><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h2>
        <p><?= ucfirst($role) ?> Account</p>
    </div>

    <?php if ($success): ?>
        <div class="message success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Profile Information -->
    <div class="profile-card">
        <h3>📋 Profile Information</h3>
        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+1 (555) 123-4567">
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" placeholder="Enter your full address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" name="update_profile" class="btn-primary">💾 Update Profile</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="profile-card">
        <h3>🔒 Change Password</h3>
        <form method="post">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" minlength="6" required>
            </div>
            
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" minlength="6" required>
            </div>
            
            <button type="submit" name="change_password" class="btn-primary">🔑 Change Password</button>
        </form>
    </div>

    <!-- Account Details -->
    <div class="profile-card">
        <h3>ℹ️ Account Details</h3>
        <div class="info-row">
            <span class="info-label">Account Type:</span>
            <span><?= ucfirst($role) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Member Since:</span>
            <span><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
        </div>
        <?php if ($role == 'student'): ?>
        <div class="info-row">
            <span class="info-label">Account Status:</span>
            <span style="color: <?= $user['status'] == 'approved' ? 'green' : 'orange' ?>; font-weight: bold;">
                <?= ucfirst($user['status']) ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="<?= $role == 'manager' ? 'index.php' : 'student_dashboard.php' ?>" 
           style="padding: 12px 30px; background: #6c757d; color: white; border-radius: 25px; text-decoration: none; display: inline-block;">
            ← Back to Dashboard
        </a>
    </div>
</div>
</body>
</html>
