<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

// Get all users
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-4xl font-bold text-slate-900 flex items-center space-x-3">
                <i class="fas fa-users-cog text-indigo-600"></i>
                <span>User Management</span>
            </h2>
            <a href="user_form.php" class="flex items-center space-x-2 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                <i class="fas fa-user-plus"></i>
                <span>Add New User</span>
            </a>
        </div>
        
        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                    <p class="text-emerald-800 font-semibold"><?php echo htmlspecialchars($_GET['success']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-600 p-4 rounded-lg">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                    <p class="text-red-800 font-semibold"><?php echo htmlspecialchars($_GET['error']); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="w-full bg-white rounded-lg overflow-hidden shadow-lg">
                <thead>
                    <tr class="bg-slate-100 border-b-2 border-slate-200">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">User ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Role</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Created Date</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-sm text-slate-900 font-semibold">#<?php echo $user['user_id']; ?></td>
                            <td class="px-6 py-4 text-sm text-slate-900"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="px-6 py-4 text-sm">
                                <?php 
                                $role_class = $user['role'] === 'Admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800';
                                $role_icon = $user['role'] === 'Admin' ? 'fa-crown' : 'fa-cashier';
                                ?>
                                <span class="inline-flex items-center space-x-1 <?php echo $role_class; ?> px-3 py-1 rounded-full font-semibold">
                                    <i class="fas <?php echo $role_icon; ?>"></i>
                                    <span><?php echo htmlspecialchars($user['role']); ?></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="user_form.php?id=<?php echo $user['user_id']; ?>" class="inline-flex items-center space-x-1 bg-amber-100 text-amber-700 px-3 py-1 rounded hover:bg-amber-200 transition">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                <a href="process_user.php?action=delete&id=<?php echo $user['user_id']; ?>" class="inline-flex items-center space-x-1 bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition" onclick="return confirm('Delete this user? This action cannot be undone.');">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete</span>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
