<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

include "db.php";

$student_id = $_SESSION['user_id'];
$book_id = $_GET['book_id'] ?? 0;

// Get book details
$book = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM books WHERE id=$book_id"));

if (!$book) {
    header("Location: student_dashboard.php");
    exit();
}

// Check if student has borrowed this book
$has_borrowed = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM borrow_requests WHERE student_id=$student_id AND book_id=$book_id AND status='approved'")) > 0;

// Get existing review
$existing_review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM book_reviews WHERE student_id=$student_id AND book_id=$book_id"));

$success = "";
$error = "";

// Handle review submission
if (isset($_POST['submit_review'])) {
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);
    
    if ($rating >= 1 && $rating <= 5) {
        if ($existing_review) {
            // Update existing review
            $sql = "UPDATE book_reviews SET rating=$rating, review_text='$review_text' WHERE student_id=$student_id AND book_id=$book_id";
        } else {
            // Insert new review
            $sql = "INSERT INTO book_reviews (book_id, student_id, rating, review_text) VALUES ($book_id, $student_id, $rating, '$review_text')";
        }
        
        if (mysqli_query($conn, $sql)) {
            $success = "✅ Review submitted successfully!";
            $existing_review = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM book_reviews WHERE student_id=$student_id AND book_id=$book_id"));
        } else {
            $error = "❌ Error submitting review: " . mysqli_error($conn);
        }
    } else {
        $error = "❌ Please select a valid rating (1-5 stars)";
    }
}

// Get all reviews for this book
$all_reviews = mysqli_query($conn, "SELECT br.*, s.username, s.full_name FROM book_reviews br JOIN students s ON br.student_id = s.id WHERE br.book_id = $book_id ORDER BY br.created_at DESC");

// Calculate average rating
$avg_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) as avg, COUNT(*) as count FROM book_reviews WHERE book_id=$book_id"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Book</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .review-container {
            max-width: 900px;
            margin: 40px auto;
        }
        .book-header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .rating-stars {
            font-size: 32px;
            color: #ffc107;
            cursor: pointer;
            user-select: none;
        }
        .rating-stars span {
            transition: all 0.2s;
        }
        .rating-stars span:hover {
            transform: scale(1.2);
        }
        .review-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #1e3c72;
        }
        .my-review {
            border-left-color: #28a745;
            background: #d4edda;
        }
        textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="container review-container">
    <div class="book-header">
        <h2>📚 <?= htmlspecialchars($book['book_name']) ?></h2>
        <p style="font-size: 18px; margin: 10px 0;">by <?= htmlspecialchars($book['author']) ?></p>
        <p style="opacity: 0.9;"><?= $book['category'] ?> • <?= $book['year'] ?></p>
        
        <?php if ($avg_rating['count'] > 0): ?>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.3);">
            <div style="font-size: 24px; color: #ffc107;">
                <?php 
                $avg = round($avg_rating['avg'], 1);
                for ($i = 1; $i <= 5; $i++) {
                    echo $i <= $avg ? '★' : '☆';
                }
                ?>
            </div>
            <p style="margin: 5px 0 0 0;"><?= number_format($avg, 1) ?> out of 5 (<?= $avg_rating['count'] ?> review<?= $avg_rating['count'] != 1 ? 's' : '' ?>)</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($success): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $success ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <!-- Review Form -->
    <div style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3><?= $existing_review ? '✏️ Edit Your Review' : '✍️ Write a Review' ?></h3>
        
        <?php if (!$has_borrowed): ?>
            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 15px 0;">
                ℹ️ You can review this book even if you haven't borrowed it yet!
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div style="margin: 20px 0;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">Your Rating:</label>
                <div class="rating-stars" id="ratingStars">
                    <span data-rating="1">☆</span>
                    <span data-rating="2">☆</span>
                    <span data-rating="3">☆</span>
                    <span data-rating="4">☆</span>
                    <span data-rating="5">☆</span>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="<?= $existing_review['rating'] ?? 0 ?>" required>
            </div>
            
            <div style="margin: 20px 0;">
                <label style="display: block; margin-bottom: 10px; font-weight: bold;">Your Review (Optional):</label>
                <textarea name="review_text" placeholder="Share your thoughts about this book..."><?= htmlspecialchars($existing_review['review_text'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" name="submit_review" class="btn-submit">
                <?= $existing_review ? '💾 Update Review' : '📝 Submit Review' ?>
            </button>
        </form>
    </div>

    <!-- All Reviews -->
    <h3>📖 All Reviews (<?= $avg_rating['count'] ?>)</h3>
    
    <?php if (mysqli_num_rows($all_reviews) > 0): ?>
        <?php while ($review = mysqli_fetch_assoc($all_reviews)): ?>
        <div class="review-card <?= $review['student_id'] == $student_id ? 'my-review' : '' ?>">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <strong><?= htmlspecialchars($review['full_name'] ?? $review['username']) ?></strong>
                    <?= $review['student_id'] == $student_id ? '<span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px; margin-left: 10px;">You</span>' : '' ?>
                    <div style="color: #ffc107; font-size: 18px; margin: 5px 0;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?= $i <= $review['rating'] ? '★' : '☆' ?>
                        <?php endfor; ?>
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
        <div style="text-align: center; padding: 40px; color: #999; background: #f8f9fa; border-radius: 10px;">
            <h3>📝 No reviews yet</h3>
            <p>Be the first to review this book!</p>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 30px;">
        <a href="student_dashboard.php" style="padding: 12px 30px; background: #6c757d; color: white; border-radius: 25px; text-decoration: none; display: inline-block;">
            ← Back to Dashboard
        </a>
    </div>
</div>

<script>
// Rating stars functionality
const stars = document.querySelectorAll('.rating-stars span');
const ratingInput = document.getElementById('ratingInput');
const currentRating = parseInt(ratingInput.value) || 0;

// Set initial rating
updateStars(currentRating);

stars.forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        ratingInput.value = rating;
        updateStars(rating);
    });
    
    star.addEventListener('mouseover', function() {
        const rating = parseInt(this.getAttribute('data-rating'));
        updateStars(rating);
    });
});

document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
    updateStars(parseInt(ratingInput.value) || 0);
});

function updateStars(rating) {
    stars.forEach((star, index) => {
        if (index < rating) {
            star.textContent = '★';
        } else {
            star.textContent = '☆';
        }
    });
}
</script>
</body>
</html>
