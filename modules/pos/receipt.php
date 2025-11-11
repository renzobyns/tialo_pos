<?php
include '../../includes/auth_check.php';
include '../../includes/db_connect.php';

$transaction_id = (int)($_GET['transaction_id'] ?? 0);

if (!$transaction_id) {
    header("Location: index.php");
    exit();
}

// Get transaction details
$trans_query = "SELECT t.*, u.name as cashier_name FROM transactions t 
                JOIN users u ON t.user_id = u.user_id 
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-2xl mx-auto px-4 py-8">
        <!-- Receipt Container -->
        <div id="receipt" class="bg-white rounded-lg shadow-2xl p-12 max-w-xl mx-auto">
            <!-- Header -->
            <div class="text-center border-b-2 border-slate-200 pb-6 mb-6">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">
                    <i class="fas fa-shopping-bag text-emerald-600 mr-2"></i>Tialo Japan Surplus
                </h1>
                <p class="text-slate-600 font-semibold">Official Receipt</p>
            </div>
            
            <!-- Receipt Info -->
            <div class="space-y-2 text-sm text-slate-600 mb-6 pb-6 border-b border-slate-200">
                <div class="flex justify-between">
                    <span class="font-semibold">Receipt #:</span>
                    <span><?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Date & Time:</span>
                    <span><?php echo date('M d, Y \a\t H:i', strtotime($transaction['transaction_date'])); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Cashier:</span>
                    <span><?php echo htmlspecialchars($transaction['cashier_name']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Payment Method:</span>
                    <span><?php echo htmlspecialchars($transaction['payment_type']); ?></span>
                </div>
            </div>
            
            <!-- Items Table -->
            <div class="mb-6">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-slate-300">
                            <th class="text-left py-2 font-bold text-slate-900">Item</th>
                            <th class="text-center py-2 font-bold text-slate-900">Qty</th>
                            <th class="text-right py-2 font-bold text-slate-900">Price</th>
                            <th class="text-right py-2 font-bold text-slate-900">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items_result->fetch_assoc()): ?>
                            <tr class="border-b border-slate-200">
                                <td class="py-2 text-slate-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-center py-2 text-slate-600"><?php echo $item['quantity']; ?></td>
                                <td class="text-right py-2 text-slate-600">₱<?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-right py-2 font-semibold text-slate-900">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Total -->
            <div class="bg-emerald-50 -mx-12 -mb-12 px-12 py-6 rounded-b-lg">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-slate-900">Total Amount:</span>
                    <span class="text-2xl font-bold text-emerald-600">₱<?php echo number_format($transaction['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-8 pt-6 border-t border-slate-200 text-sm text-slate-600 space-y-1">
                <p class="font-semibold">Thank you for your purchase!</p>
                <p>Visit us again soon.</p>
                <p class="text-xs pt-2 text-slate-500">© 2025 Tialo Japan Surplus. All rights reserved.</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-8 flex gap-4 justify-center">
            <button onclick="window.print()" class="flex items-center space-x-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                <i class="fas fa-print"></i>
                <span>Print Receipt</span>
            </button>
            <a href="index.php" class="flex items-center space-x-2 bg-emerald-600 text-white px-6 py-3 rounded-lg hover:bg-emerald-700 transition font-semibold">
                <i class="fas fa-plus"></i>
                <span>New Transaction</span>
            </a>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
