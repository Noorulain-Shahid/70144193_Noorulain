<?php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];

// Handle Actions
if (isset($_GET['action'])) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    // Delete
    if ($_GET['action'] == 'delete' && $id > 0) {
        $query = "DELETE FROM feedback WHERE id=$id AND user_id=$user_id";
        mysqli_query($conn, $query);
        header("Location: admin-feedback.php");
        exit();
    }
    
    // Approve
    if ($_GET['action'] == 'approve' && $id > 0) {
        $query = "UPDATE feedback SET status='Approved' WHERE id=$id AND user_id=$user_id";
        mysqli_query($conn, $query);
        header("Location: admin-feedback.php");
        exit();
    }
}

// Handle POST Actions (Reply & Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reply_submit'])) {
        $id = (int)$_POST['feedback_id'];
        $reply = mysqli_real_escape_string($conn, $_POST['reply_message']);
        $query = "UPDATE feedback SET reply='$reply', reply_date=NOW() WHERE id=$id AND user_id=$user_id";
        mysqli_query($conn, $query);
        header("Location: admin-feedback.php");
        exit();
    }
    
    if (isset($_POST['edit_submit'])) {
        $id = (int)$_POST['feedback_id'];
        $reply = mysqli_real_escape_string($conn, $_POST['edit_reply_content']);
        $query = "UPDATE feedback SET reply='$reply' WHERE id=$id AND user_id=$user_id";
        mysqli_query($conn, $query);
        header("Location: admin-feedback.php");
        exit();
    }
}

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
            position: relative;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .review-user {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
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
            margin-bottom: 15px;
        }
        .review-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .btn-action {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-approve { background-color: #2ecc71; }
        .btn-reply { background-color: #3498db; }
        .btn-edit { background-color: #f39c12; }
        .btn-delete { background-color: #e74c3c; }
        
        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .status-pending { background-color: #f1c40f; color: #fff; }
        .status-approved { background-color: #2ecc71; color: #fff; }

        .admin-reply {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 3px solid #3498db;
            margin-top: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 50%; 
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; }
        .btn-submit { background-color: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn-submit:hover { background-color: #2980b9; }
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
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($fb['user_name']); ?>
                                <span class="status-badge <?php echo ($fb['status'] == 'Approved') ? 'status-approved' : 'status-pending'; ?>">
                                    <?php echo isset($fb['status']) ? $fb['status'] : 'Pending'; ?>
                                </span>
                            </div>
                            <div style="display:flex; gap:10px; align-items:center;">
                                <div class="review-date">
                                    <?php echo date('M d, Y', strtotime($fb['created_at'])); ?>
                                </div>
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
                            <?php echo htmlspecialchars($fb['message']); ?>
                        </div>
                        
                        <?php if (!empty($fb['reply'])) { ?>
                        <div class="admin-reply">
                            <strong>Admin Reply:</strong><br>
                            <?php echo htmlspecialchars($fb['reply']); ?>
                            <div style="font-size:0.8rem; color:#888; margin-top:5px;">
                                <?php echo date('M d, Y H:i', strtotime($fb['reply_date'])); ?>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="review-actions">
                            <?php if (!isset($fb['status']) || $fb['status'] != 'Approved') { ?>
                            <a href="admin-feedback.php?action=approve&id=<?php echo $fb['id']; ?>" class="btn-action btn-approve" title="Approve"><i class="fas fa-check"></i> Approve</a>
                            <?php } ?>
                            
                            <?php if (empty($fb['reply'])) { ?>
                            <button onclick="openReplyModal(<?php echo $fb['id']; ?>)" class="btn-action btn-reply" title="Reply"><i class="fas fa-reply"></i> Reply</button>
                            <?php } else { ?>
                            <button onclick="openEditModal(<?php echo $fb['id']; ?>, <?php echo htmlspecialchars(json_encode($fb['reply'])); ?>)" class="btn-action btn-edit" title="Edit Reply"><i class="fas fa-edit"></i> Edit Reply</button>
                            <?php } ?>
                            
                            <a href="admin-feedback.php?action=delete&id=<?php echo $fb['id']; ?>" onclick="return confirm('Delete this feedback?')" class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i> Delete</a>
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

    <!-- Reply Modal -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('replyModal')">&times;</span>
            <h3>Reply to Feedback</h3>
            <form method="POST" action="admin-feedback.php">
                <input type="hidden" name="feedback_id" id="reply_feedback_id">
                <div class="form-group">
                    <label>Your Reply:</label>
                    <textarea name="reply_message" rows="4" required></textarea>
                </div>
                <button type="submit" name="reply_submit" class="btn-submit">Send Reply</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editModal')">&times;</span>
            <h3>Edit Reply</h3>
            <form method="POST" action="admin-feedback.php">
                <input type="hidden" name="feedback_id" id="edit_feedback_id">
                <div class="form-group">
                    <label>Your Reply:</label>
                    <textarea name="edit_reply_content" id="edit_message" rows="4" required></textarea>
                </div>
                <button type="submit" name="edit_submit" class="btn-submit">Update Reply</button>
            </form>
        </div>
    </div>

    <script src="js/admin-ui.js"></script>
    <script>
        function openReplyModal(id) {
            document.getElementById('reply_feedback_id').value = id;
            document.getElementById('replyModal').style.display = "block";
        }
        function openEditModal(id, message) {
            document.getElementById('edit_feedback_id').value = id;
            document.getElementById('edit_message').value = message;
            document.getElementById('editModal').style.display = "block";
        }
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>
