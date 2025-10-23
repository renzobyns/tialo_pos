<?php
session_start();
include '../../includes/db_connect.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Email and password are required.';
    header("Location: login.php");
    exit();
}

error_log("[v0] Login attempt for email: " . $email);

// Query user from database
$query = "SELECT user_id, name, email, password, role FROM users WHERE email = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("[v0] Database prepare error: " . $conn->error);
    $_SESSION['login_error'] = 'Database error: ' . $conn->error;
    header("Location: login.php");
    exit();
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    error_log("[v0] Database execute error: " . $stmt->error);
    $_SESSION['login_error'] = 'Database error: ' . $stmt->error;
    header("Location: login.php");
    exit();
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("[v0] User not found for email: " . $email);
    $_SESSION['login_error'] = 'Invalid email or password.';
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();
error_log("[v0] User found: " . $user['email'] . " with role: " . $user['role']);

error_log("[v0] Stored hash: " . substr($user['password'], 0, 20) . "...");
error_log("[v0] Password verify result: " . (password_verify($password, $user['password']) ? 'true' : 'false'));

// Verify password
if (!password_verify($password, $user['password'])) {
    error_log("[v0] Password verification failed for user: " . $email);
    $_SESSION['login_error'] = 'Invalid email or password.';
    header("Location: login.php");
    exit();
}

// Set session variables
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

error_log("[v0] Login successful for user: " . $email . " with role: " . $user['role']);

// Redirect based on role
if ($user['role'] === 'Admin') {
    header("Location: ../../dashboard.php");
} else {
    header("Location: ../pos/index.php");
}
exit();
?>
