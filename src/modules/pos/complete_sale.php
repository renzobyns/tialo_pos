<?php
include __DIR__ . '/../../includes/auth_check.php';
include __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload.']);
    exit();
}

$cart = $input['cart'] ?? [];
$discount = isset($input['discount']) ? (float)$input['discount'] : 0;
$payment_type = isset($input['payment_type']) ? sanitize($input['payment_type']) : '';
$installment_months = isset($input['installment_months']) ? (int)$input['installment_months'] : 6;
$down_payment = isset($input['down_payment']) ? (float)$input['down_payment'] : 0;
$customer_name = isset($input['customer_name']) ? sanitize($input['customer_name']) : '';
$customer_contact = isset($input['customer_contact']) ? sanitize($input['customer_contact']) : '';

if (empty($cart)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit();
}

$allowed_methods = ['Cash', 'GCash', 'Installment'];
if (!in_array($payment_type, $allowed_methods, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid payment method.']);
    exit();
}

// Validation for Installment
if ($payment_type === 'Installment') {
    if (empty($customer_name) || empty($customer_contact)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Customer name and contact are required for installments.']);
        exit();
    }
    if ($down_payment < 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid down payment amount.']);
        exit();
    }
}

$product_stmt = $conn->prepare("SELECT name, price, quantity FROM products WHERE product_id = ? AND status = 'Available'");
$line_items = [];
$subtotal = 0;

foreach ($cart as $item) {
    $product_id = (int)($item['product_id'] ?? 0);
    $requested_qty = (int)($item['quantity'] ?? 0);

    if ($product_id <= 0 || $requested_qty <= 0) {
        continue;
    }

    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $result = $product_stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found or unavailable.']);
        exit();
    }

    if ((int)$product['quantity'] < $requested_qty) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => $product['name'] . ' does not have enough stock.']);
        exit();
    }

    $line_total = (float)$product['price'] * $requested_qty;
    $subtotal += $line_total;

    $line_items[] = [
        'product_id' => $product_id,
        'name' => $product['name'],
        'quantity' => $requested_qty,
        'price' => (float)$product['price'],
        'subtotal' => $line_total,
    ];
}

if (empty($line_items)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'No valid items in cart.']);
    exit();
}

if ($discount < 0 || $discount > $subtotal) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid discount amount.']);
    exit();
}

$total_amount = max($subtotal - $discount, 0);
$user_id = $_SESSION['user_id'];

$conn->begin_transaction();

try {
    $txn_stmt = $conn->prepare("INSERT INTO transactions (user_id, payment_type, total_amount, discount_amount, down_payment, customer_name, customer_contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $txn_stmt->bind_param("isdddss", $user_id, $payment_type, $total_amount, $discount, $down_payment, $customer_name, $customer_contact);
    $txn_stmt->execute();
    $transaction_id = $conn->insert_id;

    $item_stmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $stock_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");

    foreach ($line_items as $line) {
        $item_stmt->bind_param("iiid", $transaction_id, $line['product_id'], $line['quantity'], $line['subtotal']);
        $item_stmt->execute();

        $stock_stmt->bind_param("ii", $line['quantity'], $line['product_id']);
        $stock_stmt->execute();
    }

    if ($payment_type === 'Installment') {
        if ($installment_months <= 0) {
            $installment_months = 6;
        }
        
        $balance_for_installment = $total_amount - $down_payment;
        $monthly_amount = $balance_for_installment / $installment_months;

        $install_stmt = $conn->prepare("INSERT INTO installments (transaction_id, due_date, amount_due, balance_remaining) VALUES (?, ?, ?, ?)");
        for ($i = 1; $i <= $installment_months; $i++) {
            $due_date = date('Y-m-d', strtotime("+$i months"));
            $amount_due = $monthly_amount;
            // Balance remaining logic: starts at full installment balance, reduces by monthly amount paid (simulated here)
            // But usually balance remaining is what is left AFTER this payment.
            // Let's stick to the previous logic but use the new base.
            // Actually, balance_remaining usually means "How much is left to pay *before* this installment is paid" or *after*?
            // The previous code was: $total_amount - ($monthly_amount * ($i - 1));
            // This implies balance at the *start* of the period. 
            // Let's adjust: Balance remaining initially is $balance_for_installment.
            // For record 1: Balance is $balance_for_installment.
            // For record 2: Balance is $balance_for_installment - $monthly_amount.
            
            $balance_remaining = $balance_for_installment - ($monthly_amount * ($i - 1));
            
            $install_stmt->bind_param("isdd", $transaction_id, $due_date, $amount_due, $balance_remaining);
            $install_stmt->execute();
        }
    }

    $conn->commit();

    $_SESSION['toast_message'] = [
        'type' => 'success',
        'message' => 'Sale #' . $transaction_id . ' completed successfully!'
    ];

    echo json_encode([
        'success' => true,
        'receipt_url' => "/index.php?page=pos_receipt&transaction_id={$transaction_id}"
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to complete sale.']);
}
