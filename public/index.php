<?php
session_start();

// Simple router based on a 'page' query parameter
$page = $_GET['page'] ?? 'home';

// Whitelist of allowed pages to prevent directory traversal and to map to the correct files.
// The key is the 'page' parameter from the URL, and the value is the path to the file to include.
$allowed_pages = [
    'home' => 'home_logic.php', // Special handling for the home page (initial redirects)
    'dashboard' => 'dashboard.php',
    'debug_auth' => 'debug_auth.php',
    'reset_password' => 'reset_password.php',
    
    // Auth
    'auth/login' => '../src/modules/auth/login.php',
    'auth/login_process' => '../src/modules/auth/login_process.php',
    'auth/logout' => '../src/modules/auth/logout.php',
    
    // POS
    'pos' => '../src/modules/pos/index.php',
    'pos/checkout' => '../src/modules/pos/checkout.php',
    'pos/process_checkout' => '../src/modules/pos/process_checkout.php',
    'pos/complete_sale' => '../src/modules/pos/complete_sale.php',
    'pos_receipt' => '../src/modules/pos/receipt.php',
    
    // Inventory
    'inventory' => '../src/modules/inventory/index.php',
    'inventory/products' => '../src/modules/inventory/products.php',
    'inventory/product_form' => '../src/modules/inventory/product_form.php',
    'inventory/process_product' => '../src/modules/inventory/process_product.php',
    'inventory/shipments' => '../src/modules/inventory/shipments.php',
    'inventory/shipment_form' => '../src/modules/inventory/shipment_form.php',
    'inventory/process_shipment' => '../src/modules/inventory/process_shipment.php',
    
    // Reports
    'reports' => '../src/modules/reports/index.php',
    'reports/sales' => '../src/modules/reports/sales_report.php',
    'reports/inventory' => '../src/modules/reports/inventory_report.php',
    'reports/installment' => '../src/modules/reports/installment_report.php',
    'reports/export' => '../src/modules/reports/export.php',
    
    // Users
    'users' => '../src/modules/users/index.php',
    'users/user_form' => '../src/modules/users/user_form.php',
    'users/process_user' => '../src/modules/users/process_user.php',
];

// Handle the home page logic separately
if ($page === 'home') {
    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['role'] === 'Admin') {
            header("Location: /index.php?page=dashboard");
        } else {
            header("Location: /index.php?page=pos");
        }
    } else {
        header("Location: /index.php?page=auth/login");
    }
    exit();
}

// Check if the requested page is in the whitelist and the file exists
if (array_key_exists($page, $allowed_pages) && file_exists($allowed_pages[$page])) {
    include $allowed_pages[$page];
} else {
    // Page not found (not in whitelist or file doesn't exist)
    http_response_code(404);
    // You can include a custom 404 page here
    echo "<h1>404 Not Found</h1>";
    echo "The page you requested was not found.";
}
?>