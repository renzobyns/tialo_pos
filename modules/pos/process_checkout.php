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

$payment_type = sanitize($_POST['payment_type']);
$total_amount = (float)$_POST['total_amount'];
$user_id = $_SESSION['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Insert transaction
    $query = "INSERT INTO transactions (user_id, payment_type, total_amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isd", $user_id, $payment_type, $total_amount);
    $stmt->execute();
    $transaction_id = $conn->insert_id;
    
    // Insert transaction items and update product quantities
    foreach ($cart as $item) {
        // Insert transaction item
        $item_query = "INSERT INTO transaction_items (transaction_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_query);
        $item_stmt->bind_param("iiid", $transaction_id, $item['product_id'], $item['quantity'], $item['subtotal']);
        $item_stmt->execute();
        
        // Update product quantity
        $update_query = "UPDATE products SET quantity = quantity - ? WHERE product_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
        $update_stmt->execute();
    }
    
    // Handle installment if applicable
    if ($payment_type === 'Installment') {
        $installment_months = (int)$_POST['installment_months'];
        $monthly_amount = $total_amount / $installment_months;
        
        for ($i = 1; $i <= $installment_months; $i++) {
            $due_date = date('Y-m-d', strtotime("+$i months"));
            $amount_due = $monthly_amount;
            $balance_remaining = $total_amount - ($monthly_amount * ($i - 1));
            
            $install_query = "INSERT INTO installments (transaction_id, due_date, amount_due, balance_remaining) VALUES (?, ?, ?, ?)";
            $install_stmt = $conn->prepare($install_query);
            $install_stmt->bind_param("isdd", $transaction_id, $due_date, $amount_due, $balance_remaining);
            $install_stmt->execute();
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Clear cart from session
    unset($_SESSION['cart']);
    unset($_SESSION['discount']);
    
    // Redirect to receipt
    header("Location: receipt.php?transaction_id=$transaction_id");
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    header("Location: checkout.php?error=Payment processing failed");
    exit();
}
?>
