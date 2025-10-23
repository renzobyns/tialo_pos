<?php
// Get all products
$products_query = "SELECT p.*, s.supplier FROM products p LEFT JOIN shipments s ON p.shipment_id = s.shipment_id ORDER BY p.product_id DESC";
$products_result = $conn->query($products_query);
?>

<div class="products-section">
    <div class="section-header">
        <h3>Products</h3>
        <a href="product_form.php" class="btn-primary">+ Add Product</a>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $product['product_id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                        <td><?php echo $product['quantity']; ?></td>
                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($product['status']); ?>">
                                <?php echo $product['status']; ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <a href="product_form.php?id=<?php echo $product['product_id']; ?>" class="btn-small btn-edit">Edit</a>
                            <a href="process_product.php?action=delete&id=<?php echo $product['product_id']; ?>" class="btn-small btn-danger" onclick="return confirm('Delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
