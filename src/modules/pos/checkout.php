<?php
include __DIR__ . '/../../includes/auth_check.php';
include __DIR__ . '/../../includes/db_connect.php';

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
<?php
$page_title = 'Checkout - Tialo Japan Surplus';
$page_styles = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50">
    <?php include __DIR__ . '/../../includes/header.php'; ?>
    
    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="grid grid-cols-3 gap-8">
            <!-- Order Summary -->
            <div class="col-span-2">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center space-x-3">
                        <i class="fas fa-receipt text-blue-600"></i>
                        <span>Order Summary</span>
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-slate-200">
                                    <th class="text-left py-3 text-sm font-semibold text-slate-700">Product</th>
                                    <th class="text-center py-3 text-sm font-semibold text-slate-700">Qty</th>
                                    <th class="text-right py-3 text-sm font-semibold text-slate-700">Price</th>
                                    <th class="text-right py-3 text-sm font-semibold text-slate-700">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $item): ?>
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="py-4 text-sm text-slate-900 font-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td class="text-center py-4 text-sm text-slate-600"><?php echo $item['quantity']; ?></td>
                                        <td class="text-right py-4 text-sm text-slate-600">₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="text-right py-4 text-sm font-semibold text-slate-900">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totals -->
                    <div class="mt-8 pt-6 border-t-2 border-slate-200 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 font-semibold">Subtotal:</span>
                            <span class="text-slate-900 font-semibold">₱<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-700 font-semibold">Discount:</span>
                            <span class="text-red-600 font-semibold">-₱<?php echo number_format($discount, 2); ?></span>
                        </div>
                        <div class="flex justify-between items-center bg-blue-50 -mx-8 -mb-8 px-8 py-4 rounded-b-lg">
                            <span class="text-lg font-bold text-slate-900">Total Amount:</span>
                            <span class="text-2xl font-bold text-blue-600">₱<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div>
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center space-x-2">
                        <i class="fas fa-credit-card text-emerald-600"></i>
                        <span>Payment Method</span>
                    </h3>
                    
                    <form method="POST" action="/index.php?page=pos/process_checkout" class="space-y-4">
                        <!-- Payment Options -->
                        <div class="space-y-3">
                            <label class="flex items-center space-x-3 p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="payment_type" value="Cash" checked class="w-4 h-4 text-blue-600">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-money-bill text-emerald-600"></i>
                                    <span class="font-semibold text-slate-900">Cash</span>
                                </div>
                            </label>
                            
                            <label class="flex items-center space-x-3 p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="payment_type" value="GCash" class="w-4 h-4 text-blue-600">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-mobile text-blue-600"></i>
                                    <span class="font-semibold text-slate-900">GCash</span>
                                </div>
                            </label>
                            
                            <label class="flex items-center space-x-3 p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                <input type="radio" name="payment_type" value="Installment" class="w-4 h-4 text-blue-600">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-calendar text-amber-600"></i>
                                    <span class="font-semibold text-slate-900">Installment</span>
                                </div>
                            </label>
                        </div>
                        
                        <!-- Installment Details (hidden by default) -->
                        <div id="installmentDetails" style="display: none;" class="mt-4 pt-4 border-t-2 border-slate-200 space-y-4">
                            <div>
                                <label for="customerName" class="block text-sm font-semibold text-slate-700 mb-2">
                                    <i class="fas fa-user mr-2 text-amber-600"></i>Customer Name:
                                </label>
                                <input type="text" id="customerName" name="customer_name" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                             <div>
                                <label for="customerContact" class="block text-sm font-semibold text-slate-700 mb-2">
                                    <i class="fas fa-phone mr-2 text-amber-600"></i>Contact Number:
                                </label>
                                <input type="text" id="customerContact" name="customer_contact" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label for="installmentMonths" class="block text-sm font-semibold text-slate-700 mb-2">
                                    <i class="fas fa-hourglass-half mr-2 text-amber-600"></i>Number of Months:
                                </label>
                                <select id="installmentMonths" name="installment_months" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="3">3 Months</option>
                                    <option value="6" selected>6 Months</option>
                                    <option value="12">12 Months</option>
                                </select>
                            </div>
                        </div>
                        
                        <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                        
                        <!-- Action Buttons -->
                        <div class="space-y-3 mt-6 pt-4 border-t-2 border-slate-200">
                            <button type="submit" class="w-full flex items-center justify-center space-x-2 bg-emerald-600 text-white px-4 py-3 rounded-lg hover:bg-emerald-700 transition font-semibold">
                                <i class="fas fa-check-circle"></i>
                                <span>Complete Payment</span>
                            </button>
                            <a href="/index.php?page=pos" class="w-full flex items-center justify-center space-x-2 bg-slate-300 text-slate-700 px-4 py-3 rounded-lg hover:bg-slate-400 transition font-semibold">
                                <i class="fas fa-times-circle"></i>
                                <span>Cancel</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    
<?php
$page_scripts = <<<EOT
    <script>
        // Show/hide installment details and set required attribute
        const customerNameInput = document.getElementById('customerName');
        const customerContactInput = document.getElementById('customerContact');
        const installmentDetails = document.getElementById('installmentDetails');

        document.querySelectorAll('input[name="payment_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isInstallment = this.value === 'Installment';
                installmentDetails.style.display = isInstallment ? 'block' : 'none';
                customerNameInput.required = isInstallment;
                customerContactInput.required = isInstallment;
            });
        });
    </script>
EOT;
?>
