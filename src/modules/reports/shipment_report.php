<?php
$view_shipment_id = isset($_GET['view_shipment']) ? (int)$_GET['view_shipment'] : 0;

if ($view_shipment_id > 0):
    // DETAIL VIEW
    // Fetch shipment info
    $stmt = $conn->prepare("SELECT * FROM shipments WHERE shipment_id = ?");
    $stmt->bind_param("i", $view_shipment_id);
    $stmt->execute();
    $shipment = $stmt->get_result()->fetch_assoc();

    if (!$shipment) {
        echo "<div class='p-4 bg-red-50 text-red-600 rounded-lg'>Shipment not found.</div>";
    } else {
        // Calculate totals for this shipment
        $stats_query = "SELECT 
            COALESCE(SUM(ti.subtotal), 0) AS total_revenue,
            COUNT(DISTINCT ti.item_id) AS items_sold
            FROM products p
            JOIN transaction_items ti ON p.product_id = ti.product_id
            WHERE p.shipment_id = ?";
        $stmt = $conn->prepare($stats_query);
        $stmt->bind_param("i", $view_shipment_id);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();

        $cost = (float)$shipment['cost'];
        $revenue = (float)$stats['total_revenue'];
        $profit = $revenue - $cost;
        $roi = $cost > 0 ? ($profit / $cost) * 100 : 0;

        // Fetch products
        $products_query = "SELECT 
            p.*, 
            COALESCE(SUM(ti.subtotal), 0) AS sold_amount
            FROM products p
            LEFT JOIN transaction_items ti ON p.product_id = ti.product_id
            WHERE p.shipment_id = ?
            GROUP BY p.product_id
            ORDER BY p.status = 'Sold' DESC, p.name ASC";
        $stmt = $conn->prepare($products_query);
        $stmt->bind_param("i", $view_shipment_id);
        $stmt->execute();
        $products = $stmt->get_result();
        ?>

        <div class="space-y-6">
            <!-- Header & Back -->
            <div class="flex items-center gap-4">
                <a href="?page=reports&tab=shipments" class="p-2 rounded-full hover:bg-slate-100 text-slate-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Shipment #<?php echo $shipment['shipment_id']; ?> Details</h2>
                    <p class="text-sm text-slate-500"><?php echo htmlspecialchars($shipment['supplier']); ?> • Received <?php echo date('M d, Y', strtotime($shipment['date_received'])); ?></p>
                </div>
            </div>

            <!-- Financial Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Investment (Cost)</p>
                    <p class="text-3xl font-bold text-slate-900"><?php echo peso($cost); ?></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Revenue (Sales)</p>
                    <p class="text-3xl font-bold text-emerald-600"><?php echo peso($revenue); ?></p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-6">
                    <p class="text-sm font-medium text-slate-500 mb-1">Net Profit / Loss</p>
                    <div class="flex items-end gap-2">
                        <p class="text-3xl font-bold <?php echo $profit >= 0 ? 'text-emerald-600' : 'text-red-600'; ?>">
                            <?php echo ($profit >= 0 ? '+' : '') . peso($profit); ?>
                        </p>
                        <span class="mb-1 text-sm font-semibold <?php echo $profit >= 0 ? 'text-emerald-700 bg-emerald-100' : 'text-red-700 bg-red-100'; ?> px-2 py-0.5 rounded">
                            <?php echo number_format($roi, 1); ?>% ROI
                        </span>
                    </div>
                </div>
            </div>

            <!-- Product Breakdown -->
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-semibold text-slate-900">Items in Shipment</h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">Product Name</th>
                            <th class="px-6 py-3 text-left">Category</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-right">Selling Price</th>
                            <th class="px-6 py-3 text-right">Sold Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php while ($prod = $products->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($prod['name']); ?></td>
                                <td class="px-6 py-3 text-slate-500"><?php echo htmlspecialchars($prod['category']); ?></td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        <?php 
                                        if ($prod['status'] === 'Sold') echo 'bg-slate-100 text-slate-800 line-through opacity-75';
                                        elseif ($prod['status'] === 'Available') echo 'bg-emerald-100 text-emerald-800';
                                        else echo 'bg-amber-100 text-amber-800'; 
                                        ?>">
                                        <?php echo $prod['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right text-slate-600"><?php echo peso($prod['price']); ?></td>
                                <td class="px-6 py-3 text-right font-semibold text-slate-900">
                                    <?php echo $prod['sold_amount'] > 0 ? peso($prod['sold_amount']) : '—'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

else:
    // LIST VIEW
    $query = "SELECT 
        s.shipment_id, 
        s.date_received, 
        s.supplier, 
        s.total_boxes, 
        s.cost AS shipment_cost,
        COALESCE(SUM(ti.subtotal), 0) AS total_revenue,
        COUNT(DISTINCT p.product_id) AS product_count,
        COUNT(DISTINCT ti.item_id) AS items_sold_count
    FROM shipments s
    LEFT JOIN products p ON s.shipment_id = p.shipment_id
    LEFT JOIN transaction_items ti ON p.product_id = ti.product_id
    GROUP BY s.shipment_id
    ORDER BY s.date_received DESC";

    $result = $conn->query($query);
    ?>

    <div class="bg-white border border-slate-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Shipment Profitability</h2>
                <p class="text-sm text-slate-500">Track investment vs. revenue for each cargo.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Supplier</th>
                        <th class="px-6 py-3 text-left">Boxes</th>
                        <th class="px-6 py-3 text-right">Investment (Cost)</th>
                        <th class="px-6 py-3 text-right">Revenue (Sales)</th>
                        <th class="px-6 py-3 text-right">Profit / Loss</th>
                        <th class="px-6 py-3 text-center">ROI</th>
                        <th class="px-6 py-3 text-center">Status</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($result->num_rows): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                $cost = (float)$row['shipment_cost'];
                                $revenue = (float)$row['total_revenue'];
                                $profit = $revenue - $cost;
                                $roi = $cost > 0 ? ($profit / $cost) * 100 : 0;
                                
                                // Determine status/color
                                $profit_class = $profit >= 0 ? 'text-emerald-600' : 'text-red-600';
                            ?>
                            <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="window.location='?page=reports&tab=shipments&view_shipment=<?php echo $row['shipment_id']; ?>'">
                                <td class="px-6 py-3 text-slate-900 font-medium">
                                    <?php echo date('M d, Y', strtotime($row['date_received'])); ?>
                                </td>
                                <td class="px-6 py-3 text-slate-600">
                                    <?php echo htmlspecialchars($row['supplier']); ?>
                                </td>
                                <td class="px-6 py-3 text-slate-600">
                                    <?php echo (int)$row['total_boxes']; ?>
                                </td>
                                <td class="px-6 py-3 text-right font-mono text-slate-600">
                                    <?php echo peso($cost); ?>
                                </td>
                                <td class="px-6 py-3 text-right font-mono text-slate-900 font-semibold">
                                    <?php echo peso($revenue); ?>
                                </td>
                                <td class="px-6 py-3 text-right font-mono font-bold <?php echo $profit_class; ?>">
                                    <?php echo peso($profit); ?>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $profit >= 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo number_format($roi, 1); ?>%
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <?php if ($profit > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            Profitable
                                        </span>
                                    <?php elseif ($revenue > 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            Recovering
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                            New / Unsold
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <a href="?page=reports&tab=shipments&view_shipment=<?php echo $row['shipment_id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium text-xs">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                No shipments found. Start by adding a shipment in the Inventory section.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>