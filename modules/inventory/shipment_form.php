<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$shipment_id = $_GET['id'] ?? null;
$shipment = null;

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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $shipment ? 'Edit' : 'Add'; ?> Shipment - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold text-slate-900 flex items-center space-x-3">
                <i class="fas fa-truck text-blue-600"></i>
                <span><?php echo $shipment ? 'Edit Shipment' : 'Add New Shipment'; ?></span>
            </h2>
            <a href="index.php?tab=shipments" class="flex items-center space-x-2 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
        
        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form method="POST" action="process_shipment.php" class="space-y-6">
                <?php if ($shipment): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="shipment_id" value="<?php echo $shipment['shipment_id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>
                
                <!-- Date Received -->
                <div>
                    <label for="date_received" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Date Received <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="date_received" 
                        name="date_received" 
                        value="<?php echo $shipment['date_received'] ?? date('Y-m-d'); ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>
                
                <!-- Time Received -->
                <div>
                    <label for="time_received" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-clock mr-2 text-blue-600"></i>Time Received <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="time" 
                        id="time_received" 
                        name="time_received" 
                        value="<?php echo $shipment['time_received'] ?? ''; ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>
                
                <!-- Supplier -->
                <div>
                    <label for="supplier" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-building mr-2 text-blue-600"></i>Supplier <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="supplier" 
                        name="supplier" 
                        placeholder="Enter supplier name"
                        value="<?php echo htmlspecialchars($shipment['supplier'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>
                
                <!-- Driver Name -->
                <div>
                    <label for="driver_name" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-user-tie mr-2 text-blue-600"></i>Driver Name
                    </label>
                    <input 
                        type="text" 
                        id="driver_name" 
                        name="driver_name" 
                        placeholder="Enter driver name"
                        value="<?php echo htmlspecialchars($shipment['driver_name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                
                <!-- Total Boxes -->
                <div>
                    <label for="total_boxes" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-box mr-2 text-blue-600"></i>Total Boxes <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="total_boxes" 
                        name="total_boxes" 
                        placeholder="Enter number of boxes"
                        value="<?php echo $shipment['total_boxes'] ?? 0; ?>"
                        min="0"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                    >
                </div>
                
                <!-- Form Actions -->
                <div class="flex gap-3 pt-6 border-t border-slate-200">
                    <button type="submit" class="flex-1 flex items-center justify-center space-x-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                        <i class="fas fa-save"></i>
                        <span><?php echo $shipment ? 'Update Shipment' : 'Create Shipment'; ?></span>
                    </button>
                    <a href="index.php?tab=shipments" class="flex-1 flex items-center justify-center space-x-2 bg-slate-300 text-slate-700 px-6 py-3 rounded-lg hover:bg-slate-400 transition font-semibold">
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
