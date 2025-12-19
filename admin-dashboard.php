<?php
require('auth_session.php');
require('db_connect.php');

$user_id = $_SESSION['user_id'];

// Fetch stats
$total_sales = 0;
$total_orders = 0;
$total_products = 0;
$total_customers = 0;
$total_categories = 0;

$result = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE user_id = $user_id");
if ($row = mysqli_fetch_assoc($result)) {
    $total_sales = $row['total'] ? $row['total'] : 0;
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id");
if ($row = mysqli_fetch_assoc($result)) {
    $total_orders = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE user_id = $user_id");
if ($row = mysqli_fetch_assoc($result)) {
    $total_products = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM customers WHERE user_id = $user_id");
if ($row = mysqli_fetch_assoc($result)) {
    $total_customers = $row['count'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM categories WHERE user_id = $user_id");
if ($row = mysqli_fetch_assoc($result)) {
    $total_categories = $row['count'];
}

// Fetch recent orders
$recent_orders = [];
$query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC LIMIT 5";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $recent_orders[] = $row;
}

// Fetch sales for last 7 days
$dates = [];
$sales = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('M d', strtotime($date));
    
    $query = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = $user_id AND DATE(order_date) = '$date'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $sales[] = $row['total'] ? $row['total'] : 0;
}

// Fetch top products
$top_products = [];
$query = "SELECT * FROM products WHERE user_id = $user_id LIMIT 5";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $top_products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - E-Commerce Admin Panel</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="admin-dashboard.php" class="nav-item active">
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
            <!-- Top Bar -->
            <header class="topbar">
                <button class="sidebar-toggle mobile-only" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
                <div class="topbar-right">
                    <div class="admin-info">
                        <span id="adminName"><?php echo $_SESSION['name']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="content-area">
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <div class="welcome-content">
                        <h2><i class="fas fa-chart-line"></i> Welcome Back, <span id="welcomeName"><?php echo $_SESSION['name']; ?></span>!</h2>
                        <p>Here's what's happening with your store today.</p>
                    </div>
                    <div class="welcome-icon">
                        <i class="fas fa-store-alt"></i>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon sales">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Rs <?php echo number_format($total_sales, 0); ?></h3>
                            <p>Total Sales</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_orders; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_products; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_customers; ?></h3>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon categories">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_categories; ?></h3>
                            <p>Total Categories</p>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Activity -->
                <div class="dashboard-grid">
                    <!-- Sales Chart -->
                    <div class="dashboard-card">
                        <h2>Sales Overview</h2>
                        <canvas id="salesChart"></canvas>
                    </div>

                    <!-- Top Products -->
                    <div class="dashboard-card">
                        <h2>Top Products</h2>
                        <div id="topProducts">
                            <?php foreach ($top_products as $product) { ?>
                            <div class="product-list-item">
                                <div class="product-list-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="product-list-info">
                                    <h4><?php echo $product['name']; ?></h4>
                                    <p>Rs <?php echo number_format($product['price'], 0); ?></p>
                                </div>
                            </div>
                            <?php } ?>
                            <?php if (empty($top_products)) { ?>
                                <p class="text-center text-muted">No products found</p>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="dashboard-card" style="grid-column: 1 / -1;">
                        <h2>Recent Orders</h2>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentOrders">
                                    <?php foreach ($recent_orders as $order) { ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo $order['customer_name']; ?></td>
                                        <td>Rs <?php echo number_format($order['total_amount'], 0); ?></td>
                                        <td><span class="status-badge <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if (empty($recent_orders)) { ?>
                                        <tr><td colspan="4" class="text-center">No orders yet</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/admin-ui.js"></script>
    <script>
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Sales',
                    data: <?php echo json_encode($sales); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2, 4]
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
