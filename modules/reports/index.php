<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$tab = $_GET['tab'] ?? 'sales';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-4xl font-bold text-slate-900 flex items-center space-x-3">
                <i class="fas fa-chart-bar text-purple-600"></i>
                <span>Reports & Analytics</span>
            </h2>
        </div>
        
        <!-- Tabs -->
        <div class="flex gap-4 mb-8 border-b border-slate-300 flex-wrap">
            <a href="?tab=sales" 
               class="px-6 py-4 font-semibold border-b-4 transition flex items-center space-x-2 <?php echo $tab === 'sales' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900'; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Sales Reports</span>
            </a>
            <a href="?tab=installments" 
               class="px-6 py-4 font-semibold border-b-4 transition flex items-center space-x-2 <?php echo $tab === 'installments' ? 'border-amber-600 text-amber-600' : 'border-transparent text-slate-600 hover:text-slate-900'; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Installments</span>
            </a>
            <a href="?tab=inventory" 
               class="px-6 py-4 font-semibold border-b-4 transition flex items-center space-x-2 <?php echo $tab === 'inventory' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-600 hover:text-slate-900'; ?>">
                <i class="fas fa-warehouse"></i>
                <span>Inventory</span>
            </a>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <?php
            if ($tab === 'sales') {
                include 'sales_report.php';
            } elseif ($tab === 'installments') {
                include 'installment_report.php';
            } elseif ($tab === 'inventory') {
                include 'inventory_report.php';
            }
            ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
