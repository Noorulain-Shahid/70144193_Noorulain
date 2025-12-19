<?php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];

// Fetch Feedback
$feedbacks = [];
$query = "SELECT * FROM feedback WHERE user_id = $user_id ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $feedbacks[] = $row;
}

// Calculate Ratings
$total_reviews = count($feedbacks);
$avg_rating = 0;
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

if ($total_reviews > 0) {
    $sum_rating = 0;
    foreach ($feedbacks as $fb) {
        $r = $fb['rating'];
        if ($r >= 1 && $r <= 5) {
            $rating_counts[$r]++;
            $sum_rating += $r;
        }
    }
    $avg_rating = $sum_rating / $total_reviews;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback & Reviews - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .review-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .review-user {
            font-weight: bold;
        }
        .review-date {
            color: #888;
            font-size: 0.9rem;
        }
        .review-rating {
            color: #f1c40f;
            margin-bottom: 10px;
        }
        .review-message {
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-store"></i>
                <h2>Admin Panel</h2>
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                <a href="admin-dashboard.php" class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin-orders.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="admin-products.php" class="nav-item">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="admin-categories.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="admin-customers.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
                <a href="admin-analytics.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
                <a href="admin-feedback.php" class="nav-item active">
                    <i class="fas fa-comments"></i>
                    <span>Feedback & Reviews</span>
                </a>
                <a href="admin-profile.php" class="nav-item">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="topbar">
                <button class="sidebar-toggle mobile-only" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Feedback & Reviews</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo $_SESSION['name']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Rating Overview -->
                <div class="rating-overview-card">
                    <div class="rating-summary">
                        <div class="rating-main">
                            <div class="rating-number" id="avgRatingBig"><?php echo number_format($avg_rating, 1); ?></div>
                            <div class="rating-stars" id="avgRatingStars">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= round($avg_rating)) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="rating-count" id="totalReviewsText"><?php echo $total_reviews; ?> Reviews</p>
                        </div>
                        <div class="rating-breakdown">
                            <?php for ($i = 5; $i >= 1; $i--) { 
                                $percent = $total_reviews > 0 ? ($rating_counts[$i] / $total_reviews) * 100 : 0;
                            ?>
                            <div class="rating-row">
                                <span class="rating-label"><?php echo $i; ?> <i class="fas fa-star"></i></span>
                                <div class="rating-bar">
                                    <div class="rating-bar-fill" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <span class="rating-count-num"><?php echo $rating_counts[$i]; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                <div class="reviews-list">
                    <?php foreach ($feedbacks as $fb) { ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-user">
                                <i class="fas fa-user-circle"></i> <?php echo $fb['user_name']; ?>
                            </div>
                            <div class="review-date">
                                <?php echo date('M d, Y', strtotime($fb['created_at'])); ?>
                            </div>
                        </div>
                        <div class="review-rating">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $fb['rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="review-message">
                            <?php echo $fb['message']; ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (empty($feedbacks)) { ?>
                        <p class="text-center">No feedback yet</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <script src="js/admin-ui.js"></script>
</body>
</html>
