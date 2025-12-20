<?php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM products WHERE id=$id AND user_id=$user_id";
    if (mysqli_query($conn, $query)) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Error deleting product: " . mysqli_error($conn);
    }
}

// Handle Add/Edit
if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    
    $image_path = "";
    $image_data = null;
    $image_type = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // File System Upload (Backup/Legacy)
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
        
        // Database BLOB Upload (Primary)
        // Re-read the file from the moved location because tmp_name is gone
        if(file_exists($target_file)) {
            $image_content = file_get_contents($target_file);
            $image_data = mysqli_real_escape_string($conn, $image_content);
            $image_type = $_FILES['image']['type'];
        }
    }

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        // Update
        $id = $_POST['id'];
        $update_query = "UPDATE products SET name='$name', description='$description', price='$price', stock='$stock', category_id='$category_id'";
        if ($image_data) {
            $update_query .= ", image='$image_path', image_data='$image_data', image_type='$image_type'";
        }
        $update_query .= " WHERE id=$id AND user_id=$user_id";
        
        if (mysqli_query($conn, $update_query)) {
            $message = "Product updated successfully!";
        } else {
            $message = "Error updating product: " . mysqli_error($conn);
        }
    } else {
        // Insert
        $img_d = $image_data ? "'$image_data'" : "NULL";
        $img_t = $image_type ? "'$image_type'" : "NULL";
        
        $query = "INSERT INTO products (user_id, name, description, price, stock, category_id, image, image_data, image_type) VALUES ('$user_id', '$name', '$description', '$price', '$stock', '$category_id', '$image_path', $img_d, $img_t)";
        if (mysqli_query($conn, $query)) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product: " . mysqli_error($conn);
        }
    }
}

// Fetch Categories for dropdown
$categories = [];
$cat_query = "SELECT * FROM categories WHERE user_id = $user_id";
$cat_result = mysqli_query($conn, $cat_query);
while ($row = mysqli_fetch_assoc($cat_result)) {
    $categories[] = $row;
}

// Fetch Products
$products = [];
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.user_id = $user_id";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .product-image-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
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
                <a href="admin-products.php" class="nav-item active">
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
                <h1>Product Management</h1>
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
                    <button class="btn-primary" onclick="openProductModal()">
                        <i class="fas fa-plus"></i>
                        Add New Product
                    </button>
                </div>

                <!-- Products Table -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productsTable">
                                <?php foreach ($products as $product) { ?>
                                <tr>
                                    <td>
                                        <?php if(!empty($product['image_data'])) { ?>
                                            <img src="data:<?php echo $product['image_type']; ?>;base64,<?php echo base64_encode($product['image_data']); ?>" alt="Product" class="product-image-thumb">
                                        <?php } elseif($product['image']) { ?>
                                            <img src="<?php echo $product['image']; ?>" alt="Product" class="product-image-thumb">
                                        <?php } else { ?>
                                            <i class="fas fa-box fa-2x text-muted"></i>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name']; ?></td>
                                    <td>Rs <?php echo number_format($product['price'], 0); ?></td>
                                    <td>
                                        <?php 
                                            $stockClass = 'success';
                                            if($product['stock'] == 0) $stockClass = 'danger';
                                            else if($product['stock'] < 10) $stockClass = 'warning';
                                        ?>
                                        <span class="status-badge <?php echo $stockClass; ?>"><?php echo $product['stock']; ?></span>
                                    </td>
                                    <td>
                                        <button class="action-btn edit" onclick='editProduct(<?php echo json_encode($product); ?>)' title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="action-btn delete" onclick="confirmDeleteProduct(<?php echo $product['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php if (empty($products)) { ?>
                                    <tr><td colspan="6" class="text-center">No products yet</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Product</h2>
                <button class="close-btn" onclick="closeProductModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" id="productId" name="id">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productName">Product Name *</label>
                            <input type="text" id="productName" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="productCategory">Category *</label>
                            <select id="productCategory" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat) { ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="productDescription">Description</label>
                        <textarea id="productDescription" name="description" rows="4"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="productPrice">Price (Rs) *</label>
                            <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="productStock">Stock Quantity *</label>
                            <input type="number" id="productStock" name="stock" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="productImage">Product Image</label>
                        <input type="file" id="productImage" name="image" accept="image/*">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" onclick="closeProductModal()">Cancel</button>
                        <button type="submit" name="submit" class="btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/admin-ui.js"></script>
    <script>
        function openProductModal() {
            document.getElementById('productModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Add New Product';
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function editProduct(product) {
            document.getElementById('productModal').style.display = 'block';
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
        }

        function confirmDeleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = 'admin-products.php?action=delete&id=' + id;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                closeProductModal();
            }
        }
    </script>
</body>
</html>
