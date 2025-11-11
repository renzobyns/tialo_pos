<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$tab = $_GET['tab'] ?? 'directory';
$subtab = $_GET['subtab'] ?? 'active';

$users_query = "SELECT * FROM users ORDER BY user_id DESC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Tialo Japan Surplus</title>
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
                        <h1 class="text-3xl font-bold text-slate-900">User Management</h1>
                        <p class="text-sm text-slate-600 mt-1">Manage team members and permissions</p>
                    </div>
                    <button class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Add User</span>
                    </button>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="border-t border-slate-200 bg-slate-50">
                <div class="px-8 py-3 overflow-x-auto">
                    <div class="flex items-center gap-3 min-w-max">
                        <a href="?tab=directory" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'directory' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Directory
                        </a>
                        <a href="?tab=roles" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'roles' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Roles & Permissions
                        </a>
                        <a href="?tab=logs" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-full transition whitespace-nowrap <?php echo $tab === 'logs' ? 'bg-red-600 text-white shadow border border-red-600' : 'bg-white text-slate-600 border border-transparent hover:border-slate-200 hover:text-slate-900'; ?>">
                            Activity Logs
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content -->
        <main class="flex-1 px-8 py-6">
            <?php if ($tab === 'directory'): ?>
                <div class="space-y-6">
                    <!-- Search and Filter -->
                    <div class="bg-white rounded-lg border border-slate-200 p-4">
                        <input type="text" placeholder="Search users..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    
                    <!-- Users Table -->
                    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Name</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Email</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Role</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-900"><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="px-3 py-1 <?php echo $user['role'] === 'Admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?> rounded-full text-xs font-semibold">
                                                <?php echo htmlspecialchars($user['role']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm"><span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Active</span></td>
                                        <td class="px-6 py-4 text-sm space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800">Edit</button>
                                            <button class="text-red-600 hover:text-red-800">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php elseif ($tab === 'roles'): ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-xl font-bold mb-6">Roles & Permissions</h2>
                    <p class="text-slate-600">Manage user roles and their permissions</p>
                </div>
            <?php elseif ($tab === 'logs'): ?>
                <div class="bg-white rounded-lg border border-slate-200 p-6">
                    <h2 class="text-xl font-bold mb-6">Activity Logs</h2>
                    <p class="text-slate-600">View user activity and security logs</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
