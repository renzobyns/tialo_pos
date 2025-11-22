<?php
include '../../includes/auth_check.php';
checkRole('Admin');
include '../../includes/db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role']);
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        header("Location: /index.php?page=users/user_form&error=All fields are required");
        exit();
    }
    
    if ($password !== $confirm_password) {
        header("Location: /index.php?page=users/user_form&error=Passwords do not match");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: /index.php?page=users/user_form&error=Password must be at least 6 characters");
        exit();
    }
    
    // Check if email already exists
    $check_query = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        header("Location: /index.php?page=users/user_form&error=Email already exists");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        header("Location: /index.php?page=users&success=User created successfully");
    } else {
        header("Location: /index.php?page=users/user_form&error=Failed to create user");
    }
    exit();
}

elseif ($action === 'update') {
    $user_id = (int)$_POST['user_id'];
    $name = sanitize($_POST['name']);
    $role = sanitize($_POST['role']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($name) || empty($role)) {
        header("Location: /index.php?page=users/user_form&id=$user_id&error=All fields are required");
        exit();
    }
    
    // If password is provided, validate it
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            header("Location: /index.php?page=users/user_form&id=$user_id&error=Passwords do not match");
            exit();
        }
        
        if (strlen($password) < 6) {
            header("Location: /index.php?page=users/user_form&id=$user_id&error=Password must be at least 6 characters");
            exit();
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = "UPDATE users SET name = ?, role = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $name, $role, $hashed_password, $user_id);
    } else {
        $query = "UPDATE users SET name = ?, role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $name, $role, $user_id);
    }
    
    if ($stmt->execute()) {
        header("Location: /index.php?page=users&success=User updated successfully");
    } else {
        header("Location: /index.php?page=users/user_form&id=$user_id&error=Failed to update user");
    }
    exit();
}

elseif ($action === 'delete') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : (int)($_GET['id'] ?? 0);
    if ($user_id <= 0) {
        header("Location: /index.php?page=users&error=Invalid user id");
        exit();
    }
    
    // Prevent deleting the current user
    if ($user_id === $_SESSION['user_id']) {
        header("Location: /index.php?page=users&error=Cannot delete your own account");
        exit();
    }
    
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        header("Location: /index.php?page=users&success=User deleted successfully");
    } else {
        header("Location: /index.php?page=users&error=Failed to delete user");
    }
    exit();
}

header("Location: /index.php?page=users");
exit();
?>
