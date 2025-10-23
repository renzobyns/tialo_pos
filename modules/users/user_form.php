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
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/users.css">
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <main class="form-container">
        <div class="form-header">
            <h2><?php echo $is_edit ? 'Edit User' : 'Add New User'; ?></h2>
            <a href="index.php" class="btn-secondary">Back</a>
        </div>
        
        <form method="POST" action="process_user.php" class="form-box">
            <?php if ($is_edit): ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="create">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter full name"
                    value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter email address"
                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                    required
                    <?php echo $is_edit ? 'readonly' : ''; ?>
                >
            </div>
            
            <?php if (!$is_edit): ?>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter password (min 6 characters)"
                        required
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm password"
                        required
                        minlength="6"
                    >
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label for="password">New Password (Leave blank to keep current)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter new password (min 6 characters)"
                        minlength="6"
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm new password"
                        minlength="6"
                    >
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="">Select a role</option>
                    <option value="Admin" <?php echo ($user['role'] ?? '') === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="Cashier" <?php echo ($user['role'] ?? '') === 'Cashier' ? 'selected' : ''; ?>>Cashier</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <?php echo $is_edit ? 'Update User' : 'Create User'; ?>
                </button>
                <a href="index.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
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
