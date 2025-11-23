<?php
include __DIR__ . '/../../includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../../includes/db_connect.php';

$tab = $_GET['tab'] ?? 'inventory';
$product_search = trim($_GET['product_search'] ?? '');
$shipment_search = trim($_GET['shipment_search'] ?? '');

$products_sql = "SELECT product_id, name, category, quantity, price, status FROM products";
if ($product_search !== '') {
    $term = "%$product_search%";
    $stmt = $conn->prepare($products_sql . " WHERE name LIKE ? OR category LIKE ? ORDER BY product_id DESC");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query($products_sql . " ORDER BY product_id DESC");
}

$shipments_sql = "SELECT shipment_id, supplier, driver_name, total_boxes, date_received, time_received FROM shipments";
if ($shipment_search !== '') {
    $term = "%$shipment_search%";
    $stmt = $conn->prepare($shipments_sql . " WHERE supplier LIKE ? OR driver_name LIKE ? ORDER BY date_received DESC, time_received DESC");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $shipments = $stmt->get_result();
} else {
    $shipments = $conn->query($shipments_sql . " ORDER BY date_received DESC, time_received DESC");
}
?>
<?php
$page_title = 'Inventory Management - Tialo Japan Surplus';
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50 flex">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 page-header">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Operations</p>
                    <h1 class="text-3xl font-bold text-slate-900">Inventory Management</h1>
                    <p class="text-sm text-slate-600">Organize shipments and products ready for sale.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="?page=inventory/product_form" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition">Add Product</a>
                    <a href="?page=inventory/shipment_form" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">New Shipment</a>
                </div>
            </div>
            <div class="border-t border-slate-200 bg-slate-50">
                <div class="px-6 py-3 overflow-x-auto">
                    <div class="flex items-center gap-3 min-w-max">
                        <a href="?page=inventory&tab=inventory" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'inventory' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">Products</a>
                        <a href="?page=inventory&tab=shipments" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'shipments' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover-border-slate-200 hover:text-slate-900'; ?>">Shipments</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-6 py-6">
            <?php if ($tab === 'inventory'): ?>
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-6">
                    <form class="flex flex-col md:flex-row gap-4" method="GET">
                        <input type="hidden" name="page" value="inventory">
                        <input type="hidden" name="tab" value="inventory">
                        <input type="text" name="product_search" value="<?php echo htmlspecialchars($product_search); ?>" placeholder="Search by product name or category" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">Search</button>
                        <?php if ($product_search): ?>
                            <a href="?page=inventory&tab=inventory" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Reset</a>
                        <?php endif; ?>
                    </form>

                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 text-left">Product</th>
                                    <th class="px-6 py-3 text-left">Category</th>
                                    <th class="px-6 py-3 text-left">Quantity</th>
                                    <th class="px-6 py-3 text-left">Price</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if ($products && $products->num_rows): ?>
                                    <?php while ($product = $products->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-3 font-semibold text-slate-900">#<?php echo $product['product_id']; ?> · <?php echo htmlspecialchars($product['name']); ?></td>
                                            <td class="px-6 py-3 text-slate-600"><?php echo htmlspecialchars($product['category']); ?></td>
                                            <td class="px-6 py-3 text-slate-900 font-semibold"><?php echo (int)$product['quantity']; ?> pcs</td>
                                            <td class="px-6 py-3 text-slate-900 font-semibold">₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td class="px-6 py-3">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $product['status'] === 'Available' ? 'bg-emerald-50 text-emerald-700' : ($product['status'] === 'Sold' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'); ?>">
                                                    <?php echo $product['status']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-right space-x-2">
                                                <a href="?page=inventory/product_form&id=<?php echo $product['product_id']; ?>" class="text-blue-600 font-semibold hover:text-blue-800">Edit</a>
                                                <form action="/index.php?page=inventory/process_product" method="POST" class="inline" onsubmit="return confirm('Delete this product?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                    <button type="submit" class="text-red-600 font-semibold hover:text-red-800">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">No products found for the current filter.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($tab === 'shipments'): ?>
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-6">
                    <form class="flex flex-col md:flex-row gap-4" method="GET">
                        <input type="hidden" name="page" value="inventory">
                        <input type="hidden" name="tab" value="shipments">
                        <input type="text" name="shipment_search" value="<?php echo htmlspecialchars($shipment_search); ?>" placeholder="Search supplier or driver" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">Search</button>
                        <?php if ($shipment_search): ?>
                            <a href="?page=inventory&tab=shipments" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Reset</a>
                        <?php endif; ?>
                    </form>

                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 text-left">Shipment</th>
                                    <th class="px-6 py-3 text-left">Supplier</th>
                                    <th class="px-6 py-3 text-left">Driver</th>
                                    <th class="px-6 py-3 text-left">Total Boxes</th>
                                    <th class="px-6 py-3 text-left">Received</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if ($shipments && $shipments->num_rows): ?>
                                    <?php while ($shipment = $shipments->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-3 font-semibold text-slate-900">#SHIP-<?php echo str_pad($shipment['shipment_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                            <td class="px-6 py-3 text-slate-700"><?php echo htmlspecialchars($shipment['supplier']); ?></td>
                                            <td class="px-6 py-3 text-slate-600"><?php echo htmlspecialchars($shipment['driver_name']); ?></td>
                                            <td class="px-6 py-3 text-slate-900 font-semibold"><?php echo (int)$shipment['total_boxes']; ?> boxes</td>
                                            <td class="px-6 py-3 text-slate-500"><?php echo date('M d, Y', strtotime($shipment['date_received'])) . ' • ' . date('h:i A', strtotime($shipment['time_received'])); ?></td>
                                            <td class="px-6 py-3 text-right space-x-2">
                                                <a href="?page=inventory/shipment_form&id=<?php echo $shipment['shipment_id']; ?>" class="text-blue-600 font-semibold hover:text-blue-800">Open</a>
                                                <form action="/index.php?page=inventory/process_shipment" method="POST" class="inline" onsubmit="return confirm('Delete this shipment?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="shipment_id" value="<?php echo $shipment['shipment_id']; ?>">
                                                    <button type="submit" class="text-red-600 font-semibold hover:text-red-800">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="px-6 py-6 text-center text-slate-500">No shipment folders yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
<?php include __DIR__ . '/../../includes/page_footer.php'; ?>