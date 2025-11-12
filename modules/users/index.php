<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$role_filter = $_GET['role'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$allowed_roles = ['Admin', 'Cashier'];
$query = "SELECT * FROM users";
$conditions = [];
$params = [];
$types = '';

if (in_array($role_filter, $allowed_roles, true)) {
    $conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if ($search !== '') {
    $conditions[] = "(name LIKE ? OR email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY user_id DESC';

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users_result = $stmt->get_result();
} else {
    $users_result = $conn->query($query);
}
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
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 page-header">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Administration</p>
                    <h1 class="text-3xl font-bold text-slate-900">User Directory</h1>
                    <p class="text-sm text-slate-600">Quickly filter and manage team members</p>
                </div>
                <a href="user_form.php" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add User
                </a>
            </div>
        </header>
        
        <main class="flex-1 px-6 py-6 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <form class="flex-1" method="GET">
                    <div class="flex items-center gap-3">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or email" class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">Search</button>
                        <?php if ($search !== ''): ?>
                            <a href="index.php?role=<?php echo urlencode($role_filter); ?>" class="text-sm text-slate-500 hover:text-red-600">Reset</a>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($role_filter); ?>">
                </form>
                <div class="flex flex-wrap items-center gap-3">
                    <?php
                    $filter_links = [
                        'all' => 'All Users',
                        'Admin' => 'Admins',
                        'Cashier' => 'Cashiers',
                    ];
                    foreach ($filter_links as $value => $label):
                        $active = $role_filter === $value || ($value === 'all' && !in_array($role_filter, $allowed_roles, true));
                    ?>
                        <a href="?role=<?php echo urlencode($value); ?>&search=<?php echo urlencode($search); ?>" class="px-4 py-2 rounded-full border text-sm font-semibold transition <?php echo $active ? 'bg-red-600 border-red-600 text-white shadow' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'; ?>">
                            <?php echo $label; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase">
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Role</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if ($users_result && $users_result->num_rows > 0): ?>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td class="px-6 py-4 text-sm text-slate-600"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $user['role'] === 'Admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Active</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-3">
                                        <a href="user_form.php?id=<?php echo $user['user_id']; ?>" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                                        <form method="POST" action="process_user.php" class="inline" onsubmit="return confirm('Delete this user?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">No users found for the selected filters.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
