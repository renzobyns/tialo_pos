<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tialo Japan Surplus - POS System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <h1>Tialo Japan Surplus</h1>
                <span class="badge">POS System</span>
            </div>
            
            <nav class="navbar-menu">
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <a href="/tialo_pos/dashboard.php" class="nav-link">Dashboard</a>
                    <a href="/tialo_pos/modules/inventory/index.php" class="nav-link">Inventory</a>
                    <a href="/tialo_pos/modules/reports/index.php" class="nav-link">Reports</a>
                    <a href="/tialo_pos/modules/users/index.php" class="nav-link">Users</a>
                <?php endif; ?>
                
                <a href="/tialo_pos/modules/pos/index.php" class="nav-link">POS</a>
            </nav>
            
            <div class="navbar-right">
                <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="/tialo_pos/modules/auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>
