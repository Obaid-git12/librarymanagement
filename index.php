<?php
session_start();

// Check if user is logged in as manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit();
}

include "db.php"; // Connect to database

/* Add book */
if (isset($_POST['add'])) {
    mysqli_query($conn,
        "INSERT INTO books (book_name, author, category, year, quantity, weekly_price, monthly_price)
         VALUES (
            '{$_POST['book']}',
            '{$_POST['author']}',
            '{$_POST['category']}',
            '{$_POST['year']}',
            '{$_POST['quantity']}',
            '{$_POST['weekly_price']}',
            '{$_POST['monthly_price']}'
         )"
    );
    header("Location: index.php?success=1");
    exit();
}

/* Update book */
if (isset($_POST['update'])) {
    mysqli_query($conn,
        "UPDATE books SET
         book_name='{$_POST['book']}',
         author='{$_POST['author']}',
         category='{$_POST['category']}',
         year='{$_POST['year']}',
         quantity='{$_POST['quantity']}',
         weekly_price='{$_POST['weekly_price']}',
         monthly_price='{$_POST['monthly_price']}'
         WHERE id={$_POST['id']}"
    );
    header("Location: index.php?success=1");
    exit();
}

/* Fetch for edit */
$edit = null;
if (isset($_GET['edit'])) {
    $res = mysqli_query($conn, "SELECT * FROM books WHERE id={$_GET['edit']}");
    $edit = mysqli_fetch_assoc($res);
}

/* Search */
$search = $_GET['search'] ?? "";
$category_filter = $_GET['category'] ?? "";
$availability_filter = $_GET['availability'] ?? "";
$sort_by = $_GET['sort'] ?? "book_name";

$where_conditions = [];
if ($search) {
    $where_conditions[] = "(book_name LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%')";
}
if ($category_filter) {
    $where_conditions[] = "category = '$category_filter'";
}
if ($availability_filter == 'available') {
    $where_conditions[] = "quantity > 0";
} elseif ($availability_filter == 'unavailable') {
    $where_conditions[] = "quantity = 0";
}

