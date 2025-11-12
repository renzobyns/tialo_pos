<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$product_id = $_GET['id'] ?? null;
$product = null;
$is_edit = false;

if ($product_id) {
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        header("Location: index.php?tab=inventory&error=Product not found");
        exit();
    }
    $is_edit = true;
}

$shipments_query = "SELECT shipment_id, supplier, date_received FROM shipments ORDER BY date_received DESC";
$shipments_result = $conn->query($shipments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Product - Tialo Japan Surplus</title>
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
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Catalog</p>
                    <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10l9 4 9-4V7" />
                        </svg>
                        <?php echo $is_edit ? 'Update Product' : 'Add New Product'; ?>
                    </h1>
                    <p class="text-sm text-slate-600">Maintain the items available for POS checkout.</p>
                </div>
                <a href="index.php?tab=inventory" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to inventory
                </a>
            </div>
        </header>

        <main class="flex-1 px-6 py-8">
            <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="border-b border-slate-100 px-8 py-5">
                    <h2 class="text-xl font-semibold text-slate-900">Product Details</h2>
                    <p class="text-sm text-slate-500 mt-1">All required fields are marked with a red badge.</p>
                </div>
                <form method="POST" action="process_product.php" class="px-8 py-6 space-y-6">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <div class="space-y-2">
                        <label for="name" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.508 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Product Name<span class="text-[#D00000]">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" placeholder="e.g. Rice Cooker" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="category" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                Category<span class="text-[#D00000]">*</span>
                            </label>
                            <select id="category" name="category" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]" required>
                                <option value="" disabled <?php echo empty($product['category']) ? 'selected' : ''; ?>>Select category</option>
                                <?php
                                    $categories = ['Appliances','Furniture','Kitchenware','Household'];
                                    foreach ($categories as $cat):
                                ?>
                                    <option value="<?php echo $cat; ?>" <?php echo ($product['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="status" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l3 3" /></svg>
                                Status<span class="text-[#D00000]">*</span>
                            </label>
                            <select id="status" name="status" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]" required>
                                <?php
                                    $statuses = ['Available','Sold','Out of Stock'];
                                    foreach ($statuses as $status):
                                ?>
                                    <option value="<?php echo $status; ?>" <?php echo ($product['status'] ?? 'Available') === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="quantity" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                Quantity<span class="text-[#D00000]">*</span>
                            </label>
                            <input type="number" id="quantity" name="quantity" value="<?php echo (int)($product['quantity'] ?? 0); ?>" min="0" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]" required>
                        </div>
                        <div class="space-y-2">
                            <label for="price" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2-4-.895-4-2" /></svg>
                                Price (₱)<span class="text-[#D00000]">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]" required>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="shipment_id" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10l9 4 9-4V7" /></svg>
                            Shipment Folder (optional)
                        </label>
                        <select id="shipment_id" name="shipment_id" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#9D0208]">
                            <option value="">No shipment</option>
                            <?php if ($shipments_result && $shipments_result->num_rows): ?>
                                <?php while ($shipment = $shipments_result->fetch_assoc()): ?>
                                    <option value="<?php echo $shipment['shipment_id']; ?>" <?php echo ($product['shipment_id'] ?? '') == $shipment['shipment_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($shipment['supplier']); ?> - <?php echo date('M d, Y', strtotime($shipment['date_received'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="index.php?tab=inventory" class="px-5 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-[#D00000] text-white font-semibold hover:bg-[#9D0208] transition">
                            <?php echo $is_edit ? 'Save Product' : 'Create Product'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
