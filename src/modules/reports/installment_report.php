<?php
$status = $_GET['status'] ?? 'All';

$query = "SELECT i.installment_id, i.due_date, i.amount_due, i.balance_remaining, i.status, 
                 t.transaction_id, t.total_amount, t.transaction_date
          FROM installments i
          JOIN transactions t ON i.transaction_id = t.transaction_id
          WHERE 1=1";

if ($status !== 'All') {
    $status = sanitize($status);
    $query .= " AND i.status = '$status'";
}

$query .= " ORDER BY i.due_date ASC";
$result = $conn->query($query);

// Count overdue
$overdue_query = "SELECT COUNT(*) as overdue_count FROM installments WHERE status = 'Unpaid' AND due_date < CURDATE()";
$overdue_result = $conn->query($overdue_query)->fetch_assoc();
?>

<div class="installment-report">
    <div class="report-filters">
        <form method="GET" class="filter-form">
            <input type="hidden" name="tab" value="installments">
            
            <div class="filter-group">
                <label>Status:</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="All">All</option>
                    <option value="Paid" <?php echo $status === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                    <option value="Unpaid" <?php echo $status === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                </select>
            </div>
            
            <button type="submit" class="btn-filter">Apply Filter</button>
            <a href="export.php?type=installments&status=<?php echo $status; ?>" class="btn-export">Export PDF</a>
        </form>
    </div>
    
    <div class="alert-box">
        <strong>⚠️ Overdue Payments:</strong> <?php echo $overdue_result['overdue_count']; ?> installments are overdue
    </div>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Due Date</th>
                    <th>Amount Due</th>
                    <th>Balance Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="<?php echo $row['status'] === 'Unpaid' && strtotime($row['due_date']) < time() ? 'overdue' : ''; ?>">
                        <td>#<?php echo str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('M d, Y', strtotime($row['due_date'])); ?></td>
                        <td>₱<?php echo number_format($row['amount_due'], 2); ?></td>
                        <td>₱<?php echo number_format($row['balance_remaining'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
