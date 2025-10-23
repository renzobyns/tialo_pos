<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $status = sanitize($_POST['status']);
    $shipment_id = !empty($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : null;
    
    $query = "INSERT INTO products (name, category, quantity, price, status, shipment_id) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssiisi", $name, $category, $quantity, $price, $status, $shipment_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=products&success=Product created successfully");
    } else {
        header("Location: product_form.php?error=Failed to create product");
    }
    exit();
}

elseif ($action === 'update') {
    $product_id = (int)$_POST['product_id'];
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $status = sanitize($_POST['status']);
    $shipment_id = !empty($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : null;
    
    $query = "UPDATE products SET name = ?, category = ?, quantity = ?, price = ?, status = ?, shipment_id = ? 
              WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siiisii", $name, $category, $quantity, $price, $status, $shipment_id, $product_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=products&success=Product updated successfully");
    } else {
        header("Location: product_form.php?id=$product_id&error=Failed to update product");
    }
    exit();
}

elseif ($action === 'delete') {
    $product_id = (int)$_GET['id'];
    
    $query = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=products&success=Product deleted successfully");
    } else {
        header("Location: index.php?tab=products&error=Failed to delete product");
    }
    exit();
}

header("Location: index.php?tab=products");
exit();
?>
