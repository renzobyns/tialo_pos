<?php
// Get all products
$products_query = "SELECT p.*, s.supplier FROM products p LEFT JOIN shipments s ON p.shipment_id = s.shipment_id ORDER BY p.product_id DESC";
$products_result = $conn->query($products_query);
?>

<div class="products-section">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-bold text-slate-900 flex items-center space-x-2">
            <i class="fas fa-boxes text-emerald-600"></i>
            <span>Products</span>
        </h3>
        <a href="product_form.php" class="flex items-center space-x-2 bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition font-semibold">
            <i class="fas fa-plus"></i>
            <span>Add Product</span>
        </a>
    </div>
    
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded">
            <div class="flex items-center space-x-2">
                <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                <p class="text-emerald-800"><?php echo htmlspecialchars($_GET['success']); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-600 p-4 rounded">
            <div class="flex items-center space-x-2">
                <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                <p class="text-red-800"><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Products Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white rounded-lg overflow-hidden shadow">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-200">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Product ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Category</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Quantity</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Price</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm text-slate-900 font-semibold">#<?php echo $product['product_id']; ?></td>
                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($product['name']); ?></td>
                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($product['category'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 text-sm"><span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold"><?php echo $product['quantity']; ?></span></td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">₱<?php echo number_format($product['price'], 2); ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?php 
                            $status = $product['status'];
                            if ($status === 'Available') {
                                $badge_class = 'bg-emerald-100 text-emerald-800';
                                $icon = 'fa-check-circle';
                            } elseif ($status === 'Sold') {
                                $badge_class = 'bg-slate-100 text-slate-800';
                                $icon = 'fa-ban';
                            } else {
                                $badge_class = 'bg-red-100 text-red-800';
                                $icon = 'fa-exclamation-circle';
                            }
                            ?>
                            <span class="inline-flex items-center space-x-1 <?php echo $badge_class; ?> px-3 py-1 rounded-full font-semibold">
                                <i class="fas <?php echo $icon; ?>"></i>
                                <span><?php echo $status; ?></span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="product_form.php?id=<?php echo $product['product_id']; ?>" class="inline-flex items-center space-x-1 bg-amber-100 text-amber-700 px-3 py-1 rounded hover:bg-amber-200 transition">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <a href="process_product.php?action=delete&id=<?php echo $product['product_id']; ?>" class="inline-flex items-center space-x-1 bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition" onclick="return confirm('Delete this product?');">
                                <i class="fas fa-trash"></i>
                                <span>Delete</span>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
