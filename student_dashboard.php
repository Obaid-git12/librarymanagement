<?php
session_start();

// Check if user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include "db.php";

$student_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle borrow request
if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    $duration = $_POST['duration'];
    
    // Check if book is available
    $book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity FROM books WHERE id=$book_id"));
    if ($book['quantity'] > 0) {
        mysqli_query($conn, "INSERT INTO borrow_requests (student_id, book_id, borrow_duration) VALUES ($student_id, $book_id, '$duration')");
        $success = "Borrow request submitted! Waiting for manager approval.";
    } else {
        $error = "Book is not available!";
    }
}

// Get books
$search = $_GET['search'] ?? "";
$category_filter = $_GET['category'] ?? "";
$price_range = $_GET['price_range'] ?? "";
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
if ($price_range == 'low') {
    $where_conditions[] = "weekly_price < 3.00";
} elseif ($price_range == 'medium') {
    $where_conditions[] = "weekly_price BETWEEN 3.00 AND 5.00";
} elseif ($price_range == 'high') {
    $where_conditions[] = "weekly_price > 5.00";
}

$where_clause = "";
if (count($where_conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
}

// Valid sort columns
$valid_sorts = ['book_name', 'author', 'category', 'year', 'weekly_price', 'monthly_price'];
if (!in_array($sort_by, $valid_sorts)) {
    $sort_by = 'book_name';
}

$books = mysqli_query($conn, "SELECT * FROM books $where_clause ORDER BY $sort_by ASC");

// Get all categories for filter
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM books ORDER BY category");

// Get student's borrow history
$borrows = mysqli_query($conn, "SELECT br.*, b.book_name, b.author FROM borrow_requests br JOIN books b ON br.book_id = b.id WHERE br.student_id = $student_id ORDER BY br.created_at DESC");

// Get student's charges
$charges = mysqli_query($conn, "SELECT * FROM charges WHERE student_id = $student_id ORDER BY created_at DESC");

// Calculate total charges
$total_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM charges WHERE student_id = $student_id AND status = 'pending'"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        .status-returned { background: #d1ecf1; color: #0c5460; }
        .charge-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #1e3c72;
        }
        .total-charges {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">📚 Student Dashboard</h2>
        <div>
            <a href="profile.php" style="margin-right: 15px; padding: 8px 16px; background: #17a2b8; color: white; border-radius: 20px; text-decoration: none;">👤 Profile</a>
            <span style="color: #666;">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            <a href="logout.php" style="margin-left: 15px; padding: 8px 16px; background: #d32f2f; color: white; border-radius: 20px; text-decoration: none;">Logout</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
            <?= $success ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Student Statistics -->
    <?php
    $my_borrows = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM borrow_requests WHERE student_id=$student_id"))['count'];
    $active_borrows = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM borrow_requests WHERE student_id=$student_id AND status='approved' AND actual_return_date IS NULL"))['count'];
    $my_reviews = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM book_reviews WHERE student_id=$student_id"))['count'];
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">📚 Total Borrows</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $my_borrows ?></div>
            <div style="font-size: 12px; opacity: 0.8;">All Time</div>
        </div>
        
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">📖 Active Loans</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $active_borrows ?></div>
            <div style="font-size: 12px; opacity: 0.8;">Currently Borrowed</div>
        </div>
        
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 20px; border-radius: 10px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9;">⭐ My Reviews</div>
            <div style="font-size: 32px; font-weight: bold; margin: 10px 0;"><?= $my_reviews ?></div>
            <div style="font-size: 12px; opacity: 0.8;">Books Reviewed</div>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('books')">📖 Browse Books</button>
        <button class="tab-btn" onclick="showTab('borrows')">📋 My Borrows</button>
        <button class="tab-btn" onclick="window.location.href='book_returns.php'">
            📦 Returns
            <?php 
            $my_active_borrows = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM borrow_requests WHERE student_id=$student_id AND status='approved' AND actual_return_date IS NULL"))['count'] ?? 0;
            if ($my_active_borrows > 0): 
            ?>
                <span class="notification-badge"><?= $my_active_borrows ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" onclick="showTab('charges')">💰 Charges</button>
        <button class="tab-btn" onclick="showTab('reviews')">⭐ My Reviews</button>
    </div>

    <!-- Books Tab -->
    <div id="books" class="tab-content active">
    
        <!-- Advanced Search & Filters -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <form method="get" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 10px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">🔍 Search</label>
                    <input type="text" name="search" placeholder="Search books, authors..." value="<?= htmlspecialchars($search) ?>" style="width: 100%;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">📚 Category</label>
                    <select name="category" style="width: 100%; padding: 10px;">
                        <option value="">All</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories)) { ?>
                            <option value="<?= $cat['category'] ?>" <?= $category_filter == $cat['category'] ? 'selected' : '' ?>>
                                <?= $cat['category'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">💰 Price</label>
                    <select name="price_range" style="width: 100%; padding: 10px;">
                        <option value="">All Prices</option>
                        <option value="low" <?= $price_range == 'low' ? 'selected' : '' ?>>Under $3</option>
                        <option value="medium" <?= $price_range == 'medium' ? 'selected' : '' ?>>$3 - $5</option>
                        <option value="high" <?= $price_range == 'high' ? 'selected' : '' ?>>Over $5</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">📦 Status</label>
                    <select name="availability" style="width: 100%; padding: 10px;">
                        <option value="">All</option>
                        <option value="available" <?= $availability_filter == 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="unavailable" <?= $availability_filter == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #666;">⬇️ Sort</label>
                    <select name="sort" style="width: 100%; padding: 10px;">
                        <option value="book_name" <?= $sort_by == 'book_name' ? 'selected' : '' ?>>Name</option>
                        <option value="author" <?= $sort_by == 'author' ? 'selected' : '' ?>>Author</option>
                        <option value="category" <?= $sort_by == 'category' ? 'selected' : '' ?>>Category</option>
                        <option value="year" <?= $sort_by == 'year' ? 'selected' : '' ?>>Year</option>
                        <option value="weekly_price" <?= $sort_by == 'weekly_price' ? 'selected' : '' ?>>Price (Low)</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 5px;">
                    <button type="submit" style="padding: 10px 20px;">Filter</button>
                    <a href="student_dashboard.php" style="padding: 10px 15px; background: #6c757d; color: white; border-radius: 25px; text-decoration: none;">Clear</a>
                </div>
            </form>
            
            <?php if ($search || $category_filter || $availability_filter || $price_range): ?>
                <div style="margin-top: 15px; padding: 10px; background: #d4edda; border-radius: 5px; border-left: 4px solid #28a745;">
                    <strong>🎯 Active Filters:</strong>
                    <?php if ($search): ?>
                        <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px; display: inline-block;">🔍 "<?= htmlspecialchars($search) ?>"</span>
                    <?php endif; ?>
                    <?php if ($category_filter): ?>
                        <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px; display: inline-block;">📚 <?= $category_filter ?></span>
                    <?php endif; ?>
                    <?php if ($price_range): ?>
                        <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px; display: inline-block;">💰 <?= ucfirst($price_range) ?> Price</span>
                    <?php endif; ?>
                    <?php if ($availability_filter): ?>
                        <span style="background: white; padding: 5px 10px; border-radius: 15px; margin-left: 5px; display: inline-block;">📦 <?= ucfirst($availability_filter) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <form method="get">
            <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <tr>
                <th>ID</th><th>Book</th><th>Author</th><th>Category</th><th>Year</th><th>Available</th><th>Weekly</th><th>Monthly</th><th>Rating</th><th>Action</th>
            </tr>
            <?php 
            $book_count = 0;
            mysqli_data_seek($books, 0); 
            while ($row = mysqli_fetch_assoc($books)) { 
                $book_count++;
                // Get average rating
                $book_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg, COUNT(*) as count FROM book_reviews WHERE book_id={$row['id']}"));
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($row['book_name']) ?></strong>
                    <br><a href="review_book.php?book_id=<?= $row['id'] ?>" style="font-size: 12px; color: #ffc107;">⭐ Reviews</a>
                </td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td><span style="background: #e3f2fd; padding: 4px 8px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars($row['category']) ?></span></td>
                <td><?= $row['year'] ?></td>
                <td>
                    <?php if ($row['quantity'] > 0): ?>
                        <span style="color: green; font-weight: bold;">✓ <?= $row['quantity'] ?></span>
                    <?php else: ?>
                        <span style="color: red; font-weight: bold;">✗ 0</span>
                    <?php endif; ?>
                </td>
                <td><strong style="color: #28a745;">$<?= number_format($row['weekly_price'], 2) ?></strong>/week</td>
                <td><strong style="color: #007bff;">$<?= number_format($row['monthly_price'], 2) ?></strong>/month</td>
                <td>
                    <?php if ($book_rating['count'] > 0): ?>
                        <div style="color: #ffc107; font-size: 14px;">
                            <?php 
                            $avg = round($book_rating['avg']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $avg ? '★' : '☆';
                            }
                            ?>
                            <br><small style="color: #666;">(<?= $book_rating['count'] ?>)</small>
                        </div>
                    <?php else: ?>
                        <span style="color: #999; font-size: 12px;">No reviews</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['quantity'] > 0): ?>
                        <form method="post" style="display: inline-flex; gap: 5px; align-items: center; flex-direction: column;">
                            <input type="hidden" name="book_id" value="<?= $row['id'] ?>">
                            <select name="duration" style="padding: 6px; border-radius: 5px; border: 1px solid #ccc; width: 100%;">
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            <button type="submit" name="borrow" style="padding: 6px 12px; font-size: 12px; width: 100%;">Borrow</button>
                        </form>
                    <?php else: ?>
                        <span style="color: #999;">Not Available</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        
        <?php if ($book_count == 0): ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <h3>📚 No books found</h3>
                <p>Try adjusting your search or filters</p>
                <a href="student_dashboard.php" style="padding: 10px 20px; background: #1e3c72; color: white; border-radius: 20px; text-decoration: none; display: inline-block; margin-top: 10px;">View All Books</a>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 15px; color: #666; background: #f8f9fa; border-radius: 5px; margin-top: 10px;">
                📊 Showing <strong><?= $book_count ?></strong> book(s)
            </div>
        <?php endif; ?>
    </div>

    <!-- Borrows Tab -->
    <div id="borrows" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>My Borrow History</h3>
            <a href="book_returns.php" style="padding: 10px 20px; background: #28a745; color: white; border-radius: 20px; text-decoration: none; font-weight: bold;">
                📦 Request Return
            </a>
        </div>
        <table>
            <tr>
                <th>Book</th><th>Author</th><th>Duration</th><th>Status</th><th>Borrow Date</th><th>Return Date</th><th>Requested</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($borrows)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['book_name']) ?></td>
                <td><?= htmlspecialchars($row['author']) ?></td>
                <td><span style="background: #e3f2fd; padding: 4px 8px; border-radius: 5px;"><?= ucfirst($row['borrow_duration']) ?></span></td>
                <td><span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
                <td><?= $row['borrow_date'] ?? 'N/A' ?></td>
                <td><?= $row['return_date'] ?? 'N/A' ?></td>
                <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Charges Tab -->
    <div id="charges" class="tab-content">
        <div class="total-charges">
            <h3 style="margin: 0 0 10px 0;">Total Pending Charges</h3>
            <h1 style="margin: 0; font-size: 36px;">$<?= number_format($total_pending, 2) ?></h1>
        </div>

        <h3>Charge History</h3>
        <?php if (mysqli_num_rows($charges) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($charges)) { ?>
            <div class="charge-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?= ucfirst(str_replace('_', ' ', $row['charge_type'])) ?></strong>
                        <p style="margin: 5px 0; color: #666;"><?= htmlspecialchars($row['description']) ?></p>
                        <small style="color: #999;">
                            Created: <?= date('M d, Y', strtotime($row['created_at'])) ?>
                            <?php if ($row['status'] == 'paid' && $row['payment_date']): ?>
                                <br>Paid: <?= date('M d, Y', strtotime($row['payment_date'])) ?>
                                <?php if ($row['payment_method']): ?>
                                    via <span style="text-transform: capitalize; font-weight: bold;"><?= $row['payment_method'] ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div style="text-align: right;">
                        <h3 style="margin: 0; color: #1e3c72;">$<?= number_format($row['amount'], 2) ?></h3>
                        <span class="status-badge status-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span>
                    </div>
                </div>
            </div>
            <?php } ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <h3>💰 No charges yet</h3>
                <p>Your charges will appear here when you borrow books.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Reviews Tab -->
    <div id="reviews" class="tab-content">
        <h3>⭐ My Book Reviews</h3>
        <?php
        $my_reviews_query = mysqli_query($conn, "SELECT br.*, b.book_name, b.author FROM book_reviews br JOIN books b ON br.book_id = b.id WHERE br.student_id = $student_id ORDER BY br.created_at DESC");
        ?>
        
        <?php if (mysqli_num_rows($my_reviews_query) > 0): ?>
            <?php while ($review = mysqli_fetch_assoc($my_reviews_query)): ?>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 5px 0; color: #1e3c72;"><?= htmlspecialchars($review['book_name']) ?></h4>
                        <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">by <?= htmlspecialchars($review['author']) ?></p>
                        <div style="color: #ffc107; font-size: 18px; margin-bottom: 10px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?= $i <= $review['rating'] ? '★' : '☆' ?>
                            <?php endfor; ?>
                            <span style="color: #666; font-size: 14px; margin-left: 5px;">(<?= $review['rating'] ?>/5)</span>
                        </div>
                        <?php if ($review['review_text']): ?>
                            <p style="margin: 10px 0 0 0; color: #333; line-height: 1.6;"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right; margin-left: 20px;">
                        <small style="color: #999;"><?= date('M d, Y', strtotime($review['created_at'])) ?></small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <h3>📝 No reviews yet</h3>
                <p>Start reviewing books you've borrowed!</p>
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
</script>
</body>
</html>
