<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$tab = $_GET['tab'] ?? 'inventory';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex">
    <?php include '../../includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Inventory Management</h1>
                        <p class="text-sm text-slate-600 mt-1">Track products, stock levels, and shipments</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">Import CSV</button>
                        <button class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Add New Item</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-200 bg-slate-50">
                <div class="px-8 py-3 overflow-x-auto">
                    <div class="flex items-center gap-3 min-w-max">
                        <a href="?tab=inventory" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'inventory' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Inventory
                        </a>
                        <a href="?tab=shipments" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'shipments' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover-border-slate-200 hover:text-slate-900'; ?>">
                            Shipments
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <main class="flex-1 px-8 py-6">
            <?php if ($tab === 'inventory'): ?>
                <div class="space-y-6">
                    <div class="bg-white rounded-lg border border-slate-200 p-4 flex flex-col xl:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" placeholder="Search products..." class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="flex gap-3">
                            <button class="px-4 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">Filter</button>
                            <button class="px-4 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">Export</button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs font-semibold text-slate-500 uppercase">
                                    <th class="px-6 py-4">Product Name</th>
                                    <th class="px-6 py-4">Category</th>
                                    <th class="px-6 py-4">Quantity</th>
                                    <th class="px-6 py-4">Price</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-sm">
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-semibold text-slate-900">Rice Cooker</td>
                                    <td class="px-6 py-4 text-slate-600">Appliances</td>
                                    <td class="px-6 py-4 font-semibold">12</td>
                                    <td class="px-6 py-4 font-semibold">₱2,500.00</td>
                                    <td class="px-6 py-4"><span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Available</span></td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        <button class="text-blue-600 hover:text-blue-800 font-semibold text-sm">Edit</button>
                                        <button class="text-red-600 hover:text-red-800 font-semibold text-sm">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($tab === 'shipments'): ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Shipment Center</h2>
                            <p class="text-sm text-slate-600">Track inbound and outbound shipments.</p>
                        </div>
                        <div class="flex gap-3">
                            <button class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">Inbound</button>
                            <button class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">Outbound</button>
                            <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">New Shipment</button>
                        </div>
                    </div>
                    <p class="text-slate-600">Shipment records will appear here.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>
