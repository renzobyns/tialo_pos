<?php
include '../../includes/auth_check.php';
include '../../includes/db_connect.php';

// Get cart from session
$cart = $_SESSION['cart'] ?? [];
$discount = $_SESSION['discount'] ?? 0;

if (empty($cart)) {
    header("Location: index.php");
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['subtotal'];
}
$total = $subtotal - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Tialo Japan Surplus</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/pos.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="checkout-container">
        <div class="checkout-box">
            <h2>Checkout</h2>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span>Discount:</span>
                        <span>-₱<?php echo number_format($discount, 2); ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total Amount:</span>
                        <span>₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Options -->
            <div class="payment-section">
                <h3>Payment Method</h3>
                <form method="POST" action="process_checkout.php">
                    <div class="payment-options">
                        <label class="payment-option">
                            <input type="radio" name="payment_type" value="Cash" checked>
                            <span>Cash</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_type" value="GCash">
                            <span>GCash</span>
                        </label>
                        <label class="payment-option">
                            <input type="radio" name="payment_type" value="Installment">
                            <span>Installment</span>
                        </label>
                    </div>
                    
                    <!-- Installment Details (hidden by default) -->
                    <div id="installmentDetails" style="display: none;">
                        <div class="form-group">
                            <label for="installmentMonths">Number of Months:</label>
                            <select id="installmentMonths" name="installment_months">
                                <option value="3">3 Months</option>
                                <option value="6">6 Months</option>
                                <option value="12">12 Months</option>
                            </select>
                        </div>
                    </div>
                    
                    <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                    
                    <div class="checkout-actions">
                        <button type="submit" class="btn-complete-payment">Complete Payment</button>
                        <a href="index.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Show/hide installment details based on payment type
        document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('installmentDetails').style.display = 
                    this.value === 'Installment' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
