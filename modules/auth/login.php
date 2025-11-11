<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tialo Japan Surplus POS</title>
    <?php include '../../includes/tailwind-cdn.html'; ?>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-700 px-8 py-12 text-center">
                <div class="flex justify-center mb-4">
                    <i class="fas fa-store text-emerald-400 text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Tialo Japan</h1>
                <p class="text-slate-300 text-sm">Surplus POS System</p>
            </div>
            
            <!-- Form Section -->
            <div class="px-8 py-8">
                <!-- Error Alert -->
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start space-x-3">
                        <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                        <div>
                            <p class="text-red-800 text-sm font-medium">Login Failed</p>
                            <p class="text-red-700 text-xs"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login_process.php" class="space-y-5">
                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-slate-500"></i>Email Address
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="admin@tialo.com" 
                            required
                            autofocus
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                        >
                    </div>
                    
                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-lock mr-2 text-slate-500"></i>Password
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="••••••••" 
                            required
                            class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none transition"
                        >
                    </div>
                    
                    <!-- Login Button -->
                    <button 
                        type="submit" 
                        class="w-full mt-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white font-semibold rounded-lg transition transform hover:scale-105 active:scale-95 flex items-center justify-center space-x-2"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Sign In</span>
                    </button>
                </form>
            </div>
            
            <!-- Footer Section -->
            <div class="bg-slate-50 px-8 py-6 border-t border-slate-200">
                <p class="text-xs text-slate-600 mb-3 font-semibold">Demo Credentials:</p>
                <div class="space-y-2 bg-slate-100 rounded-lg p-3">
                    <p class="text-xs text-slate-700">
                        <span class="font-mono bg-white px-2 py-1 rounded text-emerald-700">admin@tialo.com</span>
                    </p>
                    <p class="text-xs text-slate-700">
                        <span class="font-mono bg-white px-2 py-1 rounded text-emerald-700">admin123</span>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer Text -->
        <div class="text-center mt-6 text-slate-400 text-xs">
            <p>© 2025 Tialo Japan Surplus - POS System</p>
        </div>
    </div>
</body>
</html>
