<?php
session_start();

// Check if user is logged in as manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit();
}

include "db.php";

// Mark charge as paid
if (isset($_GET['mark_paid'])) {
    $charge_id = $_GET['mark_paid'];
    $payment_method = $_GET['method'] ?? 'cash';
    $payment_date = date('Y-m-d H:i:s');
    
    mysqli_query($conn, "UPDATE charges SET status='paid', payment_method='$payment_method', payment_date='$payment_date' WHERE id=$charge_id");
    header("Location: manage_payments.php?success=1");
    exit();
}

// Mark charge as unpaid
if (isset($_GET['mark_unpaid'])) {
    $charge_id = $_GET['mark_unpaid'];
    mysqli_query($conn, "UPDATE charges SET status='pending', payment_method=NULL, payment_date=NULL WHERE id=$charge_id");
    header("Location: manage_payments.php?success=2");
    exit();
}

// Get all charges with student info
$all_charges = mysqli_query($conn, "SELECT c.*, s.username, s.full_name, s.email, s.phone FROM charges c JOIN students s ON c.student_id = s.id ORDER BY c.created_at DESC");

// Get statistics
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM charges WHERE status='paid'"))['total'] ?? 0;
$pending_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM charges WHERE status='pending'"))['total'] ?? 0;
$total_charges = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM charges"))['count'];
$paid_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM charges WHERE status='paid'"))['count'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM charges WHERE status='pending'"))['count'];

