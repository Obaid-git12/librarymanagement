<?php
session_start();

// Check if user is super admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager' || !$_SESSION['is_super_admin']) {
    header("Location: index.php");
    exit();
}

include "db.php";

// Approve manager
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    mysqli_query($conn, "UPDATE managers SET status='approved' WHERE id=$id");
    
    // Notify the manager
    mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, title, message, type) 
                         VALUES ($id, 'manager', 'Account Approved', 'Your manager account has been approved! You can now login.', 'success')");
    
    header("Location: manage_managers.php?success=1");
    exit();
}

// Reject manager
if (isset($_GET['reject'])) {
    $id = $_GET['reject'];
    mysqli_query($conn, "UPDATE managers SET status='rejected' WHERE id=$id");
    
    // Notify the manager
    mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, title, message, type) 
                         VALUES ($id, 'manager', 'Account Rejected', 'Your manager registration has been rejected.', 'danger')");
    
    header("Location: manage_managers.php?success=2");
    exit();
}

// Get pending managers
$pending_managers = mysqli_query($conn, "SELECT * FROM managers WHERE status='pending' ORDER BY created_at DESC");
$pending_count = mysqli_num_rows($pending_managers);

// Get all managers
$all_managers = mysqli_query($conn, "SELECT * FROM managers ORDER BY is_super_admin DESC, created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Managers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .manager-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #1e3c72;
        }
        .manager-card.pending {
            border-left-color: #ffc107;
            background: #fffef0;
        }
        .manager-card.super-admin {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container" style="max-width: 1200px; margin: 40px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>👥 Manage Managers</h2>
        <a href="index.php" style="padding: 10px 20px; background: #6c757d; color: white; border-radius: 20px; text-decoration: none;">
            ← Back to Dashboard
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php if ($_GET['success'] == 1): ?>
                ✅ Manager approved successfully!
            <?php else: ?>
                ✅ Manager rejected successfully!
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Pending Managers -->
    <h3>🔔 Pending Manager Registrations (<?= $pending_count ?>)</h3>
    <?php if ($pending_count > 0): ?>
        <?php mysqli_data_seek($pending_managers, 0); while ($manager = mysqli_fetch_assoc($pending_managers)): ?>
        <div class="manager-card pending">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div>
                    <h4 style="margin: 0 0 10px 0;"><?= htmlspecialchars($manager['full_name'] ?? $manager['username']) ?></h4>
                    <p style="margin: 5px 0; color: #666;">
                        <strong>Username:</strong> <?= htmlspecialchars($manager['username']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($manager['email'] ?? 'N/A') ?><br>
                        <strong>Registered:</strong> <?= date('M d, Y H:i', strtotime($manager['created_at'])) ?>
                    </p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="?approve=<?= $manager['id'] ?>" 
                       style="padding: 10px 20px; background: #28a745; color: white; border-radius: 20px; text-decoration: none;"
                       onclick="return confirm('Approve this manager?')">
                        ✅ Approve
                    </a>
                    <a href="?reject=<?= $manager['id'] ?>" 
                       style="padding: 10px 20px; background: #dc3545; color: white; border-radius: 20px; text-decoration: none;"
                       onclick="return confirm('Reject this manager?')">
                        ❌ Reject
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #999; background: #f8f9fa; border-radius: 10px;">
            No pending manager registrations
        </p>
    <?php endif; ?>

    <!-- All Managers -->
    <h3 style="margin-top: 40px;">👥 All Managers</h3>
    <?php while ($manager = mysqli_fetch_assoc($all_managers)): ?>
    <div class="manager-card <?= $manager['is_super_admin'] ? 'super-admin' : '' ?>">
        <div style="display: flex; justify-content: space-between; align-items: start;">
            <div>
                <h4 style="margin: 0 0 10px 0;">
                    <?= htmlspecialchars($manager['full_name'] ?? $manager['username']) ?>
                    <?php if ($manager['is_super_admin']): ?>
                        <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; margin-left: 10px;">
                            👑 SUPER ADMIN
                        </span>
                    <?php endif; ?>
                </h4>
                <p style="margin: 5px 0; color: #666;">
                    <strong>Username:</strong> <?= htmlspecialchars($manager['username']) ?><br>
                    <strong>Email:</strong> <?= htmlspecialchars($manager['email'] ?? 'N/A') ?><br>
                    <strong>Joined:</strong> <?= date('M d, Y', strtotime($manager['created_at'])) ?>
                </p>
            </div>
            <div>
                <span class="status-badge status-<?= $manager['status'] ?>">
                    <?= ucfirst($manager['status']) ?>
                </span>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</body>
</html>
