<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'Admin') {
        header("Location: dashboard.php");
    } else {
        header("Location: modules/pos/index.php");
    }
    exit();
}

// Redirect to login
header("Location: modules/auth/login.php");
exit();
?>
