<?php
include '../../includes/auth_check.php';
include '../../includes/db_connect.php';

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';

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
    <?php include '../../includes/tailwind-cdn.html'; ?>
</head>
<body class="bg-slate-100">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Products Section -->
            <div class="lg:col-span-3">
                <!-- Search and Filters -->
                <div class="mb-6">
                    <div class="bg-white rounded-xl shadow-md p-6 mb-4">
                        <div class="flex flex-col md:flex-row gap-4">
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Search surplus items... (F2)" 
                                value="<?php echo htmlspecialchars($search); ?>"
                                class="flex-1 px-4 py-3 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none"
                            >
                            <button onclick="performSearch()" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition flex items-center space-x-2">
                                <i class="fas fa-search"></i>
                                <span>Search</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Category Filters -->
                    <div class="flex flex-wrap gap-2 bg-white rounded-xl shadow-md p-4">
                        <a href="?category=All" class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo $category === 'All' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                            <i class="fas fa-th mr-1"></i>(1) All
                        </a>
                        <?php 
                        $count = 2;
                        while ($cat = $categories_result->fetch_assoc()): 
                        ?>
                            <a href="?category=<?php echo urlencode($cat['category']); ?>" 
                               class="px-4 py-2 rounded-full text-sm font-medium transition <?php echo $category === $cat['category'] ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'; ?>">
                                <i class="fas fa-filter mr-1"></i>(<?php echo $count; ?>) <?php echo htmlspecialchars($cat['category']); ?>
                            </a>
                        <?php 
                            $count++;
                        endwhile; 
                        ?>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php 
                    if ($products_result->num_rows > 0) {
                        while ($product = $products_result->fetch_assoc()): 
                    ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition transform hover:scale-105">
                            <div class="h-48 bg-slate-200 overflow-hidden relative">
                                <img src="/placeholder.svg?height=200&width=200" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="w-full h-full object-cover">
                                <div class="absolute top-3 right-3 bg-emerald-600 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    Stock: <?php echo $product['quantity']; ?>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="font-bold text-slate-900 mb-1 text-sm"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="text-xs text-slate-600 mb-2">
                                    <i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($product['category']); ?>
                                </p>
                                <p class="text-2xl font-bold text-emerald-600 mb-4">₱<?php echo number_format($product['price'], 2); ?></p>
                                <button 
                                    class="w-full py-2 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-semibold rounded-lg transition flex items-center justify-center space-x-2"
                                    onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['quantity']; ?>)"
                                >
                                    <i class="fas fa-plus"></i>
                                    <span>Add to Cart</span>
                                </button>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    } else {
                        echo '<div class="col-span-full text-center py-12"><i class="fas fa-inbox text-4xl text-slate-400 mb-4"></i><p class="text-slate-600">No products found</p></div>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Shopping Cart Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg sticky top-6 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-slate-900 to-slate-700 px-6 py-4 text-white flex justify-between items-center">
                        <h2 class="font-bold text-lg flex items-center space-x-2">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Cart</span>
                        </h2>
                        <span class="bg-emerald-500 text-white px-3 py-1 rounded-full font-bold" id="cartCount">0</span>
                    </div>
                    
                    <!-- Cart Items -->
                    <div class="max-h-96 overflow-y-auto p-4" id="cartItems">
                        <p class="text-slate-500 text-center py-8 text-sm">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            Cart is empty
                        </p>
                    </div>
                    
                    <!-- Summary -->
                    <div class="border-t border-slate-200 p-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-700">Subtotal:</span>
                            <span class="font-semibold text-slate-900" id="subtotal">₱0.00</span>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-700 block mb-2">
                                <i class="fas fa-percentage mr-1"></i>Discount (₱):
                            </label>
                            <input 
                                type="number" 
                                id="discountAmount" 
                                placeholder="0.00" 
                                step="0.01" 
                                min="0" 
                                onchange="updateTotal()"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:border-emerald-500 outline-none text-sm"
                            >
                        </div>
                        <div class="border-t border-slate-200 pt-3 flex justify-between">
                            <span class="font-bold text-slate-900">Total:</span>
                            <span class="text-2xl font-bold text-emerald-600" id="total">₱0.00</span>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="border-t border-slate-200 p-4 space-y-3">
                        <button 
                            class="w-full py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-bold rounded-lg transition flex items-center justify-center space-x-2"
                            onclick="proceedToCheckout()"
                        >
                            <i class="fas fa-credit-card"></i>
                            <span>Checkout</span>
                        </button>
                        <button 
                            class="w-full py-3 bg-slate-200 hover:bg-slate-300 text-slate-900 font-bold rounded-lg transition flex items-center justify-center space-x-2"
                            onclick="clearCart()"
                        >
                            <i class="fas fa-trash"></i>
                            <span>Clear Cart</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script src="../../assets/js/pos.js"></script>
</body>
</html>
