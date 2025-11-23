<?php
include __DIR__ . '/../../includes/auth_check.php';
include __DIR__ . '/../../includes/db_connect.php';

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';
$tab = $_GET['tab'] ?? 'catalog';
$allowed_tabs = ['catalog', 'history'];
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'catalog';
}
$sort = $_GET['sort'] ?? 'default';
$low_stock_threshold = 5;
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

if ($sort === 'lowstock') {
    $query .= " ORDER BY (quantity <= {$low_stock_threshold}) DESC, product_id DESC";
} else {
    $query .= " ORDER BY product_id DESC";
}
$products_result = $conn->query($query);

$categories_query = "SELECT DISTINCT category FROM products WHERE status = 'Available' ORDER BY category";
$categories_result = $conn->query($categories_query);

$product_names = [];
if ($products_result && $products_result->num_rows > 0) {
    $products_result->data_seek(0);
    while ($p = $products_result->fetch_assoc()) {
        $product_names[] = [
            'name' => $p['name'],
            'category' => $p['category'],
            'id' => $p['product_id']
        ];
    }
    $products_result->data_seek(0);
}

$img_base_url = '../../assets/img/products/';
$placeholder = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="320" height="240"><rect width="320" height="240" fill="#e5e7eb"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="18">No image</text></svg>');

