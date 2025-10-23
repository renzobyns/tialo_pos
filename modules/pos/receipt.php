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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/pos.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="receipt-container">
        <div class="receipt-box" id="receipt">
            <div class="receipt-header">
                <h1>Tialo Japan Surplus</h1>
                <p>POS Receipt</p>
            </div>
            
            <div class="receipt-info">
                <p><strong>Receipt #:</strong> <?php echo str_pad($transaction_id, 6, '0', STR_PAD_LEFT); ?></p>
                <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($transaction['transaction_date'])); ?></p>
                <p><strong>Cashier:</strong> <?php echo htmlspecialchars($transaction['cashier_name']); ?></p>
                <p><strong>Payment:</strong> <?php echo htmlspecialchars($transaction['payment_type']); ?></p>
            </div>
            
            <table class="receipt-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₱<?php echo number_format($item['price'], 2); ?></td>
                            <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="receipt-total">
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($transaction['total_amount'], 2); ?></p>
            </div>
            
            <div class="receipt-footer">
                <p>Thank you for your purchase!</p>
                <p>Visit us again soon.</p>
            </div>
        </div>
        
        <div class="receipt-actions">
            <button onclick="window.print()" class="btn-print">Print Receipt</button>
            <a href="index.php" class="btn-new-transaction">New Transaction</a>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
