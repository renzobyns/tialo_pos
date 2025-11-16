<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$upload_dir = __DIR__ . '/../../assets/img/products/';
$upload_rel = 'products/';
$max_size = 4 * 1024 * 1024; // 4 MB
$allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

function handle_upload($file, $upload_dir, $upload_rel, $max_size, $allowed_types) {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed.'];
    }
    if ($file['size'] > $max_size) {
        return [null, 'File too large.'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed_types[$mime])) {
        return [null, 'Invalid file type.'];
    }
    $ext = $allowed_types[$mime];
    $filename = uniqid('product_', true) . '.' . $ext;
    $target = rtrim($upload_dir, '/') . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return [null, 'Could not save file.'];
    }
    return [$upload_rel . $filename, null];
}

function delete_image_file($path) {
    if (!$path) return;
    $full = __DIR__ . '/../../assets/img/' . ltrim($path, '/');
    if (is_file($full)) {
        @unlink($full);
    }
}

if ($action === 'create') {
    $name = sanitize($_POST['name']);
    $category = sanitize($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $status = sanitize($_POST['status']);
    $shipment_id = !empty($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : null;
    [$image_path, $upload_error] = handle_upload($_FILES['image'] ?? [], $upload_dir, $upload_rel, $max_size, $allowed_types);
    if ($upload_error) {
        header("Location: product_form.php?error=" . urlencode($upload_error));
        exit();
    }
    
    $query = "INSERT INTO products (name, category, quantity, price, status, shipment_id, image) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssidsis", $name, $category, $quantity, $price, $status, $shipment_id, $image_path);
    
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
    $current = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $current->bind_param("i", $product_id);
    $current->execute();
    $existing = $current->get_result()->fetch_assoc();
    $current_image = $existing['image'] ?? null;

    $remove_image = !empty($_POST['remove_image']);
    [$new_image_path, $upload_error] = handle_upload($_FILES['image'] ?? [], $upload_dir, $upload_rel, $max_size, $allowed_types);
    if ($upload_error) {
        header("Location: product_form.php?id=$product_id&error=" . urlencode($upload_error));
        exit();
    }
    $final_image = $new_image_path ?? ($remove_image ? null : $current_image);
    if ($new_image_path && $current_image) {
        delete_image_file($current_image);
    }
    if ($remove_image && !$new_image_path) {
        delete_image_file($current_image);
    }

    $query = "UPDATE products SET name = ?, category = ?, quantity = ?, price = ?, status = ?, shipment_id = ?, image = ? 
              WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssidsisi", $name, $category, $quantity, $price, $status, $shipment_id, $final_image, $product_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?tab=products&success=Product updated successfully");
    } else {
        header("Location: product_form.php?id=$product_id&error=Failed to update product");
    }
    exit();
}

elseif ($action === 'delete') {
    $product_id = (int)$_GET['id'];
    $current = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $current->bind_param("i", $product_id);
    $current->execute();
    $existing = $current->get_result()->fetch_assoc();
    $current_image = $existing['image'] ?? null;
    
    $query = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        delete_image_file($current_image);
        header("Location: index.php?tab=products&success=Product deleted successfully");
    } else {
        header("Location: index.php?tab=products&error=Failed to delete product");
    }
    exit();
}

header("Location: index.php?tab=products");
exit();
?>
