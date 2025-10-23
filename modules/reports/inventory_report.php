<?php
$report_type = $_GET['report_type'] ?? 'current_stock';

if ($report_type === 'current_stock') {
    $query = "SELECT product_id, name, category, quantity, price, status FROM products ORDER BY name ASC";
    $result = $conn->query($query);
} elseif ($report_type === 'low_stock') {
    $query = "SELECT product_id, name, category, quantity, price, status FROM products WHERE quantity < 5 AND status = 'Available' ORDER BY quantity ASC";
    $result = $conn->query($query);
} elseif ($report_type === 'stock_movement') {
    $query = "SELECT p.name, SUM(ti.quantity) as total_sold, p.quantity as current_stock 
              FROM transaction_items ti
              JOIN products p ON ti.product_id = p.product_id
              GROUP BY p.product_id
              ORDER BY total_sold DESC";
    $result = $conn->query($query);
}
?>

<div class="inventory-report">
    <div class="report-filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="tab" value="inventory">
            
            <div class="filter-group">
                <label>Report Type:</label>
                <select name="report_type" onchange="this.form.submit()">
                    <option value="current_stock" <?php echo $report_type === 'current_stock' ? 'selected' : ''; ?>>Current Stock</option>
                    <option value="low_stock" <?php echo $report_type === 'low_stock' ? 'selected' : ''; ?>>Low Stock Alert</option>
                    <option value="stock_movement" <?php echo $report_type === 'stock_movement' ? 'selected' : ''; ?>>Stock Movement</option>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Apply Filter</button>
            <a href="export.php?type=inventory&report_type=<?php echo $report_type; ?>" class="btn-export">Export PDF</a>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <?php if ($report_type === 'stock_movement'): ?>
                        <th>Product</th>
                        <th>Total Sold</th>
                        <th>Current Stock</th>
                    <?php else: ?>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Status</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php if ($report_type === 'stock_movement'): ?>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo $row['total_sold']; ?></td>
                            <td><?php echo $row['current_stock']; ?></td>
                        <?php else: ?>
                            <td>#<?php echo $row['product_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td>₱<?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
