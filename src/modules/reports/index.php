<?php
include __DIR__ . '/../../includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../../includes/db_connect.php';
$tab = $_GET['tab'] ?? 'sales';
$period = $_GET['period'] ?? 'today';
$payment_type = $_GET['payment'] ?? 'all';
$staff_id = isset($_GET['staff']) ? (int)$_GET['staff'] : 0;
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';
$today = date('Y-m-d');
$start_date = $today;
$end_date = date('Y-m-d');
switch ($period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        break;
    case 'month':
        $start_date = date('Y-m-01');
        break;
    case 'all':
        $start_date = '1970-01-01';
        break;
    case 'custom':
        if ($custom_start && $custom_end) {
            $start_date = $custom_start;
            $end_date = $custom_end;
        }
        break;
    default: // today
        $period = 'today';
}
$staff_options = $conn->query("SELECT user_id, name FROM users ORDER BY name ASC");
$where = "DATE(transaction_date) BETWEEN '$start_date' AND '$end_date'";
if ($period === 'all') {
    $where = '1=1'; // For all time, don't filter by date
}
if (in_array($payment_type, ['Cash', 'GCash', 'Installment'], true)) {
    $where .= " AND payment_type = '" . $conn->real_escape_string($payment_type) . "'";
}
if ($staff_id > 0) {
    $where .= " AND user_id = " . (int)$staff_id;
}
$overview = $conn->query("SELECT 
    COALESCE(SUM(total_amount),0) AS total_sales,
    COUNT(*) AS transactions,
    COALESCE(AVG(total_amount),0) AS avg_ticket
    FROM transactions WHERE $where")->fetch_assoc();
$top_product = $conn->query("SELECT p.name, SUM(ti.quantity) AS qty
    FROM transaction_items ti 
    JOIN transactions t ON ti.transaction_id = t.transaction_id
    JOIN products p ON ti.product_id = p.product_id
    WHERE $where
    GROUP BY ti.product_id
    ORDER BY qty DESC
    LIMIT 1")->fetch_assoc();
$sales_rows = $conn->query("SELECT t.transaction_id, t.transaction_date, t.total_amount, t.payment_type, u.name AS cashier_name
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.user_id
    WHERE $where
    ORDER BY t.transaction_date DESC
    LIMIT 50");
$installments_search = $_GET['inst_search'] ?? '';
$installments_status = $_GET['inst_status'] ?? 'Active'; // Default to Active (Unpaid)

$installments_query = "
    SELECT 
        t.transaction_id, t.transaction_date, t.customer_name, t.customer_contact, 
        t.total_amount, t.down_payment,
        COUNT(i.installment_id) as total_months,
        SUM(CASE WHEN i.status = 'Paid' THEN 1 ELSE 0 END) as paid_months,
        MIN(CASE WHEN i.status = 'Unpaid' THEN i.due_date ELSE NULL END) as next_due_date,
        MAX(CASE WHEN i.status = 'Unpaid' AND i.due_date < CURDATE() THEN 1 ELSE 0 END) as has_overdue
    FROM transactions t
    JOIN installments i ON t.transaction_id = i.transaction_id
    WHERE t.payment_type = 'Installment'
";

// Apply Search Filter
if (!empty($installments_search)) {
    $search_term = $conn->real_escape_string($installments_search);
    $installments_query .= " AND (t.customer_name LIKE '%$search_term%' OR t.transaction_id LIKE '%$search_term%') ";
}

$installments_query .= " GROUP BY t.transaction_id ";

// Apply Status Filter (HAVING clause because we use aggregate data)
if ($installments_status === 'Active') {
    // Has remaining balance (paid_months < total_months)
    $installments_query .= " HAVING paid_months < total_months ";
} elseif ($installments_status === 'Overdue') {
    // Has overdue items
    $installments_query .= " HAVING has_overdue = 1 ";
} elseif ($installments_status === 'Paid') {
    // Fully paid
    $installments_query .= " HAVING paid_months = total_months ";
}

$installments_query .= " ORDER BY next_due_date ASC ";

$installment_accounts = $conn->query($installments_query);


// Pre-fetch details for modal (optional optimization vs AJAX)
$account_details = [];
if ($tab === 'installments') {
    $details_query = "
        SELECT i.*, t.transaction_id 
        FROM installments i 
        JOIN transactions t ON i.transaction_id = t.transaction_id
        WHERE t.payment_type = 'Installment'
        ORDER BY i.due_date ASC
    ";
    $details_res = $conn->query($details_query);
    while ($row = $details_res->fetch_assoc()) {
        $account_details[$row['transaction_id']][] = $row;
    }
}

$current_stock = $conn->query("SELECT name, category, quantity FROM products WHERE status = 'Available' ORDER BY name ASC LIMIT 60");
$low_stock = $conn->query("SELECT name, category, quantity FROM products WHERE status = 'Available' AND quantity < 5 ORDER BY quantity ASC LIMIT 10");
$stock_movement = $conn->query("SELECT t.transaction_date, u.name AS cashier_name, p.name AS product_name, ti.quantity, t.payment_type
    FROM transaction_items ti
    JOIN transactions t ON ti.transaction_id = t.transaction_id
    LEFT JOIN users u ON t.user_id = u.user_id
    JOIN products p ON ti.product_id = p.product_id
    ORDER BY t.transaction_date DESC
    LIMIT 20");
function peso($value) {
    return '₱' . number_format((float)$value, 2);
}
?>
<?php
$page_title = 'Reports - Tialo Japan Surplus';
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50 flex">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 page-header">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Business Intelligence</p>
                    <h1 class="text-3xl font-bold text-slate-900">Reports &amp; Analytics</h1>
                    <p class="text-sm text-slate-600">Slice sales, installments, and inventory performance.</p>
                </div>
                <a href="?page=reports/export&tab=<?php echo urlencode($tab); ?>&period=<?php echo urlencode($period); ?>&payment=<?php echo urlencode($payment_type); ?>&staff=<?php echo (int)$staff_id; ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>" class="inline-flex items-center px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export CSV
                </a>
            </div>
            <div class="border-t border-slate-200 bg-slate-50">
                <div class="px-6 py-3 overflow-x-auto">
                    <div class="flex items-center gap-3 min-w-max">
                        <a href="?page=reports&tab=sales&period=<?php echo urlencode($period); ?>&payment=<?php echo urlencode($payment_type); ?>&staff=<?php echo (int)$staff_id; ?>" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'sales' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">Sales Reports</a>
                        <a href="?page=reports&tab=installments" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'installments' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">Installment Reports</a>
                        <a href="?page=reports&tab=inventory" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'inventory' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">Inventory Reports</a>
                        <a href="?page=reports&tab=shipments" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'shipments' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">Shipment Reports</a>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-1 px-6 py-6 space-y-6">
            <?php if ($tab === 'sales'): ?>
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4">
                    <form class="grid grid-cols-1 md:grid-cols-5 gap-4" method="GET">
                        <input type="hidden" name="page" value="reports">
                        <input type="hidden" name="tab" value="sales">
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Period</label>
                            <select name="period" onchange="this.form.submit()" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="today" <?php echo $period==='today'?'selected':''; ?>>Today</option>
                                <option value="week" <?php echo $period==='week'?'selected':''; ?>>This Week</option>
                                <option value="month" <?php echo $period==='month'?'selected':''; ?>>This Month</option>
                                <option value="all" <?php echo $period==='all'?'selected':''; ?>>All Time</option>
                                <option value="custom" <?php echo $period==='custom'?'selected':''; ?>>Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Payment Type</label>
                            <select name="payment" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="all" <?php echo $payment_type==='all'?'selected':''; ?>>All</option>
                                <option value="Cash" <?php echo $payment_type==='Cash'?'selected':''; ?>>Cash</option>
                                <option value="GCash" <?php echo $payment_type==='GCash'?'selected':''; ?>>GCash</option>
                                <option value="Installment" <?php echo $payment_type==='Installment'?'selected':''; ?>>Installment</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Staff</label>
                            <select name="staff" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="0">All</option>
                                <?php while ($staff = $staff_options->fetch_assoc()): ?>
                                    <option value="<?php echo $staff['user_id']; ?>" <?php echo $staff_id === (int)$staff['user_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($staff['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Start</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="w-full mt-1 px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">End</label>
                            <div class="flex gap-2 mt-1">
                                <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <button class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">Apply</button>
                            </div>
                        </div>
                    </form>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="text-xs text-slate-500 mb-1">Total Sales</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo peso($overview['total_sales']); ?></p>
                            <p class="text-xs text-slate-400">Range: <?php echo $start_date; ?> → <?php echo $end_date; ?></p>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="text-xs text-slate-500 mb-1">Transactions</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo (int)$overview['transactions']; ?></p>
                            <p class="text-xs text-slate-400">Completed orders</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="text-xs text-slate-500 mb-1">Average Ticket</p>
                            <p class="text-3xl font-bold text-slate-900"><?php echo peso($overview['avg_ticket']); ?></p>
                            <p class="text-xs text-slate-400">Per transaction</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl border border-slate-200 p-4">
                            <p class="text-xs text-slate-500 mb-1">Top Product</p>
                            <p class="text-lg font-semibold text-slate-900"><?php echo $top_product['name'] ?? '—'; ?></p>
                            <p class="text-xs text-slate-400"><?php echo $top_product ? $top_product['qty'] . ' units' : 'No data'; ?></p>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3 text-left">Date</th>
                                    <th class="px-6 py-3 text-left">Transaction</th>
                                    <th class="px-6 py-3 text-left">Cashier</th>
                                    <th class="px-6 py-3 text-left">Payment</th>
                                    <th class="px-6 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if ($sales_rows->num_rows): ?>
                                    <?php while ($row = $sales_rows->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-3"><?php echo date('M d, Y', strtotime($row['transaction_date'])); ?></td>
                                            <td class="px-6 py-3 font-semibold text-slate-900">#<?php echo $row['transaction_id']; ?></td>
                                            <td class="px-6 py-3 text-slate-600"><?php echo htmlspecialchars($row['cashier_name'] ?? '—'); ?></td>
                                            <td class="px-6 py-3">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $row['payment_type'] === 'Cash' ? 'bg-emerald-50 text-emerald-700' : ($row['payment_type'] === 'GCash' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700'); ?>">
                                                    <?php echo $row['payment_type']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-right font-semibold"><?php echo peso($row['total_amount']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-6 text-center text-slate-500">No transactions for the selected filters.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($tab === 'installments'): ?>
                <div class="space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Installment Accounts</h2>
                            <p class="text-sm text-slate-500">Manage customer payment schedules.</p>
                        </div>
                        
                        <form method="GET" class="flex flex-col md:flex-row gap-3">
                            <input type="hidden" name="page" value="reports">
                            <input type="hidden" name="tab" value="installments">
                            
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </span>
                                <input type="text" name="inst_search" value="<?php echo htmlspecialchars($installments_search); ?>" placeholder="Search customer..." class="pl-9 pr-4 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#D00000] text-sm w-full md:w-64">
                            </div>
                            
                            <div class="flex bg-slate-100 p-1 rounded-lg">
                                <button type="submit" name="inst_status" value="Active" class="px-4 py-1.5 text-sm font-semibold rounded-md transition <?php echo $installments_status === 'Active' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">Active</button>
                                <button type="submit" name="inst_status" value="Overdue" class="px-4 py-1.5 text-sm font-semibold rounded-md transition <?php echo $installments_status === 'Overdue' ? 'bg-white text-red-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">Overdue</button>
                                <button type="submit" name="inst_status" value="Paid" class="px-4 py-1.5 text-sm font-semibold rounded-md transition <?php echo $installments_status === 'Paid' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">Paid</button>
                                <button type="submit" name="inst_status" value="All" class="px-4 py-1.5 text-sm font-semibold rounded-md transition <?php echo $installments_status === 'All' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">All</button>
                            </div>
                        </form>
                    </div>

                    <?php if ($installment_accounts && $installment_accounts->num_rows > 0): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <?php while ($acc = $installment_accounts->fetch_assoc()): ?>
                                <?php 
                                    $progress = ($acc['paid_months'] / $acc['total_months']) * 100;
                                    $is_overdue = (int)$acc['has_overdue'] === 1;
                                    $status_color = $is_overdue ? 'border-l-4 border-l-red-500' : ($progress >= 100 ? 'border-l-4 border-l-emerald-500' : 'border-l-4 border-l-blue-500');
                                    
                                    // Calculate remaining balance dynamically from details
                                    $remaining_balance = 0;
                                    if (isset($account_details[$acc['transaction_id']])) {
                                        foreach ($account_details[$acc['transaction_id']] as $d) {
                                            if ($d['status'] === 'Unpaid') {
                                                $remaining_balance += $d['amount_due']; // Sum unpaid dues
                                                // Or use the last record's balance? No, sum of dues is safer.
                                            }
                                        }
                                    }
                                    // Fallback if fully paid
                                    if ($progress >= 100) $remaining_balance = 0;
                                ?>
                                <div onclick="openInstallmentModal(<?php echo htmlspecialchars(json_encode($acc)); ?>, <?php echo htmlspecialchars(json_encode($account_details[$acc['transaction_id']] ?? [])); ?>)" 
                                     class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition cursor-pointer p-5 flex flex-col gap-4 <?php echo $status_color; ?>">
                                    
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-bold text-slate-900 text-lg"><?php echo htmlspecialchars($acc['customer_name'] ?? 'Unknown Customer'); ?></h3>
                                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($acc['customer_contact'] ?? 'No Contact'); ?></p>
                                        </div>
                                        <span class="text-xs font-mono bg-slate-100 px-2 py-1 rounded text-slate-600">#<?php echo $acc['transaction_id']; ?></span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-xs text-slate-500">Total Loan</p>
                                            <p class="font-semibold text-slate-900"><?php echo peso($acc['total_amount']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-500">Balance</p>
                                            <p class="font-semibold text-slate-900"><?php echo peso($remaining_balance); ?></p>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex justify-between text-xs text-slate-600">
                                            <span>Progress</span>
                                            <span><?php echo $acc['paid_months']; ?>/<?php echo $acc['total_months']; ?> months</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                                        <?php if ($progress >= 100): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 uppercase tracking-wide">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Fully Paid
                                            </span>
                                        <?php elseif ($is_overdue): ?>
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-red-600 uppercase tracking-wide">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Overdue: <?php echo date('M d', strtotime($acc['next_due_date'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs font-semibold text-slate-500">
                                                Next Due: <span class="text-slate-900"><?php echo date('M d', strtotime($acc['next_due_date'])); ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <span class="text-xs text-blue-600 hover:underline">View Details &rarr;</span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-2xl border border-slate-200">
                            <p class="text-slate-500">No active installment plans found.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Installment Detail Modal -->
                <div id="instModal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900" id="modalCustName">Customer Name</h3>
                                <p class="text-sm text-slate-500">Transaction #<span id="modalTransId"></span></p>
                            </div>
                            <button onclick="closeInstallmentModal()" class="text-slate-400 hover:text-slate-600 p-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        
                        <div class="p-6 overflow-y-auto space-y-6">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <p class="text-xs text-slate-500 uppercase">Total Amount</p>
                                    <p class="font-bold text-slate-900" id="modalTotal">₱0.00</p>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <p class="text-xs text-slate-500 uppercase">Down Payment</p>
                                    <p class="font-bold text-emerald-600" id="modalDown">₱0.00</p>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <p class="text-xs text-slate-500 uppercase">Date</p>
                                    <p class="font-semibold text-slate-900" id="modalDate">-</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wide mb-3">Payment Schedule</h4>
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                                            <tr>
                                                <th class="px-4 py-3">Due Date</th>
                                                <th class="px-4 py-3 text-right">Amount</th>
                                                <th class="px-4 py-3 text-center">Status</th>
                                                <th class="px-4 py-3 text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100" id="modalTableBody">
                                            <!-- JS populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function openInstallmentModal(account, details) {
                        document.getElementById('modalCustName').textContent = account.customer_name;
                        document.getElementById('modalTransId').textContent = account.transaction_id;
                        document.getElementById('modalTotal').textContent = '₱' + Number(account.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2});
                        document.getElementById('modalDown').textContent = '₱' + Number(account.down_payment).toLocaleString('en-US', {minimumFractionDigits: 2});
                        document.getElementById('modalDate').textContent = new Date(account.transaction_date).toLocaleDateString();

                        const tbody = document.getElementById('modalTableBody');
                        tbody.innerHTML = '';

                        details.forEach(item => {
                            const isPaid = item.status === 'Paid';
                            const isOverdue = !isPaid && new Date(item.due_date) < new Date();
                            const amount = Number(item.amount_due).toLocaleString('en-US', {minimumFractionDigits: 2});
                            const dateStr = new Date(item.due_date).toLocaleDateString();
                            
                            let statusBadge = isPaid 
                                ? `<span class="px-2 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">Paid</span>`
                                : (isOverdue 
                                    ? `<span class="px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Overdue</span>`
                                    : `<span class="px-2 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Unpaid</span>`
                                );
                            
                            let actionBtn = isPaid 
                                ? `<span class="text-slate-400 text-xs">Completed</span>`
                                : `<form method="POST" action="?page=reports/process_installment" onsubmit="return confirm('Mark this installment as Paid?');">
                                        <input type="hidden" name="installment_id" value="${item.installment_id}">
                                        <button type="submit" class="text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition">Pay</button>
                                   </form>`;

                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50';
                            tr.innerHTML = `
                                <td class="px-4 py-3 font-medium text-slate-900">${dateStr}</td>
                                <td class="px-4 py-3 text-right font-mono text-slate-600">₱${amount}</td>
                                <td class="px-4 py-3 text-center">${statusBadge}</td>
                                <td class="px-4 py-3 text-right">${actionBtn}</td>
                            `;
                            tbody.appendChild(tr);
                        });

                        document.getElementById('instModal').classList.remove('hidden');
                    }

                    function closeInstallmentModal() {
                        document.getElementById('instModal').classList.add('hidden');
                    }
                    
                    // Close on outside click
                    document.getElementById('instModal').addEventListener('click', (e) => {
                        if (e.target === document.getElementById('instModal')) closeInstallmentModal();
                    });
                </script>
            <?php elseif ($tab === 'inventory'): ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="bg-white border border-slate-200 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Current Stock (Top 50)</h3>
                        <div class="space-y-3 max-h-[420px] overflow-y-auto pr-2">
                            <?php if ($current_stock->num_rows): ?>
                                <?php while ($prod = $current_stock->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($prod['name']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($prod['category']); ?></p>
                                        </div>
                                        <span class="text-slate-600 font-semibold"><?php echo (int)$prod['quantity']; ?> pcs</span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-500">No products available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Low Stock Alerts</h3>
                        <div class="space-y-3">
                            <?php if ($low_stock->num_rows): ?>
                                <?php while ($prod = $low_stock->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($prod['name']); ?></p>
                                            <p class="text-xs text-slate-500"><?php echo htmlspecialchars($prod['category']); ?></p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Stock <?php echo (int)$prod['quantity']; ?></span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-sm text-slate-500">No items below the threshold.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-slate-900 mb-4">Recent Stock Movements</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Product</th>
                                        <th class="px-4 py-3 text-left">Qty</th>
                                        <th class="px-4 py-3 text-left">Handled By</th>
                                        <th class="px-4 py-3 text-left">Type</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if ($stock_movement->num_rows): ?>
                                        <?php while ($row = $stock_movement->fetch_assoc()): ?>
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 py-3"><?php echo date('M d, Y', strtotime($row['transaction_date'])); ?></td>
                                                <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                                <td class="px-4 py-3 text-slate-700"><?php echo (int)$row['quantity']; ?> pc</td>
                                                <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($row['cashier_name'] ?? '—'); ?></td>
                                                <td class="px-4 py-3">
                                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                                        <?php echo htmlspecialchars($row['payment_type']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="px-4 py-5 text-center text-slate-500">No movement recorded.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php elseif ($tab === 'shipments'): ?>
                <?php include __DIR__ . '/shipment_report.php'; ?>
            <?php endif; ?>
        </main>
    </div>
<?php include __DIR__ . '/../../includes/page_footer.php'; ?>