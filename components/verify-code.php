<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';

// Check if email exists in session
if (!isset($_SESSION['verify_email'])) {
    header('Location: ../Pages/LoginPage.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input');
            const form = document.getElementById('verify-form');

            // Function to distribute pasted code across inputs
            function handlePastedCode(pastedCode) {
                const digits = pastedCode.slice(0, 6).split('');
                inputs.forEach((input, index) => {
                    if (digits[index]) {
                        input.value = digits[index];
                    }
                });

                // Auto submit if complete code is pasted
                if (digits.length >= 6) {
                    form.submit();
                }
            }

            // Handle paste event on any input
            inputs.forEach((input, index) => {
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                    // Remove any non-numeric characters
                    const cleanPastedText = pastedText.replace(/\D/g, '');
                    handlePastedCode(cleanPastedText);
                });

                // Handle input
                input.addEventListener('input', function(e) {
                    if (e.target.value.length === 1) {
                        if (index < 5) {
                            inputs[index + 1].focus();
                        }
                        // Check if all inputs are filled
                        const allFilled = Array.from(inputs).every(input => input.value.length === 1);
                        if (allFilled) {
                            form.submit();
                        }
                    }
                });

                // Handle backspace
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });

            // Add paste event listener to the container
            const container = document.querySelector('.code-container');
            container.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                // Remove any non-numeric characters
                const cleanPastedText = pastedText.replace(/\D/g, '');
                handlePastedCode(cleanPastedText);
            });
        });
    </script>
</head>
<body class="bg-blue-50">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white bg-opacity-95 rounded-2xl shadow-xl overflow-hidden p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold mb-6 text-blue-800">
                    Verify Your Email
                </h2>
                <p class="text-gray-600 mb-6">
                    Enter the 6-digit code sent to your email address:<br>
                    <span class="font-semibold"><?php echo $_SESSION['verify_email']; ?></span>
                </p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form id="verify-form" action="verify-code-handler.php" method="POST" class="space-y-6">
                <div class="code-container flex justify-between gap-2" tabindex="0">
                    <?php for($i = 0; $i < 6; $i++): ?>
                        <input
                            type="text"
                            name="code[]"
                            maxlength="1"
                            pattern="[0-9]"
                            inputmode="numeric"
                            class="code-input w-12 h-12 text-center text-2xl font-bold bg-white border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition-colors"
                            required
                        >
                    <?php endfor; ?>
                </div>
                
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                >
                    Verify Email
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="resend-code.php" class="text-blue-600 hover:text-blue-800 font-semibold">
                    Resend Code
                </a>
            </div>
        </div>
    </div>
</body>
</html> 