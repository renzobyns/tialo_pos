<?php
include __DIR__ . '/../../includes/auth_check.php';
include __DIR__ . '/../../includes/db_connect.php';

$transaction_id = (int)($_GET['transaction_id'] ?? 0);

if (!$transaction_id) {
    header("Location: index.php");
    exit();
}

// Get transaction details
$trans_query = "SELECT t.*, u.name as cashier_name FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.user_id 
                WHERE t.transaction_id = ?";
$trans_stmt = $conn->prepare($trans_query);
$trans_stmt->bind_param("i", $transaction_id);
$trans_stmt->execute();
$transaction = $trans_stmt->get_result()->fetch_assoc();

if (!$transaction) {
    header("Location: index.php");
    exit();
}

// Get transaction items
$items_query = "SELECT ti.*, p.name FROM transaction_items ti 
                JOIN products p ON ti.product_id = p.product_id 
                WHERE ti.transaction_id = ?";
$items_stmt = $conn->prepare($items_query);
$items_stmt->bind_param("i", $transaction_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = [];
$items_total_quantity = 0;
while ($row = $items_result->fetch_assoc()) {
    $qty = (int) $row['quantity'];
    $items_total_quantity += $qty;
    $unit_price = isset($row['price']) ? (float) $row['price'] : (float) $row['subtotal'] / max($qty, 1);
    $row['unit_price'] = $unit_price;
    $items[] = $row;
}
$total_line_items = count($items);
?>
<?php
$page_title = 'Receipt - Tialo Japan Surplus';
$page_styles = <<<EOT
<style>
        .receipt-shell {
            border-radius: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .receipt-gradient {
            background: linear-gradient(90deg, #9D0208, #D00000, #E85D04);
        }
        .receipt-table th,
        .receipt-table td {
            font-size: 0.9rem;
        }
        @media print {
            body {
                background: #fff;
                color: #111;
            }
            main {
                max-width: 80mm;
                padding: 0;
                margin: 0 auto;
            }
            .print\:hidden {
                display: none !important;
            }
            .receipt-shell {
                border-radius: 0;
                border: 1px solid #111;
                box-shadow: none;
            }
            .receipt-gradient {
                background: transparent !important;
                color: #111 !important;
                border-bottom: 1px dashed #aaa;
                padding: 16px !important;
                text-align: center;
            }
            .receipt-gradient p,
            .receipt-gradient div {
                color: inherit !important;
            }
            .receipt-table thead {
                background: transparent !important;
            }
            .receipt-table th {
                border-bottom: 1px dashed #ccc;
            }
            .receipt-table td {
                border-bottom: 1px dotted #e5e7eb;
            }
            .receipt-summary {
                border: 1px dashed #bbb !important;
                background: transparent !important;
            }
            .print\:shadow-none {
                box-shadow: none !important;
            }
            .print\:border-0 {
                border: 0 !important;
            }
        }
    </style>
EOT;
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50 text-slate-900">
    <main class="max-w-4xl mx-auto px-4 sm:px-8 py-10">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 print:hidden">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Digital Receipt</p>
                <h1 class="text-3xl font-bold text-slate-900">Sale #<?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?></h1>
                <p class="text-sm text-slate-500">Review or print the official receipt for this transaction.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-600 text-white text-sm font-semibold shadow-sm hover:bg-red-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 9H4a2 2 0 00-2 2v6h4m14 0h-4m4 0v-6a2 2 0 00-2-2h-2m0 0H6m12 0v10H6V9" />
                    </svg>
                    Print Receipt
                </button>
                <a href="/index.php?page=pos" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 text-sm font-semibold text-slate-700 hover:border-red-200 hover:text-red-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Transaction
                </a>
            </div>
        </header>

        <section id="receipt" class="mt-8 bg-white receipt-shell shadow-xl print:shadow-none overflow-hidden">
            <div class="receipt-gradient px-8 py-6 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-wide uppercase text-white/80">Tialo Japan Surplus</p>
                    <p class="text-xs text-white/70">Official Receipt · Generated <?php echo date('M d, Y \\a\\t h:i A', strtotime($transaction['transaction_date'])); ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase text-white/70">Receipt #</p>
                    <p class="text-2xl font-bold tracking-widest"><?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>

            <div class="p-8 space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Date & Time</p>
                        <p class="font-semibold text-slate-900"><?php echo date('M d, Y \\a\\t h:i A', strtotime($transaction['transaction_date'])); ?></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Cashier</p>
                        <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($transaction['cashier_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Payment Method</p>
                        <p class="inline-flex items-center gap-2 font-semibold">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $transaction['payment_type'] === 'Installment' ? 'bg-amber-100 text-amber-700' : ($transaction['payment_type'] === 'GCash' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'); ?>">
                                <?php echo htmlspecialchars($transaction['payment_type']); ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 mb-1">Items in Cart</p>
                        <p class="font-semibold text-slate-900"><?php echo $items_total_quantity; ?> total / <?php echo $total_line_items; ?> SKU</p>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="receipt-table min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-3">Item</th>
                                <th class="px-6 py-3 text-center">Qty</th>
                                <th class="px-6 py-3 text-right">Unit Price</th>
                                <th class="px-6 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium text-slate-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="px-6 py-4 text-center text-slate-600"><?php echo (int) $item['quantity']; ?></td>
                                    <td class="px-6 py-4 text-right text-slate-600">₱<?php echo number_format($item['unit_price'], 2); ?></td>
                                    <td class="px-6 py-4 text-right font-semibold text-slate-900">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php
$subtotal = ($transaction['total_amount'] ?? 0) + ($transaction['discount_amount'] ?? 0);
$discount = $transaction['discount_amount'] ?? 0;
?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="text-sm text-slate-600">
                        <p class="font-semibold">Thank you for choosing Tialo Japan Surplus.</p>
                        <p class="text-xs mt-1">This serves as an official receipt for in-store purchases.</p>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-semibold">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <?php if ($discount > 0): ?>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Discount</span>
                            <span class="font-semibold text-red-600">- ₱<?php echo number_format($discount, 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between font-bold text-lg border-t border-dashed border-slate-300 pt-3 text-slate-900">
                            <span>Total Amount</span>
                            <span>₱<?php echo number_format($transaction['total_amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <p class="text-center text-xs text-slate-500 mt-6 print:hidden">© <?php echo date('Y'); ?> Tialo Japan Surplus · Receipt generated by the POS module.</p>
    </main>
<?php include __DIR__ . '/../../includes/page_footer.php'; ?>
