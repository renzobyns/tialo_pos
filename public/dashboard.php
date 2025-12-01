<?php
include __DIR__ . '/../src/includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../src/includes/db_connect.php';

$today = date('Y-m-d');

$sales_query = "SELECT SUM(total_amount) as daily_sales FROM transactions WHERE DATE(transaction_date) = '$today'";
$sales_result = $conn->query($sales_query);
$daily_sales = $sales_result->fetch_assoc()['daily_sales'] ?? 0;

$low_stock_query = "SELECT COUNT(*) as low_stock_count FROM products WHERE quantity < 5 AND status = 'Available'";
$low_stock_result = $conn->query($low_stock_query);
$low_stock_count = $low_stock_result->fetch_assoc()['low_stock_count'] ?? 0;

$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'] ?? 0;
$today_transactions = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE DATE(transaction_date) = '$today'")->fetch_assoc()['count'] ?? 0;

$top_products = [];
$top_query = "SELECT p.name, SUM(ti.quantity) as qty_sold, SUM(ti.subtotal) as revenue 
             FROM transaction_items ti 
             JOIN products p ON ti.product_id = p.product_id 
             JOIN transactions t ON ti.transaction_id = t.transaction_id 
             WHERE DATE(t.transaction_date) = '$today'
             GROUP BY p.product_id 
             ORDER BY qty_sold DESC LIMIT 5";
if ($top_result = $conn->query($top_query)) {
    while ($row = $top_result->fetch_assoc()) {
        $top_products[] = $row;
    }
}

$low_stock_items = [];
$critical_query = "SELECT name, quantity FROM products WHERE quantity < 5 AND status = 'Available' ORDER BY quantity ASC LIMIT 5";
if ($critical_result = $conn->query($critical_query)) {
    while ($row = $critical_result->fetch_assoc()) {
        $low_stock_items[] = $row;
    }
}

$recent_sales = [];
$recent_query = "SELECT transaction_id, total_amount, payment_type, transaction_date
                 FROM transactions ORDER BY transaction_date DESC LIMIT 5";
if ($recent_result = $conn->query($recent_query)) {
    while ($row = $recent_result->fetch_assoc()) {
        $recent_sales[] = $row;
    }
}
?>
<?php
$page_title = 'Dashboard - Tialo Japan Surplus';
include __DIR__ . '/../src/includes/page_header.php';
?>
<body class="bg-slate-50 flex">
    <?php include __DIR__ . '/../src/includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="px-8 py-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm uppercase tracking-wide text-slate-500">Overview</p>
                    <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
                    <p class="text-sm text-slate-600 mt-1">Real-time snapshot of store performance</p>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex items-center gap-2 bg-slate-100 rounded-lg px-4 py-2 text-sm text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" />
                        </svg>
                        <span><?php echo date('M d, Y', strtotime($today)); ?></span>
                    </div>
                    <a href="?page=reports/export&period=today" class="px-5 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-100">Download Report</a>
                </div>
            </div>
        </header>

        <main class="flex-1 px-8 py-6 space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Daily Sales</p>
                            <p class="text-3xl font-bold text-slate-900">₱<?php echo number_format($daily_sales, 2); ?></p>
                        </div>
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v18m0 0l-3-3m3 3l3-3" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-xs text-emerald-600 mt-3">+12% vs yesterday</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Low Stock Items</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo (int) $low_stock_count; ?></p>
                        </div>
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-yellow-50 text-yellow-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 22a10 10 0 110-20 10 10 0 010 20z" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">Below threshold of 5 units</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Products</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo (int) $total_products; ?></p>
                        </div>
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">Across all categories</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Transactions Today</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo (int) $today_transactions; ?></p>
                        </div>
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-50 text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21V7m-5 14V3m-5 18v-8" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">Completed checkouts</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Top Selling Products</h2>
                            <p class="text-xs text-slate-500">Based on today's sales</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-500">Qty</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 text-left">Product</th>
                                    <th class="px-6 py-3 text-center">Qty</th>
                                    <th class="px-6 py-3 text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (!empty($top_products)): ?>
                                    <?php foreach ($top_products as $product): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="px-6 py-3 text-center font-semibold text-slate-700"><?php echo (int) $product['qty_sold']; ?></td>
                                            <td class="px-6 py-3 text-right font-semibold text-red-600">₱<?php echo number_format($product['revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-6 text-center text-slate-500">No sales recorded today.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Low Stock Alerts</h2>
                            <p class="text-xs text-slate-500">Items below the safety threshold</p>
                        </div>
                        <span class="text-sm font-semibold text-red-600">Action needed</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 text-left">Product</th>
                                    <th class="px-6 py-3 text-center">Stock</th>
                                    <th class="px-6 py-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (!empty($low_stock_items)): ?>
                                    <?php foreach ($low_stock_items as $item): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td class="px-6 py-3 text-center font-semibold text-slate-700"><?php echo (int) $item['quantity']; ?></td>
                                            <td class="px-6 py-3 text-right">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">Low Stock</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-6 text-center text-slate-500">All stock levels look healthy.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Recent Activity</h2>
                        <p class="text-xs text-slate-500">Latest transactions processed</p>
                    </div>
                    <a href="/index.php?page=reports" class="text-sm font-semibold text-red-600 hover:text-red-700">View reports →</a>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php if (!empty($recent_sales)): ?>
                        <?php foreach ($recent_sales as $sale): ?>
                            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Transaction #<?php echo htmlspecialchars($sale['transaction_id']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo date('M d, Y g:i A', strtotime($sale['transaction_date'])); ?></p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><?php echo htmlspecialchars($sale['payment_type'] ?? 'Cash'); ?></span>
                                    <p class="text-base font-bold text-slate-900">₱<?php echo number_format($sale['total_amount'], 2); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="px-6 py-6 text-center text-slate-500 text-sm">No recent transactions recorded.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include __DIR__ . '/../src/includes/page_footer.php'; ?>
