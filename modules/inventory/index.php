<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$tab = $_GET['tab'] ?? 'shipments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Tialo Japan Surplus</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/inventory.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="inventory-container">
        <div class="inventory-header">
            <h2>Inventory Management</h2>
        </div>
        
        <div class="inventory-tabs">
            <a href="?tab=shipments" class="tab-link <?php echo $tab === 'shipments' ? 'active' : ''; ?>">
                Shipments
            </a>
            <a href="?tab=products" class="tab-link <?php echo $tab === 'products' ? 'active' : ''; ?>">
                Products
            </a>
        </div>
        
        <div class="tab-content">
            <?php
            if ($tab === 'shipments') {
                include 'shipments.php';
            } elseif ($tab === 'products') {
                include 'products.php';
            }
            ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
