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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/inventory.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="form-container">
        <div class="form-header">
            <h2><?php echo $shipment ? 'Edit Shipment' : 'Add New Shipment'; ?></h2>
            <a href="index.php?tab=shipments" class="btn-secondary">Back</a>
        </div>
        
        <form method="POST" action="process_shipment.php" class="form-box">
            <?php if ($shipment): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="shipment_id" value="<?php echo $shipment['shipment_id']; ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="date_received">Date Received *</label>
                <input 
                    type="date" 
                    id="date_received" 
                    name="date_received" 
                    value="<?php echo $shipment['date_received'] ?? date('Y-m-d'); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="time_received">Time Received *</label>
                <input 
                    type="time" 
                    id="time_received" 
                    name="time_received" 
                    value="<?php echo $shipment['time_received'] ?? ''; ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="supplier">Supplier *</label>
                <input 
                    type="text" 
                    id="supplier" 
                    name="supplier" 
                    placeholder="Enter supplier name"
                    value="<?php echo htmlspecialchars($shipment['supplier'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="driver_name">Driver Name</label>
                <input 
                    type="text" 
                    id="driver_name" 
                    name="driver_name" 
                    placeholder="Enter driver name"
                    value="<?php echo htmlspecialchars($shipment['driver_name'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="total_boxes">Total Boxes *</label>
                <input 
                    type="number" 
                    id="total_boxes" 
                    name="total_boxes" 
                    placeholder="Enter number of boxes"
                    value="<?php echo $shipment['total_boxes'] ?? 0; ?>"
                    min="0"
                    required
                >
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $shipment ? 'Update Shipment' : 'Create Shipment'; ?>
                </button>
                <a href="index.php?tab=shipments" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
