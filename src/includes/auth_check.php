<?php
// Session check and role verification


if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php?page=auth/login");
    exit();
}

// Function to check if user has required role
function checkRole($required_role) {
    if ($_SESSION['role'] !== $required_role) {
        header("Location: /index.php?page=dashboard");
        exit();
    }
}

// Function to check if user is admin
function isAdmin() {
    return $_SESSION['role'] === 'Admin';
}

// Function to check if user is cashier
function isCashier() {
    return $_SESSION['role'] === 'Cashier';
}
?>
