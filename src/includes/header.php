<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: /index.php?page=auth/login");
    exit();
}
?>
<nav class="bg-gradient-to-r from-slate-900 to-slate-800 shadow-lg border-b border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="text-white font-bold text-xl">
                    <i class="fas fa-store text-emerald-500"></i> Tialo Japan
                </div>
            </div>
            
            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-1">
                <?php if ($_SESSION['role'] === 'Admin'): ?>
                    <a href="/index.php?page=dashboard" 
                       class="px-4 py-2 rounded-lg text-slate-200 hover:bg-slate-700 transition">
                        <i class="fas fa-chart-line mr-2"></i>Dashboard
                    </a>
                    <a href="/index.php?page=pos" 
                       class="px-4 py-2 rounded-lg text-slate-200 hover:bg-slate-700 transition">
                        <i class="fas fa-cash-register mr-2"></i>POS
                    </a>
                    <a href="/index.php?page=inventory" 
                       class="px-4 py-2 rounded-lg text-slate-200 hover:bg-slate-700 transition">
                        <i class="fas fa-boxes mr-2"></i>Inventory
                    </a>
                    <a href="/index.php?page=reports" 
                       class="px-4 py-2 rounded-lg text-slate-200 hover:bg-slate-700 transition">
                        <i class="fas fa-file-chart-line mr-2"></i>Reports
                    </a>
                    <a href="/index.php?page=users" 
                       class="px-4 py-2 rounded-lg text-slate-200 hover:bg-slate-700 transition">
                        <i class="fas fa-users mr-2"></i>Users
                    </a>
                <?php else: ?>
                    <a href="/index.php?page=pos" 
                       class="px-4 py-2 rounded-lg text-slate-200 hover:bg-slate-700 transition">
                        <i class="fas fa-cash-register mr-2"></i>POS
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <div class="text-sm text-slate-300">
                    <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($_SESSION['name']); ?>
                    <span class="ml-2 px-2 py-1 rounded-full text-xs font-semibold 
                                 <?php echo $_SESSION['role'] === 'Admin' ? 'bg-purple-600 text-white' : 'bg-blue-600 text-white'; ?>">
                        <?php echo $_SESSION['role']; ?>
                    </span>
                </div>
                <a href="/index.php?page=auth/logout" 
                   class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white transition font-medium">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>
