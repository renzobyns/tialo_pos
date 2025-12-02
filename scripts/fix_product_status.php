<?php
include __DIR__ . '/../src/includes/db_connect.php';

// Get all products
$result = $conn->query("SELECT product_id, quantity, status FROM products");

if ($result->num_rows > 0) {
    while ($product = $result->fetch_assoc()) {
        $new_status = $product['status'];
        if ((int)$product['quantity'] === 0 && $product['status'] !== 'Out of Stock') {
            $new_status = 'Out of Stock';
        } elseif ((int)$product['quantity'] > 0 && $product['status'] === 'Out of Stock') {
            $new_status = 'Available';
        }

        if ($new_status !== $product['status']) {
            $update_query = "UPDATE products SET status = '$new_status' WHERE product_id = " . $product['product_id'];
            if ($conn->query($update_query) === TRUE) {
                echo "Product ID " . $product['product_id'] . " status updated to " . $new_status . "\n";
            } else {
                echo "Error updating record for product ID " . $product['product_id'] . ": " . $conn->error . "\n";
            }
        }
    }
} else {
    echo "0 results";
}

$conn->close();
?>