// Get payment method breakdown
$payment_methods = mysqli_query($conn, "SELECT payment_method, COUNT(*) as count, SUM(amount) as total FROM charges WHERE status='paid' AND payment_method IS NOT NULL GROUP BY payment_method");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            max-width: 1200px;
            margin: 40px auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            padding: 20px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .charge-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #1e3c72;
        }
        .charge-card.paid {
            border-left-color: #28a745;
            background: #f0fff4;
        }
        .charge-card.pending {
            border-left-color: #ffc107;
            background: #fffef0;
        }
        .payment-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            font-size: 14px;
        }
        .btn-cash { background: #28a745; color: white; }
        .btn-card { background: #007bff; color: white; }
        .btn-online { background: #17a2b8; color: white; }
        .btn-unpaid { background: #dc3545; color: white; }
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .filter-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: bold;
            color: #666;
        }
        .filter-tab.active {
            color: #1e3c72;
            border-bottom-color: #1e3c72;
        }
    </style>
</head>
<body>
<div class="container payment-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>💰 Payment Management</h2>
        <a href="index.php" style="padding: 10px 20px; background: #6c757d; color: white; border-radius: 20px; text-decoration: none;">← Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php if ($_GET['success'] == 1): ?>
                ✅ Payment marked as paid successfully!
            <?php else: ?>
                ✅ Payment marked as unpaid successfully!
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div style="font-size: 14px; opacity: 0.9;">💵 Total Revenue</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;">$<?= number_format($total_revenue, 2) ?></div>
            <div style="font-size: 12px; opacity: 0.8;"><?= $paid_count ?> paid charges</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
            <div style="font-size: 14px; opacity: 0.9;">⏳ Pending</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;">$<?= number_format($pending_revenue, 2) ?></div>
            <div style="font-size: 12px; opacity: 0.8;"><?= $pending_count ?> pending charges</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <div style="font-size: 14px; opacity: 0.9;">📊 Total Charges</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $total_charges ?></div>
            <div style="font-size: 12px; opacity: 0.8;">All time</div>
        </div>
        
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <div style="font-size: 14px; opacity: 0.9;">💳 Collection Rate</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;">
                <?= $total_charges > 0 ? round(($paid_count / $total_charges) * 100) : 0 ?>%
            </div>
            <div style="font-size: 12px; opacity: 0.8;">Paid vs Total</div>
        </div>
    </div>

    <!-- Payment Methods Breakdown -->
    <?php if (mysqli_num_rows($payment_methods) > 0): ?>
    <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>💳 Payment Methods Breakdown</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
            <?php while ($method = mysqli_fetch_assoc($payment_methods)): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 5px;">
                    <?php 
                    echo $method['payment_method'] == 'cash' ? '💵' : 
                         ($method['payment_method'] == 'card' ? '💳' : '🌐');
                    ?>
                </div>
                <div style="font-weight: bold; text-transform: capitalize;"><?= $method['payment_method'] ?></div>
                <div style="color: #28a745; font-size: 20px; font-weight: bold; margin: 5px 0;">
                    $<?= number_format($method['total'], 2) ?>
                </div>
                <div style="font-size: 12px; color: #666;"><?= $method['count'] ?> transactions</div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <button class="filter-tab active" onclick="filterCharges('all')">All Charges</button>
        <button class="filter-tab" onclick="filterCharges('pending')">Pending (<?= $pending_count ?>)</button>
        <button class="filter-tab" onclick="filterCharges('paid')">Paid (<?= $paid_count ?>)</button>
    </div>

    <!-- Charges List -->
    <div id="chargesList">
        <?php while ($charge = mysqli_fetch_assoc($all_charges)): ?>
        <div class="charge-card <?= $charge['status'] ?>" data-status="<?= $charge['status'] ?>">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <h4 style="margin: 0;"><?= htmlspecialchars($charge['full_name'] ?? $charge['username']) ?></h4>
                        <span style="background: <?= $charge['status'] == 'paid' ? '#28a745' : '#ffc107' ?>; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            <?= ucfirst($charge['status']) ?>
                        </span>
                    </div>
                    
                    <div style="color: #666; margin-bottom: 10px;">
                        <strong><?= ucfirst(str_replace('_', ' ', $charge['charge_type'])) ?></strong> - 
                        <?= htmlspecialchars($charge['description']) ?>
                    </div>
                    
                    <div style="font-size: 14px; color: #999;">
                        <span>📅 <?= date('M d, Y', strtotime($charge['created_at'])) ?></span>
                        <?php if ($charge['status'] == 'paid' && $charge['payment_date']): ?>
                            <span style="margin-left: 15px;">💳 Paid: <?= date('M d, Y', strtotime($charge['payment_date'])) ?></span>
                            <?php if ($charge['payment_method']): ?>
                                <span style="margin-left: 10px; background: #e3f2fd; padding: 2px 8px; border-radius: 10px;">
                                    <?= ucfirst($charge['payment_method']) ?>
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($charge['email'] || $charge['phone']): ?>
                    <div style="font-size: 12px; color: #666; margin-top: 8px;">
                        <?php if ($charge['email']): ?>
                            <span>📧 <?= htmlspecialchars($charge['email']) ?></span>
                        <?php endif; ?>
                        <?php if ($charge['phone']): ?>
                            <span style="margin-left: 15px;">📱 <?= htmlspecialchars($charge['phone']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="text-align: right; margin-left: 20px;">
                    <div style="font-size: 28px; font-weight: bold; color: #1e3c72; margin-bottom: 10px;">
                        $<?= number_format($charge['amount'], 2) ?>
                    </div>
                    
                    <?php if ($charge['status'] == 'pending'): ?>
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <a href="?mark_paid=<?= $charge['id'] ?>&method=cash" class="payment-btn btn-cash" onclick="return confirm('Mark as paid via Cash?')">💵 Cash</a>
                            <a href="?mark_paid=<?= $charge['id'] ?>&method=card" class="payment-btn btn-card" onclick="return confirm('Mark as paid via Card?')">💳 Card</a>
                            <a href="?mark_paid=<?= $charge['id'] ?>&method=online" class="payment-btn btn-online" onclick="return confirm('Mark as paid via Online?')">🌐 Online</a>
                        </div>
                    <?php else: ?>
                        <a href="?mark_unpaid=<?= $charge['id'] ?>" class="payment-btn btn-unpaid" onclick="return confirm('Mark as unpaid?')">↩️ Undo</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function filterCharges(status) {
    const cards = document.querySelectorAll('.charge-card');
    const tabs = document.querySelectorAll('.filter-tab');
    
    // Update active tab
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter cards
    cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
