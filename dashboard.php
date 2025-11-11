<?php
include 'includes/auth_check.php';
checkRole('Admin');
include 'includes/db_connect.php';


$today = date('Y-m-d');

$sales_query = "SELECT SUM(total_amount) as daily_sales FROM transactions WHERE DATE(transaction_date) = '$today'";
$sales_result = $conn->query($sales_query);
$daily_sales = $sales_result->fetch_assoc()['daily_sales'] ?? 0;

$low_stock_query = "SELECT COUNT(*) as low_stock_count FROM products WHERE quantity < 5 AND status = 'Available'";
$low_stock_result = $conn->query($low_stock_query);
$low_stock_count = $low_stock_result->fetch_assoc()['low_stock_count'];

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$today_transactions = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = '$today'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tialo Japan Surplus</title>
    <?php include 'includes/tailwind-cdn.html'; ?>
</head>
<body class="bg-slate-50">
    <?php include 'includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-4xl font-bold text-slate-900 mb-2">Dashboard Overview</h2>
            <p class="text-slate-600 flex items-center space-x-2">
                <i class="fas fa-calendar text-emerald-600"></i>
                <span><?php echo date('F d, Y'); ?></span>
            </p>
        </div>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Daily Sales Card -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Daily Sales</p>
                        <p class="text-3xl font-bold text-slate-900">₱<?php echo number_format($daily_sales, 2); ?></p>
                    </div>
                    <div class="bg-emerald-100 rounded-full p-4">
                        <i class="fas fa-chart-line text-emerald-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Card -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 border-l-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Low Stock Items</p>
                        <p class="text-3xl font-bold text-slate-900"><?php echo $low_stock_count; ?></p>
                    </div>
                    <div class="bg-amber-100 rounded-full p-4">
                        <i class="fas fa-exclamation-triangle text-amber-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Total Products Card -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Total Products</p>
                        <p class="text-3xl font-bold text-slate-900"><?php echo $total_products; ?></p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-boxes text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Transactions Card -->
            <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-600 text-sm font-medium mb-1">Transactions Today</p>
                        <p class="text-3xl font-bold text-slate-900"><?php echo $today_transactions; ?></p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-receipt text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tables Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Selling Products -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-slate-900 to-slate-700 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <i class="fas fa-fire text-orange-400"></i>
                        <span>Top Selling Products</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Product</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Qty</th>
                                    <th class="text-right py-3 px-4 font-semibold text-slate-700">Revenue</th>
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
                                if ($top_result->num_rows > 0) {
                                    while ($row = $top_result->fetch_assoc()): 
                                ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td class="text-center py-3 px-4 text-slate-700 font-medium"><?php echo $row['qty_sold']; ?></td>
                                        <td class="text-right py-3 px-4 text-emerald-700 font-semibold">₱<?php echo number_format($row['revenue'], 2); ?></td>
                                    </tr>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<tr><td colspan="3" class="py-6 px-4 text-center text-slate-500"><i class="fas fa-inbox"></i> No sales today</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Alert -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-6 py-4">
                    <h3 class="text-lg font-bold text-white flex items-center space-x-2">
                        <i class="fas fa-bell text-white"></i>
                        <span>Low Stock Alert</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Product</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Stock</th>
                                    <th class="text-right py-3 px-4 font-semibold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $low_query = "SELECT product_id, name, quantity FROM products WHERE quantity < 5 AND status = 'Available' ORDER BY quantity ASC LIMIT 5";
                                $low_result = $conn->query($low_query);
                                if ($low_result->num_rows > 0) {
                                    while ($row = $low_result->fetch_assoc()): 
                                ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                                        <td class="py-3 px-4 text-slate-900"><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td class="text-center py-3 px-4 font-medium text-amber-700"><?php echo $row['quantity']; ?></td>
                                        <td class="text-right py-3 px-4">
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                <i class="fas fa-exclamation-circle mr-1"></i>Low Stock
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<tr><td colspan="3" class="py-6 px-4 text-center text-slate-500"><i class="fas fa-check-circle mr-2"></i>All stock levels healthy</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Access -->
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-xl shadow-lg p-8 text-white">
            <h3 class="text-xl font-bold mb-6 flex items-center space-x-2">
                <i class="fas fa-rocket text-emerald-400"></i>
                <span>Quick Access</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="modules/pos/index.php" class="bg-slate-700 hover:bg-slate-600 rounded-lg p-4 transition flex items-center space-x-3 transform hover:scale-105">
                    <div class="bg-emerald-500 rounded-full p-3">
                        <i class="fas fa-cash-register text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Go to POS</p>
                        <p class="text-sm text-slate-300">Process sales</p>
                    </div>
                </a>
                
                <a href="modules/inventory/index.php" class="bg-slate-700 hover:bg-slate-600 rounded-lg p-4 transition flex items-center space-x-3 transform hover:scale-105">
                    <div class="bg-blue-500 rounded-full p-3">
                        <i class="fas fa-boxes text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Manage Inventory</p>
                        <p class="text-sm text-slate-300">Stock control</p>
                    </div>
                </a>
                
                <a href="modules/reports/index.php" class="bg-slate-700 hover:bg-slate-600 rounded-lg p-4 transition flex items-center space-x-3 transform hover:scale-105">
                    <div class="bg-purple-500 rounded-full p-3">
                        <i class="fas fa-chart-bar text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold">View Reports</p>
                        <p class="text-sm text-slate-300">Analytics</p>
                    </div>
                </a>
                
                <a href="modules/users/index.php" class="bg-slate-700 hover:bg-slate-600 rounded-lg p-4 transition flex items-center space-x-3 transform hover:scale-105">
                    <div class="bg-pink-500 rounded-full p-3">
                        <i class="fas fa-users text-xl text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Manage Users</p>
                        <p class="text-sm text-slate-300">Admin panel</p>
                    </div>
                </a>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
