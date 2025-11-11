<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$tab = $_GET['tab'] ?? 'sales';
$subtab = $_GET['subtab'] ?? 'summary';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex">
    <!-- Sidebar -->
    <?php include '../../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Reports & Analytics</h1>
                        <p class="text-sm text-slate-600 mt-1">Generate and view business reports</p>
                    </div>
                    <button class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Export</span>
                    </button>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="border-t border-slate-200 bg-slate-50">
                <div class="px-8 py-3 overflow-x-auto">
                    <div class="flex items-center gap-3 min-w-max">
                        <a href="?tab=sales" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'sales' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Sales
                        </a>
                        <a href="?tab=inventory" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'inventory' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Inventory
                        </a>
                        <a href="?tab=customers" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'customers' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Customers
                        </a>
                        <a href="?tab=employees" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'employees' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Employees
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <main class="flex-1 px-8 py-6">
            <?php if ($tab === 'sales'): ?>
                <div class="space-y-6">
                    <!-- Date Filter -->
                    <div class="bg-white rounded-lg border border-slate-200 p-4">
                        <div class="flex flex-col md:flex-row gap-4">
                            <input type="date" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <input type="date" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <button class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition font-medium">Filter</button>
                        </div>
                    </div>
                    
                    <!-- KPI Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <p class="text-sm text-slate-600 mb-2">Total Sales</p>
                            <p class="text-3xl font-bold text-slate-900">₱45,800.00</p>
                            <p class="text-xs text-green-600 mt-2">+12% from last month</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <p class="text-sm text-slate-600 mb-2">Transactions</p>
                            <p class="text-3xl font-bold text-slate-900">156</p>
                            <p class="text-xs text-slate-600 mt-2">Completed</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <p class="text-sm text-slate-600 mb-2">Avg Transaction</p>
                            <p class="text-3xl font-bold text-slate-900">₱293.59</p>
                            <p class="text-xs text-slate-600 mt-2">Per transaction</p>
                        </div>
                        <div class="bg-white rounded-lg border border-slate-200 p-6">
                            <p class="text-sm text-slate-600 mb-2">Top Product</p>
                            <p class="text-xl font-bold text-slate-900">Rice Cooker</p>
                            <p class="text-xs text-slate-600 mt-2">45 units sold</p>
                        </div>
                    </div>
                    
                    <!-- Sales Table -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Transaction ID</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Amount</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Payment Type</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Cashier</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 text-sm text-slate-900">Nov 11, 2025</td>
                                    <td class="px-6 py-4 text-sm text-slate-600">#TXN001</td>
                                    <td class="px-6 py-4 text-sm font-semibold">₱2,500.00</td>
                                    <td class="px-6 py-4 text-sm"><span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Cash</span></td>
                                    <td class="px-6 py-4 text-sm text-slate-600">Admin User</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-xl font-bold mb-6">Report Details</h2>
                    <p class="text-slate-600">This report section is under development</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
