<?php
include __DIR__ . '/../../includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../../includes/db_connect.php';

$shipment_id = $_GET['id'] ?? null;
$shipment = null;
$is_edit = false;

if ($shipment_id) {
    $query = "SELECT * FROM shipments WHERE shipment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $shipment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $shipment = $result->fetch_assoc();
    
    if (!$shipment) {
        header("Location: index.php?tab=shipments&error=Shipment not found");
        exit();
    }
    $is_edit = true;
}
?>
<?php
$page_title = ($is_edit ? 'Edit' : 'Add') . ' Shipment - Tialo Japan Surplus';
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50 flex">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 page-header">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Logistics</p>
                    <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l3 9h8l3-7H9" />
                        </svg>
                        <?php echo $is_edit ? 'Update Shipment' : 'Add New Shipment'; ?>
                    </h1>
                    <p class="text-sm text-slate-600">Capture every incoming delivery before products move to inventory.</p>
                </div>
                <a href="/index.php?page=inventory&tab=shipments" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to shipments
                </a>
            </div>
        </header>

        <main class="flex-1 px-6 py-8">
            <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="border-b border-slate-100 px-8 py-5">
                    <h2 class="text-xl font-semibold text-slate-900">Shipment Details</h2>
                    <p class="text-sm text-slate-500 mt-1">Keep this record complete for traceability.</p>
                </div>
                <form method="POST" action="/index.php?page=inventory/process_shipment" class="px-8 py-6 space-y-6">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="shipment_id" value="<?php echo $shipment['shipment_id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="date_received" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                Date Received<span class="text-[#D00000]">*</span>
                            </label>
                            <input
                                type="date"
                                id="date_received"
                                name="date_received"
                                value="<?php echo $shipment['date_received'] ?? date('Y-m-d'); ?>"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]"
                                required
                            >
                        </div>
                        <div class="space-y-2">
                            <label for="time_received" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Time Received<span class="text-[#D00000]">*</span>
                            </label>
                            <input
                                type="time"
                                id="time_received"
                                name="time_received"
                                value="<?php echo $shipment['time_received'] ?? ''; ?>"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]"
                                required
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="supplier" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
                                Supplier<span class="text-[#D00000]">*</span>
                            </label>
                            <input
                                type="text"
                                id="supplier"
                                name="supplier"
                                placeholder="Enter supplier name"
                                value="<?php echo htmlspecialchars($shipment['supplier'] ?? ''); ?>"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]"
                                required
                            >
                        </div>
                        <div class="space-y-2">
                            <label for="driver_name" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 0a4 4 0 11-4-4 4 4 0 014 4zm0 0a4 4 0 104 4H8a4 4 0 004-4zm0 8v4" /></svg>
                                Driver's Name
                            </label>
                            <input
                                type="text"
                                id="driver_name"
                                name="driver_name"
                                placeholder="Enter driver name"
                                value="<?php echo htmlspecialchars($shipment['driver_name'] ?? ''); ?>"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-400"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="total_boxes" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10l9 4 9-4V7" /></svg>
                                Total Boxes<span class="text-[#D00000]">*</span>
                            </label>
                            <input
                                type="number"
                                id="total_boxes"
                                name="total_boxes"
                                placeholder="Enter number of boxes"
                                value="<?php echo $shipment['total_boxes'] ?? 0; ?>"
                                min="0"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]"
                                required
                            >
                        </div>
                        <div class="space-y-2">
                            <label for="cost" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#D00000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Total Investment Cost (₱)<span class="text-[#D00000]">*</span>
                            </label>
                            <input
                                type="number"
                                id="cost"
                                name="cost"
                                step="0.01"
                                placeholder="Enter total cost (Bidding + Delivery)"
                                value="<?php echo $shipment['cost'] ?? 0.00; ?>"
                                min="0"
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000]"
                                required
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="/index.php?page=inventory&tab=shipments" class="px-5 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-[#D00000] text-white font-semibold hover:bg-[#9D0208] transition">
                            <?php echo $is_edit ? 'Save Shipment' : 'Create Shipment'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
<?php include __DIR__ . '/../../includes/page_footer.php'; ?>
