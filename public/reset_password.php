<?php
/**
 * Password Reset Utility
 * Use this to set/reset user passwords
 * 
 * IMPORTANT: Delete this file after use for security!
 */

// Database connection
$host = 'localhost';
$db = 'tialo_posdb';
$user = 'root';
$password = '';

try {
    $conn = new mysqli($host, $user, $password, $db, 3307);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        
        // Validation
        if (empty($email) || empty($new_password) || empty($confirm_password)) {
            $error = "All fields are required";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters";
        } else {
            // Hash the password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            // Update the user
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $success = "Password updated successfully for: " . htmlspecialchars($email);
                    $success .= "<br><strong>You can now log in with:</strong><br>";
                    $success .= "Email: " . htmlspecialchars($email) . "<br>";
                    $success .= "Password: " . htmlspecialchars($new_password);
                } else {
                    $error = "User not found with email: " . htmlspecialchars($email);
                }
            } else {
                $error = "Error updating password: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    // Get all users for reference
    $users_result = $conn->query("SELECT user_id, name, email, role FROM users");
    $users = [];
    if ($users_result) {
        while ($row = $users_result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<?php
$page_title = 'Password Reset Utility - Tialo POS';
$page_styles = <<<EOT
<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #5568d3;
        }
        
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .users-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        
        .users-section h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .user-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .user-item strong {
            color: #667eea;
        }
        
        .user-item .role {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 10px;
        }
    </style>
EOT;
include __DIR__ . '/../src/includes/page_header.php';
?>
<body>
    <div class="container">
        <h1>Password Reset Utility</h1>
        
        <div class="warning">
            <strong>⚠️ Security Warning:</strong> This file should be deleted after use. Do not leave it on your server!
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                ✓ <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                ✗ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">User Email:</label>
                <input type="email" id="email" name="email" required placeholder="admin@tialo.com">
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required placeholder="Enter new password" minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password" minlength="6">
            </div>
            
            <button type="submit">Reset Password</button>
        </form>
        
        <?php if (!empty($users)): ?>
            <div class="users-section">
                <h3>System Users:</h3>
                <?php foreach ($users as $user): ?>
                    <div class="user-item">
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                        (<?php echo htmlspecialchars($user['email']); ?>)
                        <span class="role"><?php echo htmlspecialchars($user['role']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php include __DIR__ . '/../src/includes/page_footer.php'; ?>