$where_clause = "";
if (count($where_conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Valid sort columns
$valid_sorts = ['book_name', 'author', 'category', 'year', 'weekly_price', 'monthly_price', 'quantity'];
if (!in_array($sort_by, $valid_sorts)) {
    $sort_by = 'book_name';
}

$books = mysqli_query($conn, "SELECT * FROM books $where_clause ORDER BY $sort_by ASC");

// Get all categories for filter
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM books ORDER BY category");

// Get pending student registrations
$pending_students = mysqli_query($conn, "SELECT * FROM students WHERE status='pending' ORDER BY created_at DESC");
$pending_count = mysqli_num_rows($pending_students);

// Get pending borrow requests
$pending_borrows = mysqli_query($conn, "SELECT br.*, s.username, b.book_name, br.borrow_duration FROM borrow_requests br JOIN students s ON br.student_id = s.id JOIN books b ON br.book_id = b.id WHERE br.status='pending' ORDER BY br.created_at DESC");
$borrow_count = mysqli_num_rows($pending_borrows);

// Get all active borrows (approved and not returned)
$active_borrows = mysqli_query($conn, "SELECT br.*, s.username, s.full_name, s.email, s.phone, b.book_name, b.author, b.category FROM borrow_requests br JOIN students s ON br.student_id = s.id JOIN books b ON br.book_id = b.id WHERE br.status='approved' AND br.actual_return_date IS NULL ORDER BY br.return_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Management System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .notification-badge {
            background: #d32f2f;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #1e3c72;
            border-bottom-color: #1e3c72;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
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
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">📚 Library Management System</h2>
        <div>
            <a href="profile.php" style="margin-right: 15px; padding: 8px 16px; background: #17a2b8; color: white; border-radius: 20px; text-decoration: none;">👤 Profile</a>
            <span style="color: #666;">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (Manager)</span>
            <a href="logout.php" style="margin-left: 15px; padding: 8px 16px; background: #d32f2f; color: white; border-radius: 20px; text-decoration: none;">Logout</a>
        </div>
    </div>

    <!-- Dashboard Statistics -->
    <?php
    $total_books = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM books"))['count'];
    $total_quantity = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(quantity) as total FROM books"))['total'] ?? 0;
    $borrowed_books = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM borrow_requests WHERE status='approved' AND actual_return_date IS NULL"))['count'];
    $pending_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE status='pending'"))['count'];
    $pending_borrows = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM borrow_requests WHERE status='pending'"))['count'];
    $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM charges WHERE status='paid'"))['total'] ?? 0;
    $pending_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM charges WHERE status='pending'"))['total'] ?? 0;
    $total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE status='approved'"))['count'];
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">📚 Total Books</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $total_books ?></div>
            <div style="font-size: 12px; opacity: 0.8;">Total Copies: <?= $total_quantity ?></div>
        </div>
        
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">📖 Currently Borrowed</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $borrowed_books ?></div>
            <div style="font-size: 12px; opacity: 0.8;">Active Loans</div>
        </div>
        
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">👥 Active Students</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $total_students ?></div>
            <div style="font-size: 12px; opacity: 0.8;">Approved Members</div>
        </div>
        
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">💰 Total Revenue</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;">$<?= number_format($total_revenue, 2) ?></div>
            <div style="font-size: 12px; opacity: 0.8;">Pending: $<?= number_format($pending_revenue, 2) ?></div>
        </div>
    </div>

    <?php if ($pending_students > 0 || $pending_borrows > 0): ?>
    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <strong>⚠️ Pending Actions:</strong>
        <?php if ($pending_students > 0): ?>
            <span style="margin-left: 10px;">👥 <?= $pending_students ?> student approval(s)</span>
        <?php endif; ?>
        <?php if ($pending_borrows > 0): ?>
            <span style="margin-left: 10px;">📋 <?= $pending_borrows ?> borrow request(s)</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('books')">📚 Manage Books</button>
        <button class="tab-btn" onclick="showTab('students')">
            👥 Student Approvals
            <?php if ($pending_count > 0): ?>
                <span class="notification-badge"><?= $pending_count ?></span>
            <?php endif; ?>
        </button>
        <?php if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']): ?>
        <button class="tab-btn" onclick="window.location.href='manage_managers.php'">
            👔 Manager Approvals
            <?php 
            $pending_managers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM managers WHERE status='pending'"))['count'] ?? 0;
            if ($pending_managers > 0): 
            ?>
                <span class="notification-badge"><?= $pending_managers ?></span>
            <?php endif; ?>
        </button>
        <?php endif; ?>
        <button class="tab-btn" onclick="showTab('borrows')">
            📋 Borrow Requests
            <?php if ($borrow_count > 0): ?>
                <span class="notification-badge"><?= $borrow_count ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" onclick="showTab('active-borrows')">
            📖 Active Borrows
        </button>
        <button class="tab-btn" onclick="window.location.href='book_returns.php'">
            📦 Returns
            <?php 
            $return_requests_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM borrow_requests WHERE return_requested=TRUE AND status='approved'"))['count'] ?? 0;
            if ($return_requests_count > 0): 
            ?>
                <span class="notification-badge"><?= $return_requests_count ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" onclick="window.location.href='manage_payments.php'">
            💰 Payments
            <?php 
            $pending_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM charges WHERE status='pending'"))['count'];
            if ($pending_payments > 0): 
            ?>
                <span class="notification-badge"><?= $pending_payments ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Books Tab -->
    <div id="books" class="tab-content active">
    
    <!-- Advanced Search & Filters -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
        <form method="get" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">Search</label>
                <input type="text" name="search" placeholder="Search by book, author, or category..." value="<?= htmlspecialchars($search) ?>" style="width: 100%;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">Category</label>
                <select name="category" style="width: 100%; padding: 10px;">
                    <option value="">All Categories</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                        <option value="<?= $cat['category'] ?>" <?= $category_filter == $cat['category'] ? 'selected' : '' ?>>
                            <?= $cat['category'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">Availability</label>
                <select name="availability" style="width: 100%; padding: 10px;">
                    <option value="">All Books</option>
                    <option value="available" <?= $availability_filter == 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="unavailable" <?= $availability_filter == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">Sort By</label>
                <select name="sort" style="width: 100%; padding: 10px;">
                    <option value="book_name" <?= $sort_by == 'book_name' ? 'selected' : '' ?>>Book Name</option>
                    <option value="author" <?= $sort_by == 'author' ? 'selected' : '' ?>>Author</option>
                    <option value="category" <?= $sort_by == 'category' ? 'selected' : '' ?>>Category</option>
                    <option value="year" <?= $sort_by == 'year' ? 'selected' : '' ?>>Year</option>
                    <option value="weekly_price" <?= $sort_by == 'weekly_price' ? 'selected' : '' ?>>Weekly Price</option>
                    <option value="monthly_price" <?= $sort_by == 'monthly_price' ? 'selected' : '' ?>>Monthly Price</option>
                    <option value="quantity" <?= $sort_by == 'quantity' ? 'selected' : '' ?>>Quantity</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 5px;">
                <button type="submit" style="padding: 10px 20px; white-space: nowrap;">🔍 Search</button>
                <a href="index.php" style="padding: 10px 15px; background: #6c757d; color: white; border-radius: 25px; text-decoration: none; display: inline-block;">Clear</a>
            </div>
        </form>
        
        <?php if ($search || $category_filter || $availability_filter): ?>
            <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
                <strong>Filters Active:</strong>
                <?php if ($search): ?>
                    <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px;">Search: "<?= htmlspecialchars($search) ?>"</span>
                <?php endif; ?>
                <?php if ($category_filter): ?>
                    <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px;">Category: <?= $category_filter ?></span>
                <?php endif; ?>
                <?php if ($availability_filter): ?>
                    <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px;">Status: <?= ucfirst($availability_filter) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Search -->
    <form method="get">
        <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
        <?php if ($edit) echo '<a href="index.php">Cancel Edit</a>'; ?>
    </form>

    <!-- Add/Edit Form -->
    <form method="post">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="text" name="book" placeholder="Book Name" required value="<?= $edit['book_name'] ?? '' ?>">
        <input type="text" name="author" placeholder="Author Name" required value="<?= $edit['author'] ?? '' ?>">
        <input type="text" name="category" placeholder="Category" required value="<?= $edit['category'] ?? '' ?>">
        <input type="number" name="year" placeholder="Year" required value="<?= $edit['year'] ?? '' ?>">
        <input type="number" name="quantity" placeholder="Quantity" required value="<?= $edit['quantity'] ?? '' ?>">
        <input type="number" step="0.01" name="weekly_price" placeholder="Weekly Price ($)" required value="<?= $edit['weekly_price'] ?? '5.00' ?>">
        <input type="number" step="0.01" name="monthly_price" placeholder="Monthly Price ($)" required value="<?= $edit['monthly_price'] ?? '15.00' ?>">
        <?php if ($edit) { ?>
            <button type="submit" name="update">Update Book</button>
        <?php } else { ?>
            <button type="submit" name="add">Add Book</button>
        <?php } ?>
    </form>

    <!-- Book Table -->
    <table>
        <tr>
            <th>ID</th><th>Book</th><th>Author</th><th>Category</th><th>Year</th><th>Qty</th><th>Weekly</th><th>Monthly</th><th>Action</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($books)) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['book_name'] ?></td>
            <td><?= $row['author'] ?></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['year'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>$<?= number_format($row['weekly_price'], 2) ?></td>
            <td>$<?= number_format($row['monthly_price'], 2) ?></td>
            <td><a href="?edit=<?= $row['id'] ?>">Edit</a></td>
        </tr>
        <?php } ?>
    </table>
    </div>

    <!-- Students Tab -->
    <div id="students" class="tab-content">
        <h3>Pending Student Registrations</h3>
        <?php if ($pending_count > 0): ?>
        <table>
            <tr>
                <th>Full Name</th><th>Username</th><th>Email</th><th>Registered</th><th>Status</th><th>Action</th>
            </tr>
            <?php mysqli_data_seek($pending_students, 0); while ($row = mysqli_fetch_assoc($pending_students)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                <td><span class="status-badge status-pending">Pending</span></td>
                <td>
                    <a href="manage_students.php?approve=<?= $row['id'] ?>" style="background: #4caf50; padding: 6px 12px; color: white; border-radius: 15px; margin-right: 5px;">Approve</a>
                    <a href="manage_students.php?reject=<?= $row['id'] ?>" style="background: #f44336; padding: 6px 12px; color: white; border-radius: 15px;">Reject</a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php else: ?>
        <p style="text-align: center; color: #999; padding: 40px;">No pending student registrations.</p>
        <?php endif; ?>
    </div>

    <!-- Borrows Tab -->
    <div id="borrows" class="tab-content">
        <h3>Pending Borrow Requests</h3>
        <?php if ($borrow_count > 0): ?>
        <table>
            <tr>
                <th>Student</th><th>Book</th><th>Duration</th><th>Requested</th><th>Status</th><th>Action</th>
            </tr>
            <?php mysqli_data_seek($pending_borrows, 0); while ($row = mysqli_fetch_assoc($pending_borrows)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['book_name']) ?></td>
                <td><span style="background: #e3f2fd; padding: 4px 8px; border-radius: 5px;"><?= ucfirst($row['borrow_duration']) ?></span></td>
                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                <td><span class="status-badge status-pending">Pending</span></td>
                <td>
                    <a href="manage_borrows.php?approve=<?= $row['id'] ?>" style="background: #4caf50; padding: 6px 12px; color: white; border-radius: 15px; margin-right: 5px;">Approve</a>
                    <a href="manage_borrows.php?reject=<?= $row['id'] ?>" style="background: #f44336; padding: 6px 12px; color: white; border-radius: 15px;">Reject</a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php else: ?>
        <p style="text-align: center; color: #999; padding: 40px;">No pending borrow requests.</p>
        <?php endif; ?>
    </div>

    <!-- Active Borrows Tab -->
    <div id="active-borrows" class="tab-content">
        <h3>📖 Currently Borrowed Books</h3>
        
        <?php if (mysqli_num_rows($active_borrows) > 0): ?>
        <div style="margin-bottom: 15px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
            <strong>ℹ️ Showing all books currently borrowed by students</strong>
        </div>
        
        <table>
            <tr>
                <th>Student</th>
                <th>Contact</th>
                <th>Book</th>
                <th>Author</th>
                <th>Duration</th>
                <th>Borrowed</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($active_borrows)) { 
                $due_date = strtotime($row['return_date']);
                $today = strtotime(date('Y-m-d'));
                $days_left = floor(($due_date - $today) / (60 * 60 * 24));
                $is_overdue = $days_left < 0;
                $is_due_soon = $days_left >= 0 && $days_left <= 3;
            ?>
            <tr style="<?= $is_overdue ? 'background: #ffebee;' : ($is_due_soon ? 'background: #fff3cd;' : '') ?>">
                <td>
                    <strong><?= htmlspecialchars($row['full_name'] ?? $row['username']) ?></strong>
                    <br><small style="color: #666;">@<?= htmlspecialchars($row['username']) ?></small>
                </td>
                <td>
                    <?php if ($row['email']): ?>
                        <div style="font-size: 12px;">
                            📧 <?= htmlspecialchars($row['email']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($row['phone']): ?>
                        <div style="font-size: 12px;">
                            📱 <?= htmlspecialchars($row['phone']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!$row['email'] && !$row['phone']): ?>
                        <span style="color: #999; font-size: 12px;">No contact info</span>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($row['book_name']) ?></strong>
                    <br><small style="color: #666;"><?= htmlspecialchars($row['category']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td>
                    <span style="background: #e3f2fd; padding: 4px 8px; border-radius: 5px; font-size: 12px;">
                        <?= ucfirst($row['borrow_duration']) ?>
                    </span>
                </td>
                <td><?= date('M d, Y', strtotime($row['borrow_date'])) ?></td>
                <td>
                    <strong><?= date('M d, Y', strtotime($row['return_date'])) ?></strong>
                    <?php if ($is_overdue): ?>
                        <br><span style="color: #d32f2f; font-size: 12px; font-weight: bold;">
                            ⚠️ <?= abs($days_left) ?> day(s) overdue
                        </span>
                    <?php elseif ($is_due_soon): ?>
                        <br><span style="color: #ff9800; font-size: 12px; font-weight: bold;">
                            ⏰ Due in <?= $days_left ?> day(s)
                        </span>
                    <?php else: ?>
                        <br><span style="color: #4caf50; font-size: 12px;">
                            ✓ <?= $days_left ?> day(s) left
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($is_overdue): ?>
                        <span class="status-badge" style="background: #f8d7da; color: #721c24;">Overdue</span>
                    <?php elseif ($is_due_soon): ?>
                        <span class="status-badge" style="background: #fff3cd; color: #856404;">Due Soon</span>
                    <?php else: ?>
                        <span class="status-badge status-approved">Active</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h4 style="margin: 0 0 10px 0;">📊 Summary</h4>
            <?php
            mysqli_data_seek($active_borrows, 0);
            $total_active = 0;
            $overdue_count = 0;
            $due_soon_count = 0;
            
            while ($row = mysqli_fetch_assoc($active_borrows)) {
                $total_active++;
                $due_date = strtotime($row['return_date']);
                $today = strtotime(date('Y-m-d'));
                $days_left = floor(($due_date - $today) / (60 * 60 * 24));
                
                if ($days_left < 0) $overdue_count++;
                elseif ($days_left <= 3) $due_soon_count++;
            }
            ?>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <div style="text-align: center; padding: 10px; background: white; border-radius: 5px;">
                    <div style="font-size: 24px; font-weight: bold; color: #1e3c72;"><?= $total_active ?></div>
                    <div style="font-size: 12px; color: #666;">Total Active</div>
                </div>
                <div style="text-align: center; padding: 10px; background: white; border-radius: 5px;">
                    <div style="font-size: 24px; font-weight: bold; color: #d32f2f;"><?= $overdue_count ?></div>
                    <div style="font-size: 12px; color: #666;">Overdue</div>
                </div>
                <div style="text-align: center; padding: 10px; background: white; border-radius: 5px;">
                    <div style="font-size: 24px; font-weight: bold; color: #ff9800;"><?= $due_soon_count ?></div>
                    <div style="font-size: 12px; color: #666;">Due Soon (≤3 days)</div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999; background: #f8f9fa; border-radius: 10px;">
            <h3>📚 No active borrows</h3>
            <p>No books are currently borrowed by students.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", () => {
        const btn = form.querySelector("button[type='submit']");
        if (btn) btn.classList.add("loading");
    });
});
</script>
</body>
</html>
