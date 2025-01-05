<?php
session_start();
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ADLC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add custom animation -->
    <style>
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen">
    <!-- Background decoration -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-1/2 -right-1/2 w-96 h-96 rounded-full bg-blue-100 blur-3xl opacity-50"></div>
        <div class="absolute -bottom-1/2 -left-1/2 w-96 h-96 rounded-full bg-blue-100 blur-3xl opacity-50"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Logo or Icon -->
            <div class="text-center mb-8 float-animation">
                <div class="inline-block p-4 rounded-full bg-blue-100 mb-4">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-4xl font-bold text-blue-900">Welcome Back</h2>
                <p class="text-blue-600 mt-2 text-lg">Login to your account</p>
            </div>

            <!-- Main Form Container -->
            <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-8 transform transition-all duration-300 hover:shadow-2xl">
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded animate-fade-in">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded animate-fade-in">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="../config/auth.php" method="POST" class="space-y-6">
                    <!-- Email Input -->
                    <div class="transform transition-all duration-300 hover:scale-[1.01]">
                        <label class="block text-blue-900 text-base font-semibold mb-2" for="email">
                            Email Address
                        </label>
                        <input class="w-full px-4 py-3 rounded-lg border border-blue-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-300 text-base"
                               id="email" type="email" name="email" required
                               placeholder="your@email.com">
                    </div>

                    <!-- Password Input -->
                    <div class="transform transition-all duration-300 hover:scale-[1.01]">
                        <label class="block text-blue-900 text-sm font-semibold mb-2" for="password">
                            Password
                        </label>
                        <input class="w-full px-4 py-3 rounded-lg border border-blue-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all duration-300"
                               id="password" type="password" name="password" required
                               placeholder="••••••••">
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between text-base">
                        <div class="flex items-center transform transition-all duration-300 hover:scale-[1.02]">
                            <input type="checkbox" id="remember" name="remember" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-blue-300 rounded">
                            <label for="remember" class="ml-2 block text-blue-900">Remember me</label>
                        </div>
                        <a href="../components/forgot-password.php" class="text-blue-600 hover:text-blue-800 font-semibold transform transition-all duration-300 hover:scale-[1.02]">
                            Forgot Password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-lg transform transition-all duration-300 hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-lg"
                            type="submit" name="login">
                        Logins
                    </button>

                    <!-- Sign Up Link -->
                    <div class="text-center transform transition-all duration-300 hover:scale-[1.02]">
                        <a class="inline-block text-blue-600 hover:text-blue-800 font-semibold text-base"
                           href="SignupPage.php">
                           Don't have an account? <span class="underline">Sign up here</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-base text-blue-600/80">
                Protected by our <a href="#" class="underline">Privacy Policy</a> and <a href="#" class="underline">Terms of Service</a>
            </div>
        </div>
    </div>
</body>
</html> 