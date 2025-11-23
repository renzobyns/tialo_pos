<?php
include __DIR__ . '/../../includes/auth_check.php';
checkRole('Admin');
include __DIR__ . '/../../includes/db_connect.php';

$user_id = $_GET['id'] ?? null;
$user = null;
$is_edit = false;

if ($user_id) {
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header("Location: index.php?error=User not found");
        exit();
    }
    $is_edit = true;
}
?>
<?php
$page_title = ($is_edit ? 'Edit' : 'Add') . ' User - Tialo Japan Surplus';
include __DIR__ . '/../../includes/page_header.php';
?>
<body class="bg-slate-50 flex">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 page-header">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-500">Team Management</p>
                    <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <?php echo $is_edit ? 'Update User' : 'Add New User'; ?>
                    </h1>
                    <p class="text-sm text-slate-600">Fill in the information below to <?php echo $is_edit ? 'update this team member' : 'create a new team member'; ?>.</p>
                </div>
                <a href="/index.php?page=users" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to directory
                </a>
            </div>
        </header>

        <main class="flex-1 px-6 py-8">
            <div class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="border-b border-slate-100 px-8 py-5">
                    <h2 class="text-xl font-semibold text-slate-900"><?php echo $is_edit ? 'Profile Details' : 'New Account Details'; ?></h2>
                    <p class="text-sm text-slate-500 mt-1">All required fields are marked with an asterisk.</p>
                </div>
                <form method="POST" action="/index.php?page=users/process_user" class="px-8 py-6 space-y-6">
                    <?php if ($is_edit): ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="name" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.508 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Full Name<span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="John Dela Cruz" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0l-4 4m4-4l-4-4" /></svg>
                                Email Address<span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="admin@tialo.com" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 <?php echo $is_edit ? 'bg-slate-50 text-slate-500' : ''; ?>" <?php echo $is_edit ? 'readonly' : 'required'; ?>>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="role" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.508 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Role<span class="text-red-500">*</span>
                            </label>
                            <select id="role" name="role" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                                <option value="" disabled <?php echo empty($user['role']) ? 'selected' : ''; ?>>Select role</option>
                                <option value="Admin" <?php echo ($user['role'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="Cashier" <?php echo ($user['role'] ?? '') === 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Status
                            </label>
                            <div class="flex items-center gap-3 px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-sm text-slate-600">Active by default</span>
                            </div>
                        </div>
                    </div>

                    <?php if (!$is_edit): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 11c-1.1 0-2 .9-2 2v5h4v-5c0-1.1-.9-2-2-2z"/><path d="M6 11V7a6 6 0 1112 0v4"/></svg>
                                Password<span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="password" name="password" placeholder="At least 6 characters" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        </div>
                        <div class="space-y-2">
                            <label for="confirm_password" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 11-9-9"/></svg>
                                Confirm Password<span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500" required>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 11c-1.1 0-2 .9-2 2v5h4v-5c0-1.1-.9-2-2-2z"/><path d="M6 11V7a6 6 0 1112 0v4"/></svg>
                                New Password
                            </label>
                            <input type="password" id="password" name="password" placeholder="Leave blank to keep current" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="space-y-2">
                            <label for="confirm_password" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 11-9-9"/></svg>
                                Confirm New Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="/index.php?page=users" class="px-5 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition">Cancel</a>
                        <button type="submit" class="px-6 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                            <?php echo $is_edit ? 'Save Changes' : 'Create User'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
<?php include __DIR__ . '/../../includes/page_footer.php'; ?>
