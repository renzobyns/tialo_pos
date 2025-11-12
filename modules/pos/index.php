<?php
include '../../includes/auth_check.php';
include '../../includes/db_connect.php';

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';
$tab = $_GET['tab'] ?? 'catalog';

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
                        <a href="?tab=receipts" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'receipts' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-slate-200 hover:text-slate-900'; ?>">
                            Receipts
                        </a>
                        <a href="?tab=returns" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'returns' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-slate-200 hover:text-slate-900'; ?>">
                            Returns
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
                            <div class="space-y-3 text-sm text-slate-600">
                                <div class="flex items-center justify-between">
                                    <span>Subtotal</span>
                                    <span id="subtotal" class="font-semibold text-slate-900">₱0.00</span>
                                </div>
                                <div>
                                    <label for="discountAmount" class="block mb-1 font-medium">Discount</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.01" id="discountAmount" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="0.00">
                                        <button onclick="clearCart()" type="button" class="px-3 py-2 text-sm text-slate-500 hover:text-red-600 transition">Clear</button>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-base font-semibold text-slate-900 pt-2 border-t border-slate-200">
                                    <span>Total</span>
                                    <span id="total">₱0.00</span>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs uppercase font-semibold text-slate-500 mb-3">Payment Method</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <button class="p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left">
                                        <p class="text-sm font-semibold text-slate-900">Cash (F3)</p>
                                        <p class="text-xs text-slate-500">Counter payment</p>
                                    </button>
                                    <button class="p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left">
                                        <p class="text-sm font-semibold text-slate-900">Card (F4)</p>
                                        <p class="text-xs text-slate-500">Credit/Debit</p>
                                    </button>
                                    <button class="p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left">
                                        <p class="text-sm font-semibold text-slate-900">GCash (F5)</p>
                                        <p class="text-xs text-slate-500">QR payment</p>
                                    </button>
                                    <button class="p-3 rounded-lg border border-slate-200 hover:border-red-600 hover:bg-red-50 transition text-left">
                                        <p class="text-sm font-semibold text-slate-900">Split</p>
                                        <p class="text-xs text-slate-500">Multi method</p>
                                    </button>
                                </div>
                            </div>

                            <button onclick="proceedToCheckout()" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                Complete Sale (F9)
                            </button>
                        </div>
                    </aside>
                </div>
            <?php elseif ($tab === 'receipts'): ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-xl font-bold mb-6">Transaction Receipts</h2>
                    <p class="text-slate-600">Recent receipts will appear here</p>
                </div>
            <?php elseif ($tab === 'returns'): ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-xl font-bold mb-6">Returns & Adjustments</h2>
                    <p class="text-slate-600">Process item returns and exchanges.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

        <script src="../../assets/js/pos.js"></script>
</body>
</html>
