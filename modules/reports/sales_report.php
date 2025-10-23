<?php
$period = $_GET['period'] ?? 'daily';
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$payment_type = $_GET['payment_type'] ?? 'All';

// Build query
$query = "SELECT t.transaction_id, t.transaction_date, t.payment_type, t.total_amount, u.name as cashier_name, COUNT(ti.item_id) as item_count
          FROM transactions t
          JOIN users u ON t.user_id = u.user_id
          LEFT JOIN transaction_items ti ON t.transaction_id = ti.transaction_id
          WHERE 1=1";

if ($period === 'daily') {
    $query .= " AND DATE(t.transaction_date) = '$start_date'";
} elseif ($period === 'weekly') {
    $query .= " AND WEEK(t.transaction_date) = WEEK('$start_date')";
} elseif ($period === 'monthly') {
    $query .= " AND MONTH(t.transaction_date) = MONTH('$start_date') AND YEAR(t.transaction_date) = YEAR('$start_date')";
} elseif ($period === 'custom') {
    $query .= " AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
}

if ($payment_type !== 'All') {
    $payment_type = sanitize($payment_type);
    $query .= " AND t.payment_type = '$payment_type'";
}

$query .= " GROUP BY t.transaction_id ORDER BY t.transaction_date DESC";
$result = $conn->query($query);

// Calculate totals
$total_query = "SELECT SUM(total_amount) as total_sales, COUNT(*) as transaction_count FROM transactions t WHERE 1=1";
if ($period === 'daily') {
    $total_query .= " AND DATE(t.transaction_date) = '$start_date'";
} elseif ($period === 'custom') {
    $total_query .= " AND DATE(t.transaction_date) BETWEEN '$start_date' AND '$end_date'";
}
if ($payment_type !== 'All') {
    $total_query .= " AND t.payment_type = '$payment_type'";
}
$total_result = $conn->query($total_query)->fetch_assoc();
?>

<div class="sales-report">
    <div class="report-filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="tab" value="sales">
            
            <div class="filter-group">
                <label>Period:</label>
                <select name="period" onchange="this.form.submit()">
                    <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $period === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>
            
            <?php if ($period === 'custom'): ?>
                <div class="filter-group">
                    <label>Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="filter-group">
                    <label>End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
            <?php else: ?>
                <div class="filter-group">
                    <label>Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
            <?php endif; ?>
            
            <div class="filter-group">
                <label>Payment Type:</label>
                <select name="payment_type" onchange="this.form.submit()">
                    <option value="All">All</option>
                    <option value="Cash" <?php echo $payment_type === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="GCash" <?php echo $payment_type === 'GCash' ? 'selected' : ''; ?>>GCash</option>
                    <option value="Installment" <?php echo $payment_type === 'Installment' ? 'selected' : ''; ?>>Installment</option>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Apply Filter</button>
            <a href="export.php?type=sales&period=<?php echo $period; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&payment_type=<?php echo $payment_type; ?>" class="btn-export">Export PDF</a>
        </form>
    </div>
    
    <div class="report-summary">
        <div class="summary-card">
            <h4>Total Sales</h4>
            <p class="summary-value">₱<?php echo number_format($total_result['total_sales'] ?? 0, 2); ?></p>
        </div>
        <div class="summary-card">
            <h4>Transactions</h4>
            <p class="summary-value"><?php echo $total_result['transaction_count'] ?? 0; ?></p>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Date & Time</th>
                    <th>Cashier</th>
                    <th>Items</th>
                    <th>Payment Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($row['transaction_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                        <td><?php echo $row['item_count']; ?></td>
                        <td><?php echo htmlspecialchars($row['payment_type']); ?></td>
                        <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
