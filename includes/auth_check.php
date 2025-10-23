<?php
// Session check and role verification
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . dirname(dirname(__FILE__)) . "/index.php");
    exit();
}

// Function to check if user has required role
function checkRole($required_role) {
    if ($_SESSION['role'] !== $required_role) {
        header("Location: " . dirname(dirname(__FILE__)) . "/dashboard.php");
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
