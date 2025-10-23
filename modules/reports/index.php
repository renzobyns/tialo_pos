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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/reports.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="reports-container">
        <div class="reports-header">
            <h2>Reports & Analytics</h2>
        </div>
        
        <div class="reports-tabs">
            <a href="?tab=sales" class="tab-link <?php echo $tab === 'sales' ? 'active' : ''; ?>">
                Sales Reports
            </a>
            <a href="?tab=installments" class="tab-link <?php echo $tab === 'installments' ? 'active' : ''; ?>">
                Installments
            </a>
            <a href="?tab=inventory" class="tab-link <?php echo $tab === 'inventory' ? 'active' : ''; ?>">
                Inventory
            </a>
        </div>
        
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
