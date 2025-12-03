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
                <form method="POST" action="<?php echo APP_URL; ?>/public/index.php?page=users/process_user" class="px-8 py-6 space-y-6">
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
                            <div class="relative">
                                <input type="password" id="password" name="password" placeholder="At least 6 characters" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 pr-10" required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" id="togglePasswordCreate">
                                    <svg class="w-5 h-5 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="confirm_password" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 11-9-9"/></svg>
                                Confirm Password<span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 pr-10" required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" id="toggleConfirmPasswordCreate">
                                    <svg class="w-5 h-5 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label for="password_edit" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 11c-1.1 0-2 .9-2 2v5h4v-5c0-1.1-.9-2-2-2z"/><path d="M6 11V7a6 6 0 1112 0v4"/></svg>
                                New Password
                            </label>
                            <div class="relative">
                                <input type="password" id="password_edit" name="password" placeholder="Leave blank to keep current" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 pr-10">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" id="togglePasswordEdit">
                                    <svg class="w-5 h-5 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="confirm_password_edit" class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M21 12a9 9 0 11-9-9"/></svg>
                                Confirm New Password
                            </label>
                            <div class="relative">
                                <input type="password" id="confirm_password_edit" name="confirm_password" placeholder="Repeat new password" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500 pr-10">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" id="toggleConfirmPasswordEdit">
                                    <svg class="w-5 h-5 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle password visibility
    function togglePasswordVisibility(toggleBtn, passwordInput) {
        if (!toggleBtn || !passwordInput) return;
        
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon (change SVG)
            const svg = this.querySelector('svg');
            if (type === 'text') {
                // Show "eye-slash" icon (password visible)
                svg.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                `;
            } else {
                // Show "eye" icon (password hidden)
                svg.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    }

    // For Create User form
    const togglePasswordCreate = document.getElementById('togglePasswordCreate');
    const passwordCreate = document.getElementById('password');
    const toggleConfirmPasswordCreate = document.getElementById('toggleConfirmPasswordCreate');
    const confirmPasswordCreate = document.getElementById('confirm_password');

    togglePasswordVisibility(togglePasswordCreate, passwordCreate);
    togglePasswordVisibility(toggleConfirmPasswordCreate, confirmPasswordCreate);

    // For Edit User form
    const togglePasswordEdit = document.getElementById('togglePasswordEdit');
    const passwordEdit = document.getElementById('password_edit');
    const toggleConfirmPasswordEdit = document.getElementById('toggleConfirmPasswordEdit');
    const confirmPasswordEdit = document.getElementById('confirm_password_edit');

    togglePasswordVisibility(togglePasswordEdit, passwordEdit);
    togglePasswordVisibility(toggleConfirmPasswordEdit, confirmPasswordEdit);
});
</script>

<?php include __DIR__ . '/../../includes/page_footer.php'; ?>