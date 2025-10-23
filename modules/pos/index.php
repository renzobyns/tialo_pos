<?php
include '../../includes/auth_check.php';
include '../../includes/db_connect.php';

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';

// Build query based on filters
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

// Get categories
$categories_query = "SELECT DISTINCT category FROM products WHERE status = 'Available' ORDER BY category";
$categories_result = $conn->query($categories_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Tialo Japan Surplus</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/pos.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="pos-container">
        <div class="pos-main">
            <!-- Product Catalog -->
            <div class="catalog-section">
                <div class="catalog-header">
                    <div class="search-box">
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Search surplus items... (F2)" 
                            value="<?php echo htmlspecialchars($search); ?>"
                        >
                        <button onclick="performSearch()" class="btn-search">Search</button>
                    </div>
                </div>
                
                <!-- Category Filters -->
                <div class="category-filters">
                    <a href="?category=All" class="category-btn <?php echo $category === 'All' ? 'active' : ''; ?>">
                        (1) All
                    </a>
                    <?php 
                    $count = 2;
                    while ($cat = $categories_result->fetch_assoc()): 
                    ?>
                        <a href="?category=<?php echo urlencode($cat['category']); ?>" 
                           class="category-btn <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                            (<?php echo $count; ?>) <?php echo htmlspecialchars($cat['category']); ?>
                        </a>
                    <?php 
                        $count++;
                    endwhile; 
                    ?>
                </div>
                
                <!-- Products Grid -->
                <div class="products-grid">
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="/placeholder.svg?height=200&width=200" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-category"><?php echo htmlspecialchars($product['category']); ?></p>
                                <p class="product-condition">Excellent</p>
                                <p class="product-price">₱<?php echo number_format($product['price'], 2); ?></p>
                                <p class="product-stock">Stock: <?php echo $product['quantity']; ?></p>
                                <button 
                                    class="btn-add-cart" 
                                    onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, <?php echo $product['quantity']; ?>)"
                                >
                                    + Add to Cart (Enter)
                                </button>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        
        <!-- Shopping Cart Sidebar -->
        <aside class="cart-sidebar">
            <div class="cart-header">
                <h2>Shopping Cart</h2>
                <span class="cart-count" id="cartCount">0</span>
            </div>
            
            <div class="cart-items" id="cartItems">
                <p class="empty-cart">Cart is empty</p>
            </div>
            
            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">₱0.00</span>
                </div>
                <div class="summary-row">
                    <span>Discount:</span>
                    <input type="number" id="discountAmount" placeholder="0.00" step="0.01" min="0" onchange="updateTotal()">
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span id="total">₱0.00</span>
                </div>
            </div>
            
            <div class="cart-actions">
                <button class="btn-checkout" onclick="proceedToCheckout()">Checkout</button>
                <button class="btn-clear" onclick="clearCart()">Clear Cart</button>
            </div>
        </aside>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script src="../../assets/js/pos.js"></script>
</body>
</html>
