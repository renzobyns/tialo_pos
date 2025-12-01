<?php
include __DIR__ . '/../../includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$upload_dir = dirname(__DIR__, 3) . '/public/assets/img/products/';
$upload_rel = 'products/';
$max_size = 4 * 1024 * 1024; // 4 MB
$allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

function handle_upload($file, $upload_dir, $upload_rel, $max_size, $allowed_types) {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed with error code ' . $file['error']];
    }
    if ($file['size'] > $max_size) {
        return [null, 'File too large. Max is 4 MB.'];
    }
    if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
        return [null, 'Upload directory is not writable. Check server permissions.'];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed_types[$mime])) {
        return [null, 'Invalid file type. Only JPG, PNG, and WebP are allowed.'];
    }
    $ext = $allowed_types[$mime];
    $filename = uniqid('product_', true) . '.' . $ext;
    $target = rtrim($upload_dir, '/') . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return [null, 'Could not save uploaded file. Check server permissions.'];
    }
    return [$upload_rel . $filename, null];
}

function delete_image_file($path) {
    if (!$path) return;
    $full = dirname(__DIR__, 3) . '/public/assets/img/' . ltrim($path, '/');
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
        $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Product created successfully.'];
        header("Location: /index.php?page=inventory&tab=inventory");
    } else {
        $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Failed to create product.'];
        header("Location: /index.php?page=inventory/product_form");
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
        $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Product updated successfully.'];
        header("Location: /index.php?page=inventory&tab=inventory");
    } else {
        $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Failed to update product.'];
        header("Location: /index.php?page=inventory/product_form&id=$product_id");
    }
    exit();
}

elseif ($action === 'delete') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    if (!$product_id) {
        $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Invalid product ID for deletion.'];
        header("Location: /index.php?page=inventory&tab=inventory");
        exit();
    }
    
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
        $_SESSION['toast_message'] = ['type' => 'success', 'message' => 'Product deleted successfully.'];
    } else {
        $_SESSION['toast_message'] = ['type' => 'error', 'message' => 'Failed to delete product.'];
    }
    header("Location: /index.php?page=inventory&tab=inventory");
    exit();
}

header("Location: /index.php?page=inventory&tab=inventory");
exit();
?>
