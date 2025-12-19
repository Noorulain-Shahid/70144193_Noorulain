<?php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM categories WHERE id=$id AND user_id=$user_id";
    if (mysqli_query($conn, $query)) {
        $message = "Category deleted successfully!";
    } else {
        $message = "Error deleting category: " . mysqli_error($conn);
    }
}

// Handle Add/Edit
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $icon = mysqli_real_escape_string($conn, $_POST['icon']);
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = $_POST['id'];
        $query = "UPDATE categories SET name='$name', description='$description', icon='$icon' WHERE id=$id AND user_id=$user_id";
        if (mysqli_query($conn, $query)) {
            $message = "Category updated successfully!";
        } else {
            $message = "Error updating category: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $query = "INSERT INTO categories (user_id, name, description, icon) VALUES ('$user_id', '$name', '$description', '$icon')";
        if (mysqli_query($conn, $query)) {
            $message = "Category added successfully!";
        } else {
            $message = "Error adding category: " . mysqli_error($conn);
        }
    }
}

// Fetch Categories
$categories = [];
$query = "SELECT * FROM categories WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    // Get product count for each category
    $cat_id = $row['id'];
    $count_query = "SELECT COUNT(*) as count FROM products WHERE category_id=$cat_id AND user_id=$user_id";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $row['productCount'] = $count_row['count'];
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .category-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        .category-card i {
            font-size: 2rem;
            color: var(--primary-color);
            width: 50px;
            text-align: center;
        }
        .category-info {
            flex: 1;
        }
        .category-info h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }
        .category-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .category-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            padding: 5px;
            transition: color 0.3s;
        }
        .action-btn.edit { color: #3498db; }
        .action-btn.delete { color: #e74c3c; }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
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
                <a href="admin-categories.php" class="nav-item active">
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
                <a href="admin-feedback.php" class="nav-item">
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
                <h1>Category Management</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo $_SESSION['name']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <?php if($message != "") { ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php } ?>

                <!-- Action Bar -->
                <div class="action-bar">
                    <button class="btn-primary" onclick="openCategoryModal()">
                        <i class="fas fa-plus"></i>
                        Add New Category
                    </button>
                </div>

                <!-- Categories Grid -->
                <div class="categories-grid" id="categoriesGrid">
                    <?php foreach ($categories as $category) { ?>
                    <div class="category-card">
                        <i class="<?php echo $category['icon'] ? $category['icon'] : 'fas fa-tag'; ?>"></i>
                        <div class="category-info">
                            <h3><?php echo $category['name']; ?></h3>
                            <p><?php echo $category['description']; ?></p>
                            <p class="text-muted category-product-count"><?php echo $category['productCount']; ?> products</p>
                            <div class="category-actions">
                                <button class="action-btn edit" onclick='editCategory(<?php echo json_encode($category); ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete" onclick="confirmDeleteCategory(<?php echo $category['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (empty($categories)) { ?>
                        <p class="text-center">No categories yet</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Category</h2>
                <button class="close-btn" onclick="closeCategoryModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="categoryForm" method="post" action="">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="form-group">
                        <label for="categoryName">Category Name *</label>
                        <input type="text" id="categoryName" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="categoryDescription">Description</label>
                        <textarea id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categoryIcon">Icon (Font Awesome class)</label>
                        <input type="text" id="categoryIcon" name="icon" placeholder="fas fa-laptop">
                        <small>Example: fas fa-laptop, fas fa-mobile, fas fa-tshirt</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeCategoryModal()">Cancel</button>
                        <button type="submit" name="submit" class="btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/admin-ui.js"></script>
    <script>
        function openCategoryModal() {
            document.getElementById('categoryModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Add New Category';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function editCategory(category) {
            document.getElementById('categoryModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description;
            document.getElementById('categoryIcon').value = category.icon;
        }

        function confirmDeleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                window.location.href = 'admin-categories.php?action=delete&id=' + id;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                closeCategoryModal();
            }
        }
    </script>
</body>
</html>
