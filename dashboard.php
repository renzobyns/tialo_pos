<?php
include 'includes/auth_check.php';
checkRole('Admin');
include 'includes/db_connect.php';

// Get dashboard statistics
$today = date('Y-m-d');

// Daily sales
$sales_query = "SELECT SUM(total_amount) as daily_sales FROM transactions WHERE DATE(transaction_date) = '$today'";
$sales_result = $conn->query($sales_query);
$daily_sales = $sales_result->fetch_assoc()['daily_sales'] ?? 0;

// Low stock products
$low_stock_query = "SELECT COUNT(*) as low_stock_count FROM products WHERE quantity < 5 AND status = 'Available'";
$low_stock_result = $conn->query($low_stock_query);
$low_stock_count = $low_stock_result->fetch_assoc()['low_stock_count'];

// Top selling products
$top_products_query = "SELECT p.name, SUM(ti.quantity) as total_sold FROM transaction_items ti 
                       JOIN products p ON ti.product_id = p.product_id 
                       WHERE DATE(ti.transaction_id) IN (SELECT transaction_id FROM transactions WHERE DATE(transaction_date) = '$today')
                       GROUP BY p.product_id ORDER BY total_sold DESC LIMIT 5";
$top_products_result = $conn->query($top_products_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tialo Japan Surplus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="dashboard-container">
        <div class="dashboard-header">
            <h2>Dashboard Overview</h2>
            <p class="date-info">Today: <?php echo date('F d, Y'); ?></p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <h3>Daily Sales</h3>
                    <p class="stat-value">₱<?php echo number_format($daily_sales, 2); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <h3>Low Stock Items</h3>
                    <p class="stat-value"><?php echo $low_stock_count; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3>Total Products</h3>
                    <p class="stat-value"><?php echo $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count']; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🛒</div>
                <div class="stat-content">
                    <h3>Transactions Today</h3>
                    <p class="stat-value"><?php echo $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = '$today'")->fetch_assoc()['count']; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Top Selling Products -->
        <div class="dashboard-section">
            <h3>Top Selling Products</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $top_query = "SELECT p.name, SUM(ti.quantity) as qty_sold, SUM(ti.subtotal) as revenue 
                                     FROM transaction_items ti 
                                     JOIN products p ON ti.product_id = p.product_id 
                                     JOIN transactions t ON ti.transaction_id = t.transaction_id 
                                     WHERE DATE(t.transaction_date) = '$today'
                                     GROUP BY p.product_id 
                                     ORDER BY qty_sold DESC LIMIT 5";
                        $top_result = $conn->query($top_query);
                        while ($row = $top_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['qty_sold']; ?></td>
                                <td>₱<?php echo number_format($row['revenue'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Low Stock Alert -->
        <div class="dashboard-section">
            <h3>Low Stock Alert</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $low_query = "SELECT product_id, name, quantity FROM products WHERE quantity < 5 AND status = 'Available' ORDER BY quantity ASC LIMIT 5";
                        $low_result = $conn->query($low_query);
                        while ($row = $low_result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><span class="badge-warning">Low Stock</span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Quick Access -->
        <div class="quick-access">
            <h3>Quick Access</h3>
            <div class="quick-buttons">
                <a href="modules/pos/index.php" class="quick-btn">Go to POS</a>
                <a href="modules/inventory/index.php" class="quick-btn">Manage Inventory</a>
                <a href="modules/reports/index.php" class="quick-btn">View Reports</a>
                <a href="modules/users/index.php" class="quick-btn">Manage Users</a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
