<?php
session_start();
include '../src/includes/db_connect.php';

echo "<h1>Tialo POS - Authentication Diagnostic</h1>";
echo "<hr>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
if ($conn->connect_error) {
    echo "<p style='color: red;'><strong>FAILED:</strong> " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'><strong>SUCCESS:</strong> Connected to database</p>";
}

// Test 2: Check if users table exists
echo "<h2>2. Users Table Test</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p style='color: green;'><strong>SUCCESS:</strong> Users table exists with " . $row['count'] . " users</p>";
} else {
    echo "<p style='color: red;'><strong>FAILED:</strong> " . $conn->error . "</p>";
}

// Test 3: Check admin user
echo "<h2>3. Admin User Test</h2>";
$result = $conn->query("SELECT user_id, name, email, role, password FROM users WHERE email = 'admin@tialo.com'");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p style='color: green;'><strong>SUCCESS:</strong> Admin user found</p>";
    echo "<ul>";
    echo "<li><strong>User ID:</strong> " . $user['user_id'] . "</li>";
    echo "<li><strong>Name:</strong> " . $user['name'] . "</li>";
    echo "<li><strong>Email:</strong> " . $user['email'] . "</li>";
    echo "<li><strong>Role:</strong> " . $user['role'] . "</li>";
    echo "<li><strong>Password Hash:</strong> " . substr($user['password'], 0, 30) . "...</li>";
    echo "<li><strong>Hash Type:</strong> " . (strpos($user['password'], '$2y$') === 0 ? 'bcrypt (correct)' : 'INCORRECT - not bcrypt') . "</li>";
    echo "</ul>";
    
    // Test password verification
    echo "<h3>Password Verification Test</h3>";
    $test_password = 'admin123';
    $verify_result = password_verify($test_password, $user['password']);
    echo "<p>Testing password: <strong>" . $test_password . "</strong></p>";
    echo "<p style='color: " . ($verify_result ? 'green' : 'red') . ";'>";
    echo "<strong>" . ($verify_result ? 'SUCCESS' : 'FAILED') . ":</strong> ";
    echo ($verify_result ? 'Password matches!' : 'Password does NOT match the hash');
    echo "</p>";
    
} else {
    echo "<p style='color: red;'><strong>FAILED:</strong> Admin user not found in database</p>";
    echo "<p>Available users:</p>";
    $result = $conn->query("SELECT user_id, name, email, role FROM users");
    if ($result && $result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . $row['email'] . " (" . $row['role'] . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>No users found in database at all!</p>";
    }
}

// Test 4: Check PHP error log location
echo "<h2>4. PHP Error Log Location</h2>";
$error_log = ini_get('error_log');
echo "<p><strong>Error Log Path:</strong> " . ($error_log ? $error_log : 'Not configured (check php.ini)') . "</p>";

// Test 5: Session test
echo "<h2>5. Session Test</h2>";
$_SESSION['test'] = 'working';
echo "<p style='color: green;'><strong>SUCCESS:</strong> Sessions are working</p>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If admin user is not found, run the SQL script: <code>scripts/01_create_database.sql</code></li>";
echo "<li>If password hash is incorrect, update it with the correct bcrypt hash</li>";
echo "<li>Check the error log at the path shown above for detailed error messages</li>";
echo "<li>Try logging in again and check the error log for '[v0]' debug messages</li>";
echo "</ol>";
?>
