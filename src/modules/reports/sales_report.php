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

<div class="sales-report space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center space-x-2">
            <i class="fas fa-filter text-blue-600"></i>
            <span>Filter Report</span>
        </h3>
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="tab" value="sales">
            
            <div>
                <label for="period" class="block text-sm font-semibold text-slate-700 mb-2">Period:</label>
                <select name="period" id="period" onchange="this.form.submit()" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="daily" <?php echo $period === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $period === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo $period === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="custom" <?php echo $period === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>
            </div>
            
            <?php if ($period === 'custom'): ?>
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700 mb-2">Start Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-semibold text-slate-700 mb-2">End Date:</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php else: ?>
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700 mb-2">Date:</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            <?php endif; ?>
            
            <div>
                <label for="payment_type" class="block text-sm font-semibold text-slate-700 mb-2">Payment Type:</label>
                <select name="payment_type" onchange="this.form.submit()" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="All">All</option>
                    <option value="Cash" <?php echo $payment_type === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="GCash" <?php echo $payment_type === 'GCash' ? 'selected' : ''; ?>>GCash</option>
                    <option value="Installment" <?php echo $payment_type === 'Installment' ? 'selected' : ''; ?>>Installment</option>
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex items-center space-x-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-semibold flex-1">
                    <i class="fas fa-search"></i>
                    <span>Apply</span>
                </button>
                <a href="export.php?type=sales&period=<?php echo $period; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&payment_type=<?php echo $payment_type; ?>" class="flex items-center space-x-2 bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 transition font-semibold">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 font-semibold">Total Sales</p>
                    <p class="text-4xl font-bold">₱<?php echo number_format($total_result['total_sales'] ?? 0, 2); ?></p>
                </div>
                <i class="fas fa-chart-line text-blue-200 text-5xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 font-semibold">Number of Transactions</p>
                    <p class="text-4xl font-bold"><?php echo $total_result['transaction_count'] ?? 0; ?></p>
                </div>
                <i class="fas fa-receipt text-purple-200 text-5xl opacity-50"></i>
            </div>
        </div>
    </div>
    
    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-slate-200">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Transaction ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Date & Time</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Cashier</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Items</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Payment Type</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-slate-700">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">#<?php echo str_pad($row['transaction_id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M d, Y H:i', strtotime($row['transaction_date'])); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($row['cashier_name']); ?></td>
                            <td class="px-6 py-4 text-sm"><span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-semibold"><?php echo $row['item_count']; ?></span></td>
                            <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($row['payment_type']); ?></td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900 text-right">₱<?php echo number_format($row['total_amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
