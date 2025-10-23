<?php
// Get all shipments
$shipments_query = "SELECT * FROM shipments ORDER BY date_received DESC";
$shipments_result = $conn->query($shipments_query);
?>

<div class="shipments-section">
    <div class="section-header">
        <h3>Shipments</h3>
        <a href="shipment_form.php" class="btn-primary">+ Add Shipment</a>
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
                    <th>Shipment ID</th>
                    <th>Date Received</th>
                    <th>Supplier</th>
                    <th>Driver Name</th>
                    <th>Total Boxes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($shipment = $shipments_result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $shipment['shipment_id']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($shipment['date_received'])); ?></td>
                        <td><?php echo htmlspecialchars($shipment['supplier']); ?></td>
                        <td><?php echo htmlspecialchars($shipment['driver_name'] ?? 'N/A'); ?></td>
                        <td><?php echo $shipment['total_boxes']; ?></td>
                        <td class="action-buttons">
                            <a href="shipment_form.php?id=<?php echo $shipment['shipment_id']; ?>" class="btn-small btn-edit">Edit</a>
                            <a href="process_shipment.php?action=delete&id=<?php echo $shipment['shipment_id']; ?>" class="btn-small btn-danger" onclick="return confirm('Delete this shipment?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
