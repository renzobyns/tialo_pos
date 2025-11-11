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
    <?php include '../../includes/tailwind-cdn.html'; ?>
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-4xl font-bold text-slate-900 flex items-center space-x-3">
                <i class="fas fa-warehouse text-blue-600"></i>
                <span>Inventory Management</span>
            </h2>
        </div>
        
        <!-- Tabs -->
        <div class="flex gap-4 mb-8 border-b border-slate-300">
            <a href="?tab=shipments" 
               class="px-6 py-4 font-semibold border-b-4 transition <?php echo $tab === 'shipments' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900'; ?>">
                <i class="fas fa-truck mr-2"></i>Shipments
            </a>
            <a href="?tab=products" 
               class="px-6 py-4 font-semibold border-b-4 transition <?php echo $tab === 'products' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-600 hover:text-slate-900'; ?>">
                <i class="fas fa-boxes mr-2"></i>Products
            </a>
        </div>
        
        <!-- Tab Content -->
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
