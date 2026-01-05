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

// Student requests return
if (isset($_POST['request_return']) && $role == 'student') {
    $borrow_id = $_POST['borrow_id'];
    mysqli_query($conn, "UPDATE borrow_requests SET return_requested=TRUE, return_request_date=NOW() WHERE id=$borrow_id AND student_id=$user_id");
    
    // Create notification for manager
    mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, title, message, type, link) 
                         SELECT 1, 'manager', 'Return Request', CONCAT('Student ', username, ' requested to return a book'), 'info', 'book_returns.php'
                         FROM students WHERE id=$user_id");
    
    $success = "✅ Return request submitted! Waiting for manager approval.";
}

// Manager approves return
if (isset($_GET['approve_return']) && $role == 'manager') {
    $borrow_id = $_GET['approve_return'];
    
    $borrow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM borrow_requests WHERE id=$borrow_id"));
    $book_id = $borrow['book_id'];
    $student_id = $borrow['student_id'];
    
    // Update borrow request
    mysqli_query($conn, "UPDATE borrow_requests SET status='returned', actual_return_date=NOW() WHERE id=$borrow_id");
    
    // Restore book quantity
    mysqli_query($conn, "UPDATE books SET quantity = quantity + 1 WHERE id=$book_id");
    
    // Calculate late fee if overdue
    $return_date = strtotime($borrow['return_date']);
    $today = time();
    $days_late = max(0, floor(($today - $return_date) / (60 * 60 * 24)));
    
    if ($days_late > 0) {
        $late_fee = $days_late * 1.00; // $1 per day
        mysqli_query($conn, "INSERT INTO charges (student_id, amount, charge_type, description, is_late_fee, late_fee_days) 
                             VALUES ($student_id, $late_fee, 'late_fee', 'Late fee for $days_late day(s) overdue', TRUE, $days_late)");
    }
    
    // Notify student
    mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, title, message, type) 
                         VALUES ($student_id, 'student', 'Return Approved', 'Your book return has been approved', 'success')");
    
    // Check for reservations
    $reservation = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM book_reservations WHERE book_id=$book_id AND status='active' ORDER BY reserved_at ASC LIMIT 1"));
    if ($reservation) {
        mysqli_query($conn, "UPDATE book_reservations SET status='notified', notified_at=NOW(), expires_at=DATE_ADD(NOW(), INTERVAL 48 HOUR) WHERE id={$reservation['id']}");
        mysqli_query($conn, "INSERT INTO notifications (user_id, user_role, title, message, type, link) 
                             VALUES ({$reservation['student_id']}, 'student', 'Book Available', 'Your reserved book is now available!', 'success', 'reservations.php')");
    }
    
    $success = "✅ Return approved successfully!";
}

// Get data based on role
if ($role == 'manager') {
    $return_requests_query = mysqli_query($conn, "SELECT br.*, s.username, s.full_name, b.book_name, b.author 
                                            FROM borrow_requests br 
                                            JOIN students s ON br.student_id = s.id 
                                            JOIN books b ON br.book_id = b.id 
                                            WHERE br.return_requested=TRUE AND br.status='approved' 
                                            ORDER BY br.return_request_date DESC");
    
    if (!$return_requests_query) {
        die("Database error: " . mysqli_error($conn) . "<br><br><strong>Please run <a href='quick_setup_returns.php'>quick_setup_returns.php</a> first!</strong>");
    }
    
    $return_requests = $return_requests_query;
    
    $active_borrows = mysqli_query($conn, "SELECT br.*, s.username, s.full_name, b.book_name 
                                           FROM borrow_requests br 
                                           JOIN students s ON br.student_id = s.id 
                                           JOIN books b ON br.book_id = b.id 
                                           WHERE br.status='approved' AND br.actual_return_date IS NULL 
                                           ORDER BY br.return_date ASC");
} else {
    $my_borrows_query = mysqli_query($conn, "SELECT br.*, b.book_name, b.author, b.category 
                                       FROM borrow_requests br 
                                       JOIN books b ON br.book_id = b.id 
                                       WHERE br.student_id=$user_id AND br.status='approved' AND br.actual_return_date IS NULL 
                                       ORDER BY br.return_date ASC");
    
    if (!$my_borrows_query) {
        die("Database error: " . mysqli_error($conn) . "<br><br><strong>Please run <a href='quick_setup_returns.php'>quick_setup_returns.php</a> first!</strong>");
    }
    
    $my_borrows = $my_borrows_query;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Returns</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .return-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #1e3c72;
        }
        .return-card.requested {
            border-left-color: #ffc107;
            background: #fffef0;
        }
        .return-card.overdue {
            border-left-color: #dc3545;
            background: #ffebee;
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 1200px; margin: 40px auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>📦 Book Returns</h2>
        <a href="<?= $role == 'manager' ? 'index.php' : 'student_dashboard.php' ?>" 
           style="padding: 10px 20px; background: #6c757d; color: white; border-radius: 20px; text-decoration: none;">
            ← Back to Dashboard
        </a>
    </div>

    <?php if ($success): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if ($role == 'manager'): ?>
        <!-- Manager View -->
        <h3>🔔 Pending Return Requests</h3>
        <?php if (mysqli_num_rows($return_requests) > 0): ?>
            <?php while ($req = mysqli_fetch_assoc($return_requests)): 
                $days_late = max(0, floor((time() - strtotime($req['return_date'])) / (60 * 60 * 24)));
            ?>
            <div class="return-card requested">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h4 style="margin: 0 0 10px 0;"><?= htmlspecialchars($req['book_name']) ?></h4>
                        <p style="margin: 5px 0; color: #666;">
                            <strong>Student:</strong> <?= htmlspecialchars($req['full_name'] ?? $req['username']) ?><br>
                            <strong>Author:</strong> <?= htmlspecialchars($req['author']) ?><br>
                            <strong>Borrowed:</strong> <?= date('M d, Y', strtotime($req['borrow_date'])) ?><br>
                            <strong>Due Date:</strong> <?= date('M d, Y', strtotime($req['return_date'])) ?>
                            <?php if ($days_late > 0): ?>
                                <span style="color: #dc3545; font-weight: bold;"> (<?= $days_late ?> days late)</span>
                            <?php endif; ?>
                        </p>
                        <small style="color: #999;">Requested: <?= date('M d, Y H:i', strtotime($req['return_request_date'])) ?></small>
                    </div>
                    <div>
                        <a href="?approve_return=<?= $req['id'] ?>" 
                           style="padding: 10px 20px; background: #28a745; color: white; border-radius: 20px; text-decoration: none; display: inline-block;"
                           onclick="return confirm('Approve this return?')">
                            ✅ Approve Return
                        </a>
                        <?php if ($days_late > 0): ?>
                            <div style="margin-top: 10px; padding: 8px; background: #fff3cd; border-radius: 5px; font-size: 12px;">
                                ⚠️ Late fee: $<?= number_format($days_late * 1.00, 2) ?> will be charged
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #999; background: #f8f9fa; border-radius: 10px;">
                No pending return requests
            </p>
        <?php endif; ?>

    <?php else: ?>
        <!-- Student View -->
        <h3>📚 My Active Borrows</h3>
        <?php if (mysqli_num_rows($my_borrows) > 0): ?>
            <?php while ($borrow = mysqli_fetch_assoc($my_borrows)): 
                $days_left = floor((strtotime($borrow['return_date']) - time()) / (60 * 60 * 24));
                $is_overdue = $days_left < 0;
            ?>
            <div class="return-card <?= $is_overdue ? 'overdue' : '' ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 10px 0;"><?= htmlspecialchars($borrow['book_name']) ?></h4>
                        <p style="margin: 5px 0; color: #666;">
                            <strong>Author:</strong> <?= htmlspecialchars($borrow['author']) ?><br>
                            <strong>Category:</strong> <?= htmlspecialchars($borrow['category']) ?><br>
                            <strong>Borrowed:</strong> <?= date('M d, Y', strtotime($borrow['borrow_date'])) ?><br>
                            <strong>Due Date:</strong> <?= date('M d, Y', strtotime($borrow['return_date'])) ?>
                        </p>
                        <?php if ($is_overdue): ?>
                            <div style="margin-top: 10px; padding: 10px; background: #dc3545; color: white; border-radius: 5px;">
                                ⚠️ <strong>OVERDUE</strong> by <?= abs($days_left) ?> day(s) - Late fee: $<?= number_format(abs($days_left) * 1.00, 2) ?>
                            </div>
                        <?php elseif ($days_left <= 3): ?>
                            <div style="margin-top: 10px; padding: 10px; background: #ffc107; color: #000; border-radius: 5px;">
                                ⏰ Due in <?= $days_left ?> day(s)
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 10px; color: #28a745;">
                                ✓ <?= $days_left ?> day(s) remaining
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <?php if ($borrow['return_requested']): ?>
                            <div style="padding: 10px 20px; background: #ffc107; color: #000; border-radius: 20px; font-weight: bold;">
                                ⏳ Return Pending
                            </div>
                            <small style="display: block; margin-top: 5px; color: #666;">
                                Requested: <?= date('M d, Y', strtotime($borrow['return_request_date'])) ?>
                            </small>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="borrow_id" value="<?= $borrow['id'] ?>">
                                <button type="submit" name="request_return" 
                                        style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 20px; cursor: pointer; font-weight: bold;"
                                        onclick="return confirm('Request to return this book?')">
                                    📦 Request Return
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #999; background: #f8f9fa; border-radius: 10px;">
                You don't have any active borrows
            </p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
