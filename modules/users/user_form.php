<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> User - Tialo Japan Surplus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <?php include '../../includes/header.php'; ?>
    
    <main class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-bold text-slate-900 flex items-center space-x-3">
                <i class="fas fa-user-plus text-indigo-600"></i>
                <span><?php echo $is_edit ? 'Edit User' : 'Add New User'; ?></span>
            </h2>
            <a href="index.php" class="flex items-center space-x-2 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
        
        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <form method="POST" action="process_user.php" class="space-y-6">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>
                
                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-user mr-2 text-indigo-600"></i>Full Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter full name"
                        value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        required
                    >
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-600"></i>Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter email address"
                        value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        required
                        <?php echo $is_edit ? 'readonly' : ''; ?>
                    >
                </div>
                
                <!-- Password Fields -->
                <?php if (!$is_edit): ?>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-lock mr-2 text-indigo-600"></i>Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter password (min 6 characters)"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            required
                            minlength="6"
                        >
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-check-circle mr-2 text-indigo-600"></i>Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm password"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            required
                            minlength="6"
                        >
                    </div>
                <?php else: ?>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-lock mr-2 text-indigo-600"></i>New Password (Leave blank to keep current)
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter new password (min 6 characters)"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            minlength="6"
                        >
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-check-circle mr-2 text-indigo-600"></i>Confirm New Password
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm new password"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            minlength="6"
                        >
                    </div>
                <?php endif; ?>
                
                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-shield-alt mr-2 text-indigo-600"></i>Role <span class="text-red-500">*</span>
                    </label>
                    <select id="role" name="role" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        <option value="">Select a role</option>
                        <option value="Admin" <?php echo ($user['role'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Cashier" <?php echo ($user['role'] ?? '') === 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                    </select>
                </div>
                
                <!-- Form Actions -->
                <div class="flex gap-3 pt-6 border-t border-slate-200">
                    <button type="submit" class="flex-1 flex items-center justify-center space-x-2 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-semibold">
                        <i class="fas fa-save"></i>
                        <span><?php echo $is_edit ? 'Update User' : 'Create User'; ?></span>
                    </button>
                    <a href="index.php" class="flex-1 flex items-center justify-center space-x-2 bg-slate-300 text-slate-700 px-6 py-3 rounded-lg hover:bg-slate-400 transition font-semibold">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
    
    <script>
        // Validate password match on form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
