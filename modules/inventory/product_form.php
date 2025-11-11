<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$product_id = $_GET['id'] ?? null;
$product = null;

if ($product_id) {
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        header("Location: index.php?tab=products&error=Product not found");
        exit();
    }
}

// Get shipments for dropdown
$shipments_query = "SELECT shipment_id, supplier, date_received FROM shipments ORDER BY date_received DESC";
$shipments_result = $conn->query($shipments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? 'Edit' : 'Add'; ?> Product - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold text-slate-900 flex items-center space-x-3">
                <i class="fas fa-box text-emerald-600"></i>
                <span><?php echo $product ? 'Edit Product' : 'Add New Product'; ?></span>
            </h2>
            <a href="index.php?tab=products" class="flex items-center space-x-2 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
        
        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form method="POST" action="process_product.php" class="space-y-6">
                <?php if ($product): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>
                
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-tag mr-2 text-emerald-600"></i>Product Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter product name"
                        value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                        required
                    >
                </div>
                
                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-list mr-2 text-emerald-600"></i>Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" name="category" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        <option value="">Select a category</option>
                        <option value="Appliances" <?php echo ($product['category'] ?? '') === 'Appliances' ? 'selected' : ''; ?>>Appliances</option>
                        <option value="Furniture" <?php echo ($product['category'] ?? '') === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                        <option value="Kitchenware" <?php echo ($product['category'] ?? '') === 'Kitchenware' ? 'selected' : ''; ?>>Kitchenware</option>
                        <option value="Household" <?php echo ($product['category'] ?? '') === 'Household' ? 'selected' : ''; ?>>Household</option>
                    </select>
                </div>
                
                <!-- Quantity and Price Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-cubes mr-2 text-emerald-600"></i>Quantity <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="quantity" 
                            name="quantity" 
                            placeholder="Enter quantity"
                            value="<?php echo $product['quantity'] ?? 0; ?>"
                            min="0"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            required
                        >
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-peso-sign mr-2 text-emerald-600"></i>Price <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            placeholder="Enter price"
                            value="<?php echo $product['price'] ?? ''; ?>"
                            step="0.01"
                            min="0"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                            required
                        >
                    </div>
                </div>
                
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-circle-info mr-2 text-emerald-600"></i>Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent" required>
                        <option value="Available" <?php echo ($product['status'] ?? 'Available') === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Sold" <?php echo ($product['status'] ?? '') === 'Sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="Out of Stock" <?php echo ($product['status'] ?? '') === 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                
                <!-- Shipment -->
                <div>
                    <label for="shipment_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-truck mr-2 text-emerald-600"></i>Shipment (Optional)
                    </label>
                    <select id="shipment_id" name="shipment_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">No Shipment</option>
                        <?php while ($shipment = $shipments_result->fetch_assoc()): ?>
                            <option value="<?php echo $shipment['shipment_id']; ?>" <?php echo ($product['shipment_id'] ?? '') == $shipment['shipment_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($shipment['supplier']); ?> - <?php echo date('M d, Y', strtotime($shipment['date_received'])); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Form Actions -->
                <div class="flex gap-3 pt-6 border-t border-slate-200">
                    <button type="submit" class="flex-1 flex items-center justify-center space-x-2 bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition font-semibold">
                        <i class="fas fa-save"></i>
                        <span><?php echo $product ? 'Update Product' : 'Create Product'; ?></span>
                    </button>
                    <a href="index.php?tab=products" class="flex-1 flex items-center justify-center space-x-2 bg-slate-300 text-slate-700 px-6 py-3 rounded-lg hover:bg-slate-400 transition font-semibold">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
