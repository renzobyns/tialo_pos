<?php
include '../../includes/auth_check.php';
include '../../includes/db_connect.php';

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';
$tab = $_GET['tab'] ?? 'catalog';
$allowed_tabs = ['catalog', 'history'];
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'catalog';
}
$history_ranges = [
    'today' => 'Today',
    'week' => 'This Week',
    'month' => 'This Month',
    'all' => 'All Time'
];
$selected_range = $_GET['range'] ?? 'today';
if (!array_key_exists($selected_range, $history_ranges)) {
    $selected_range = 'today';
}

$query = "SELECT * FROM products WHERE status = 'Available'";

if ($category !== 'All') {
    $category = sanitize($category);
    $query .= " AND category = '$category'";
}

if (!empty($search)) {
    $search = sanitize($search);
    $query .= " AND name LIKE '%$search%'";
}

$query .= " ORDER BY product_id DESC";
$products_result = $conn->query($query);

$categories_query = "SELECT DISTINCT category FROM products WHERE status = 'Available' ORDER BY category";
$categories_result = $conn->query($categories_query);

$history_result = null;
if ($tab === 'history') {
    $range_filters = [
        'today' => "DATE(t.transaction_date) = CURDATE()",
        'week' => "YEARWEEK(t.transaction_date, 1) = YEARWEEK(CURDATE(), 1)",
        'month' => "DATE_FORMAT(t.transaction_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')",
        'all' => "1=1"
    ];
    $where_clause = $range_filters[$selected_range] ?? $range_filters['today'];
    $history_query = "
        SELECT 
            t.transaction_id,
            t.transaction_date,
            t.payment_type,
            t.total_amount,
            u.name AS cashier_name,
            (SELECT COUNT(*) FROM transaction_items ti WHERE ti.transaction_id = t.transaction_id) AS item_count
        FROM transactions t
        LEFT JOIN users u ON u.user_id = t.user_id
        WHERE {$where_clause}
        ORDER BY t.transaction_date DESC
        LIMIT 50
    ";
    $history_result = $conn->query($history_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 page-header">
            <div class="px-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500">Sales Console</p>
                        <h1 class="text-3xl font-bold text-slate-900">Point of Sale</h1>
                        <p class="text-sm text-slate-600">Manage sales and checkout transactions</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="?tab=catalog" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'catalog' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-slate-200 hover:text-slate-900'; ?>">
                            Catalog
                        </a>
                        <a href="?tab=history" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'history' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-slate-200 hover:text-slate-900'; ?>">
                            History
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="flex-1 px-6 py-4">
            <?php if ($tab === 'catalog'): ?>
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 space-y-6">
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <div class="flex flex-col md:flex-row gap-4 mb-6">
                                <div class="flex-1">
                                    <input 
                                        type="text" 
                                        id="searchInput" 
                                        placeholder="Search surplus items... (F2)" 
                                        value="<?php echo htmlspecialchars($search); ?>"
                                        class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    >
                                </div>
                                <button onclick="performSearch()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    <span>Search</span>
                                </button>
                            </div>
                            
                            <div class="flex flex-wrap gap-3">
                                <a href="?category=All&tab=catalog" class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo $category === 'All' ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                                    All Items
                                </a>
                                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                    <a href="?category=<?php echo urlencode($cat['category']); ?>&tab=catalog" 
                                       class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo $category === $cat['category'] ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                            <?php 
                            if ($products_result && $products_result->num_rows > 0) {
                                while ($product = $products_result->fetch_assoc()): ?>
                                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden hover:shadow-lg hover:border-red-200 transition">
                                    <div class="h-48 bg-slate-100 overflow-hidden relative">
                                        <img src="/placeholder.svg?height=200&width=200" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="w-full h-full object-cover">
                                        <div class="absolute top-3 right-3 bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-bold">
                                            Stock: <?php echo (int) $product['quantity']; ?>
                                        </div>
                                    </div>
                                    <div class="p-4 space-y-2">
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-sm truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                                            <p class="text-xs text-slate-600"><?php echo htmlspecialchars($product['category']); ?></p>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <p class="text-2xl font-bold text-red-600">₱<?php echo number_format($product['price'], 2); ?></p>
                                            <span class="text-xs font-semibold text-slate-500">Stock <?php echo (int) $product['quantity']; ?></span>
                                        </div>
                                        <button 
                                            class="w-full py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center justify-center space-x-2"
                                            onclick="addToCart(<?php echo (int) $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo (float) $product['price']; ?>, <?php echo (int) $product['quantity']; ?>)"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span>Add to Cart</span>
                                        </button>
                                    </div>
                                </div>
                            <?php endwhile;
                            } else {
                                echo '<div class="col-span-full text-center py-12"><p class="text-slate-600">No products found</p></div>';
                            }
                            ?>
                        </div>
                    </div>

                    <aside class="w-full lg:w-96 space-y-6">
                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Shopping Cart</h2>
                                    <p class="text-xs text-slate-500">Shortcuts: + add qty · - remove qty · Del remove item</p>
                                </div>
                                <span id="cartCount" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-sm font-semibold text-slate-700">0</span>
                            </div>
                            <div id="cartItems" class="space-y-4 max-h-[420px] overflow-y-auto pr-1">
                                <p class="text-slate-600 text-center py-8">Cart is empty</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-5">
                        <div class="space-y-4 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span>Subtotal</span>
                                <span id="subtotal" class="font-semibold text-slate-900">₱0.00</span>
                            </div>
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="discountAmount" class="block font-medium">Discount</label>
                                    <button onclick="clearCart()" type="button" class="text-xs text-slate-500 hover:text-red-600 transition">Clear cart</button>
                                </div>
                                <input type="number" min="0" step="0.01" id="discountAmount" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="0.00">
                                <p id="discountError" class="hidden text-xs text-red-600 mt-1">Discount cannot exceed subtotal.</p>
                            </div>
                            <div class="flex items-center justify-between text-base font-semibold text-slate-900 pt-2 border-t border-slate-200">
                                <span>Total</span>
                                <span id="total">₱0.00</span>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-500 mb-3">Payment Method</p>
                            <div class="grid grid-cols-2 gap-3" id="paymentButtons">
                                <button type="button" class="payment-method-btn p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left" data-method="Cash">
                                    <p class="text-sm font-semibold text-slate-900">Cash (F3)</p>
                                    <p class="text-xs text-slate-500">Counter payment</p>
                                </button>
                                <button type="button" class="payment-method-btn p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left" data-method="GCash">
                                    <p class="text-sm font-semibold text-slate-900">GCash (F4)</p>
                                    <p class="text-xs text-slate-500">QR payment</p>
                                </button>
                                <button type="button" class="payment-method-btn p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left col-span-2" data-method="Installment">
                                    <p class="text-sm font-semibold text-slate-900">Installment (F5)</p>
                                    <p class="text-xs text-slate-500">Auto-schedule dues</p>
                                </button>
                            </div>
                            <div id="installmentConfig" class="hidden mt-4 border border-dashed border-slate-200 rounded-lg p-3 bg-slate-50">
                                <label for="installmentMonths" class="block text-xs font-semibold text-slate-500 mb-2">Installment term</label>
                                <select id="installmentMonths" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
                                    <option value="3">3 months</option>
                                    <option value="6" selected>6 months</option>
                                    <option value="12">12 months</option>
                                </select>
                                <p class="text-xs text-slate-500 mt-2">Monthly dues are computed automatically after completion.</p>
                            </div>
                        </div>

                        <button id="completeSaleBtn" onclick="proceedToCheckout()" class="w-full py-3 bg-red-600 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>Complete Sale (F9)</span>
                        </button>
                        </div>
                    </aside>
                </div>
            <?php elseif ($tab === 'history'): ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-500">Transaction History</p>
                            <h2 class="text-2xl font-bold text-slate-900">Receipts & Daily Sales</h2>
                            <p class="text-sm text-slate-500">Review completed sales and reopen digital receipts.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($history_ranges as $key => $label): ?>
                                <a href="?tab=history&range=<?php echo $key; ?>"
                                   class="px-3 py-2 rounded-full text-xs font-semibold border transition <?php echo $selected_range === $key ? 'bg-red-600 text-white border-red-600 shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'; ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if ($history_result && $history_result->num_rows > 0): ?>
                        <div class="overflow-x-auto -mx-6 px-6">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                        <th class="py-3">Receipt #</th>
                                        <th class="py-3">Date & Time</th>
                                        <th class="py-3">Cashier</th>
                                        <th class="py-3">Items</th>
                                        <th class="py-3">Payment</th>
                                        <th class="py-3 text-right">Total</th>
                                        <th class="py-3 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php while ($history = $history_result->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="py-4 font-semibold text-slate-900">#<?php echo str_pad($history['transaction_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td class="py-4 text-sm text-slate-600"><?php echo date('M d, Y \\a\\t h:i A', strtotime($history['transaction_date'])); ?></td>
                                            <td class="py-4 text-sm text-slate-600"><?php echo htmlspecialchars($history['cashier_name'] ?? '—'); ?></td>
                                            <td class="py-4 text-sm text-slate-600"><?php echo (int) ($history['item_count'] ?? 0); ?></td>
                                            <td class="py-4">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $history['payment_type'] === 'Installment' ? 'bg-amber-100 text-amber-700' : ($history['payment_type'] === 'GCash' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                                    <?php echo htmlspecialchars($history['payment_type']); ?>
                                                </span>
                                            </td>
                                            <td class="py-4 text-right font-semibold text-slate-900">₱<?php echo number_format($history['total_amount'], 2); ?></td>
                                            <td class="py-4 text-right">
                                                <a href="receipt.php?transaction_id=<?php echo (int) $history['transaction_id']; ?>"
                                                   class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-full border border-slate-200 text-slate-700 hover:text-red-600 hover:border-red-200 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0A9 9 0 11.999 12a9 9 0 0120.001 0z" />
                                                    </svg>
                                                    View Receipt
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <p class="text-xs text-slate-500 mt-4">Showing up to the 50 most recent transactions for the selected range.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 text-slate-500">
                            <p class="text-sm font-semibold">No transactions found for this range.</p>
                            <p class="text-xs mt-1">Complete a sale and it will appear here instantly.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

        <script src="../../assets/js/pos.js"></script>
</body>
</html>
