<?php
// Get all shipments
$shipments_query = "SELECT * FROM shipments ORDER BY date_received DESC";
$shipments_result = $conn->query($shipments_query);
?>

<div class="shipments-section">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-2xl font-bold text-slate-900 flex items-center space-x-2">
            <i class="fas fa-truck text-blue-600"></i>
            <span>Shipments</span>
        </h3>
        <a href="shipment_form.php" class="flex items-center space-x-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
            <i class="fas fa-plus"></i>
            <span>Add Shipment</span>
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
    
    <!-- Shipments Table -->
    <div class="overflow-x-auto">
        <table class="w-full bg-white rounded-lg overflow-hidden shadow">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-200">
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Shipment ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Date Received</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Supplier</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Driver Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Total Boxes</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($shipment = $shipments_result->fetch_assoc()): ?>
                    <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                        <td class="px-6 py-4 text-sm text-slate-900 font-semibold">#<?php echo $shipment['shipment_id']; ?></td>
                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M d, Y', strtotime($shipment['date_received'])); ?></td>
                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($shipment['supplier']); ?></td>
                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($shipment['driver_name'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 text-sm"><span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold"><?php echo $shipment['total_boxes']; ?></span></td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="shipment_form.php?id=<?php echo $shipment['shipment_id']; ?>" class="inline-flex items-center space-x-1 bg-amber-100 text-amber-700 px-3 py-1 rounded hover:bg-amber-200 transition">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <a href="/index.php?page=inventory/process_shipment&action=delete&id=<?php echo $shipment['shipment_id']; ?>" class="inline-flex items-center space-x-1 bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition" onclick="return confirm('Delete this shipment?');">
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