$history_result = null;
$history_search = '';
$history_payment = 'all';
if ($tab === 'history') {
    $history_search = isset($_GET['history_search']) ? trim($_GET['history_search']) : '';
    $history_payment = $_GET['history_payment'] ?? 'all';
    $range_filters = [
        'today' => "DATE(t.transaction_date) = CURDATE()",
        'week' => "YEARWEEK(t.transaction_date, 1) = YEARWEEK(CURDATE(), 1)",
        'month' => "DATE_FORMAT(t.transaction_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')",
        'all' => "1=1"
    ];
    $where_clause = $range_filters[$selected_range] ?? $range_filters['today'];
    $params = [];
    $types = '';
    $payment_filter = '';
    if (in_array($history_payment, ['Cash', 'GCash', 'Installment'], true)) {
        $payment_filter = " AND t.payment_type = ?";
        $types .= 's';
        $params[] = $history_payment;
    }
    $search_filter = '';
    if ($history_search !== '') {
        $search_filter = " AND (u.name LIKE ? OR t.transaction_id LIKE ?)";
        $types .= 'ss';
        $like = '%' . $history_search . '%';
        $params[] = $like;
        $params[] = $like;
    }
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
        WHERE {$where_clause} {$payment_filter} {$search_filter}
        ORDER BY t.transaction_date DESC
        LIMIT 100
    ";
    $history_stmt = $conn->prepare($history_query);
    if ($types) {
        $history_stmt->bind_param($types, ...$params);
    }
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
}
?>
<?php
$page_title = 'POS - Tialo Japan Surplus';
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50 flex min-h-screen">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-semibold text-slate-900">Tialo Japan Surplus</h1>
                        <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold uppercase tracking-wide">POS System</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Press F1 for shortcuts · Keep catalog, cart, and checkout visible together.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="?page=pos&tab=catalog" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'catalog' ? 'bg-[#D00000] text-white border border-[#D00000] shadow' : 'bg-white text-slate-600 border border-slate-200 hover:text-slate-900'; ?>">
                        Catalog
                    </a>
                    <a href="?page=pos&tab=history" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'history' ? 'bg-[#D00000] text-white border border-[#D00000] shadow' : 'bg-white text-slate-600 border border-slate-200 hover:text-slate-900'; ?>">
                        History
                    </a>
                </div>
            </div>
        </header>
        
        <main class="flex-1 px-6 pt-4 pb-4 bg-slate-50 overflow-auto">
            <?php if ($tab === 'catalog'): ?>
                <div class="flex flex-col lg:flex-row gap-4 min-h-[calc(100vh-140px)] mt-2">
                    <div class="flex-1 flex flex-col gap-3 overflow-y-auto pb-4">
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="searchInput" 
                                        placeholder="Search surplus items... (F2)" 
                                        value="<?php echo htmlspecialchars($search); ?>"
                                        class="w-full h-12 rounded-full border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-[#D00000]"
                                    >
                                    <div id="searchSuggestions" class="absolute top-full left-0 right-0 mt-2 hidden z-30">
                                        <div class="bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden">
                                            <ul class="divide-y divide-slate-100 text-sm max-h-64 overflow-y-auto"></ul>
                                        </div>
                                    </div>
                                </div>
                                <button id="searchBtn" onclick="performSearch()" class="h-12 px-5 rounded-full bg-[#D00000] text-white text-sm font-semibold flex items-center gap-2 hover:bg-red-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                    <span>Search</span>
                                </button>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 mt-4 text-sm">
                                <a href="?page=pos&category=All&tab=catalog&sort=<?php echo htmlspecialchars($sort); ?>" class="category-pill px-4 py-2 rounded-full border <?php echo $category === 'All' ? 'bg-[#D00000] text-white border-[#D00000]' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'; ?>" data-category="All">
                                    All Items
                                </a>
                                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                    <a href="?page=pos&category=<?php echo urlencode($cat['category']); ?>&tab=catalog&sort=<?php echo htmlspecialchars($sort); ?>" 
                                       class="category-pill px-4 py-2 rounded-full border <?php echo $category === $cat['category'] ? 'bg-[#D00000] text-white border-[#D00000]' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'; ?>"
                                       data-category="<?php echo htmlspecialchars($cat['category']); ?>">
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </a>
                                <?php endwhile; ?>
                                <div class="flex items-center gap-2 ml-auto">
                                    <span class="text-xs text-slate-500">Sort:</span>
                                    <a href="?page=pos&tab=catalog&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=default"
                                       class="px-3 py-2 rounded-full text-xs font-semibold border <?php echo $sort === 'default' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200'; ?>">Default</a>
                                    <a href="?page=pos&tab=catalog&category=<?php echo urlencode($category); ?>&search=<?php echo urlencode($search); ?>&sort=lowstock"
                                       class="px-3 py-2 rounded-full text-xs font-semibold border <?php echo $sort === 'lowstock' ? 'bg-[#D00000] text-white border-[#D00000]' : 'bg-white text-slate-600 border-slate-200'; ?>">Low stock first</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto pr-1 pb-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4 pb-6">
                                <?php 
                                if ($products_result && $products_result->num_rows > 0) {
                                while ($product = $products_result->fetch_assoc()):
                                    $raw_image = trim($product['image'] ?? '');
                                    $img_src = $placeholder;
                                    if ($raw_image) {
                                        if (preg_match('/^https?:\\/\\//i', $raw_image)) {
                                            $img_src = $raw_image;
                                        } else {
                                            $basename = basename(str_replace('\\', '/', $raw_image));
                                            if ($basename && preg_match('/\\.(jpe?g|png|webp)$/i', $basename)) {
                                                $abs = __DIR__ . '/../../assets/img/products/' . $basename;
                                                $version = is_file($abs) ? '?v=' . filemtime($abs) : '';
                                                $img_src = $img_base_url . $basename . $version;
                                            }
                                        }
                                    }
                                    ?>
                                <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-lg hover:-translate-y-1 transition flex flex-col gap-4">
                                    <div class="aspect-video rounded-xl bg-slate-100 overflow-hidden">
                                        <img src="<?php echo $img_src; ?>" 
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="w-full h-full object-cover" loading="lazy" style="max-height: 260px;">
                                    </div>
                                    <div class="space-y-1">
                                            <div class="flex items-center justify-between text-[11px] uppercase tracking-wide text-slate-500">
                                                <span><?php echo htmlspecialchars($product['category'] ?: 'Uncategorized'); ?></span>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full <?php echo (int)$product['quantity'] <= $low_stock_threshold ? 'bg-amber-100 text-amber-700 font-semibold' : 'bg-slate-100 text-slate-600'; ?>">
                                                    <?php if ((int)$product['quantity'] <= $low_stock_threshold): ?>
                                                        Low stock
                                                    <?php else: ?>
                                                        Stock: <?php echo (int) $product['quantity']; ?>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                            <h3 class="text-base font-semibold text-slate-900 truncate"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <p class="text-2xl font-bold text-slate-900">₱<?php echo number_format($product['price'], 2); ?></p>
                                        <span class="text-xs text-slate-500">ID <?php echo (int) $product['product_id']; ?></span>
                                    </div>
                                    <button 
                                        class="mt-auto inline-flex items-center justify-center gap-2 h-11 rounded-2xl bg-slate-900 text-white text-sm font-semibold hover:bg-black transition"
                                        onclick="addToCart(<?php echo (int) $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', <?php echo (float) $product['price']; ?>, <?php echo (int) $product['quantity']; ?>)"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
                            <?php endwhile;
                            } else {
                                echo '<div class="col-span-full text-center py-12 text-slate-600">No products found</div>';
                            }
                            ?>
                            </div>
                        </div>
                    </div>

                    <aside class="w-full lg:w-[320px] xl:w-[340px] shrink-0">
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex flex-col gap-4 lg:sticky lg:top-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">Shopping Cart</h2>
                                    <p class="text-xs text-slate-500">Quick: + add qty · - remove qty · Del remove item</p>
                                </div>
                                <span id="cartCount" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-900 text-white text-sm font-semibold">0</span>
                            </div>
                            <div id="cartItems" class="space-y-3 max-h-[50vh] overflow-y-auto pr-1">
                                <p class="text-slate-500 text-sm text-center py-8">Cart is empty</p>
                            </div>
                            <div class="space-y-4 border-t border-slate-100 pt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Subtotal</span>
                                    <span id="subtotal" class="font-semibold text-slate-900">₱0.00</span>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label for="discountAmount" class="text-sm font-medium text-slate-700">Discount</label>
                                        <button onclick="clearCart()" type="button" class="text-xs text-slate-400 hover:text-red-600 transition">Clear cart</button>
                                    </div>
                                    <input type="number" min="0" step="0.01" id="discountAmount" class="w-full h-11 px-3 rounded-2xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000] text-sm" placeholder="0.00">
                                    <p id="discountError" class="hidden text-xs text-red-600 mt-1">Discount cannot exceed subtotal.</p>
                                </div>
                                <div class="flex items-center justify-between text-lg font-semibold text-slate-900">
                                    <span class="flex items-center gap-2">Total <span id="discountBadge" class="hidden text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Discount applied</span></span>
                                    <span id="total">₱0.00</span>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <p class="text-xs uppercase font-semibold text-slate-500">Payment Method</p>
                                <div class="flex flex-wrap gap-2" id="paymentButtons">
                                    <button type="button" class="payment-method-btn flex-1 min-w-[110px] px-4 py-3 rounded-2xl border border-slate-200 text-left hover:border-[#D00000] hover:bg-red-50 transition" data-method="Cash">
                                        <p class="text-sm font-semibold text-slate-900">Cash (F3)</p>
                                        <p class="text-xs text-slate-500">Counter payment</p>
                                    </button>
                                    <button type="button" class="payment-method-btn flex-1 min-w-[110px] px-4 py-3 rounded-2xl border border-slate-200 text-left hover:border-[#D00000] hover:bg-red-50 transition" data-method="GCash">
                                        <p class="text-sm font-semibold text-slate-900">GCash (F4)</p>
                                        <p class="text-xs text-slate-500">QR payment</p>
                                    </button>
                                    <button type="button" class="payment-method-btn w-full px-4 py-3 rounded-2xl border border-slate-200 text-left hover:border-[#D00000] hover:bg-red-50 transition" data-method="Installment">
                                        <p class="text-sm font-semibold text-slate-900">Installment (F5)</p>
                                        <p class="text-xs text-slate-500">Auto-schedule dues</p>
                                    </button>
                                </div>
                                <div id="installmentConfig" class="hidden border border-dashed border-slate-200 rounded-2xl p-3 bg-slate-50">
                                    <label for="installmentMonths" class="block text-xs font-semibold text-slate-500 mb-2">Installment term</label>
                                    <select id="installmentMonths" class="w-full h-10 px-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900 text-sm">
                                        <option value="3">3 months</option>
                                        <option value="6" selected>6 months</option>
                                        <option value="12">12 months</option>
                                    </select>
                                    <p class="text-xs text-slate-500 mt-2">Monthly dues are computed automatically after completion.</p>
                                </div>
                            </div>

                            <button id="completeSaleBtn" onclick="proceedToCheckout(this)" data-checkout-url="/index.php?page=pos/complete_sale" class="w-full h-12 rounded-2xl bg-[#D00000] hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span>Complete Sale (F9)</span>
                            </button>
                        </div>
                    </aside>
                </div>
            <?php elseif ($tab === 'history'): ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm h-full overflow-y-auto">
                    <div class="flex flex-col gap-4 mb-6">
                        <div class="flex flex-col gap-2">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Transaction History</p>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-2xl font-bold text-slate-900">Receipts & Daily Sales</h2>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($history_ranges as $key => $label): ?>
                                        <a href="?page=pos&tab=history&range=<?php echo $key; ?>"
                                           class="px-3 py-2 rounded-full text-xs font-semibold border transition <?php echo $selected_range === $key ? 'bg-red-600 text-white border-red-600 shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300'; ?>">
                                            <?php echo htmlspecialchars($label); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <p class="text-sm text-slate-500">Filter by cashier, receipt #, payment type, and date range.</p>
                        </div>

                        <form method="get" class="flex flex-col md:flex-row md:items-center gap-3 bg-slate-50 border border-slate-200 rounded-2xl p-4">
                            <input type="hidden" name="tab" value="history">
                            <input type="hidden" name="range" value="<?php echo htmlspecialchars($selected_range); ?>">
                            <div class="relative flex-1 min-w-[220px]">
                                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                            <input type="text" name="history_search" value="<?php echo htmlspecialchars($history_search); ?>" placeholder="Search receipts or cashier…" class="w-full h-11 rounded-full border border-slate-200 bg-white pl-10 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#D00000]">
                        </div>
                        <select name="history_payment" class="h-11 px-3 rounded-full border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#D00000]">
                            <option value="all" <?php echo $history_payment === 'all' ? 'selected' : ''; ?>>All payments</option>
                            <option value="Cash" <?php echo $history_payment === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="GCash" <?php echo $history_payment === 'GCash' ? 'selected' : ''; ?>>GCash</option>
                                <option value="Installment" <?php echo $history_payment === 'Installment' ? 'selected' : ''; ?>>Installment</option>
                            </select>
                            <button type="submit" class="h-11 px-5 rounded-full bg-[#D00000] text-white text-sm font-semibold hover:bg-red-700 transition">Filter</button>
                        </form>
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

        <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<?php
$page_scripts = '
        <script src="/assets/js/pos.js"></script>
        <script>
        window.POS_PRODUCTS = ' . json_encode($product_names) . ';
        function performSearch() {
            const input = document.getElementById(\'searchInput\');
            if (!input) return;
            const params = new URLSearchParams(window.location.search);
            const value = input.value.trim();
            if (value) {
                params.set(\'search\', value);
            } else {
                params.delete(\'search\');
            }
            params.set(\'tab\', \'catalog\');
            window.location.href = \'?page=pos&\' + params.toString();
        }
        const searchInput = document.getElementById(\'searchInput\');
        if (searchInput) {
            searchInput.addEventListener(\'keydown\', function (event) {
                if (event.key === \'Enter\') {
                    event.preventDefault();
                    performSearch();
                }
            });
        }
        </script>';
include __DIR__ . '/../../includes/page_footer.php';
?>
