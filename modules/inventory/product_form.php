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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/inventory.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="form-container">
        <div class="form-header">
            <h2><?php echo $product ? 'Edit Product' : 'Add New Product'; ?></h2>
            <a href="index.php?tab=products" class="btn-secondary">Back</a>
        </div>
        
        <form method="POST" action="process_product.php" class="form-box">
            <?php if ($product): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter product name"
                    value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Appliances" <?php echo ($product['category'] ?? '') === 'Appliances' ? 'selected' : ''; ?>>Appliances</option>
                    <option value="Furniture" <?php echo ($product['category'] ?? '') === 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Kitchenware" <?php echo ($product['category'] ?? '') === 'Kitchenware' ? 'selected' : ''; ?>>Kitchenware</option>
                    <option value="Household" <?php echo ($product['category'] ?? '') === 'Household' ? 'selected' : ''; ?>>Household</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input 
                    type="number" 
                    id="quantity" 
                    name="quantity" 
                    placeholder="Enter quantity"
                    value="<?php echo $product['quantity'] ?? 0; ?>"
                    min="0"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="price">Price (₱) *</label>
                <input 
                    type="number" 
                    id="price" 
                    name="price" 
                    placeholder="Enter price"
                    value="<?php echo $product['price'] ?? ''; ?>"
                    step="0.01"
                    min="0"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="Available" <?php echo ($product['status'] ?? 'Available') === 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Sold" <?php echo ($product['status'] ?? '') === 'Sold' ? 'selected' : ''; ?>>Sold</option>
                    <option value="Out of Stock" <?php echo ($product['status'] ?? '') === 'Out of Stock' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="shipment_id">Shipment (Optional)</label>
                <select id="shipment_id" name="shipment_id">
                    <option value="">No Shipment</option>
                    <?php while ($shipment = $shipments_result->fetch_assoc()): ?>
                        <option value="<?php echo $shipment['shipment_id']; ?>" <?php echo ($product['shipment_id'] ?? '') == $shipment['shipment_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($shipment['supplier']); ?> - <?php echo date('M d, Y', strtotime($shipment['date_received'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $product ? 'Update Product' : 'Create Product'; ?>
                </button>
                <a href="index.php?tab=products" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
