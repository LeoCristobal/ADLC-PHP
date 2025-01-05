<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';
require_once '../config/auth.php';

// Check if user is logged in with all required session data
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: LoginPage.php");
    exit();
}

// Get current date and time
$currentDate = date('Y-m-d');
$currentTime = date('H:i');
 
// Create user_logs table if it doesn't exist
try {
    // First drop the existing table
    $pdo->exec("DROP TABLE IF EXISTS user_logs");
    
    // Then create a new table with all required columns
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(255) NOT NULL,
        user VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) {
    // Log the error but don't show it to users
    error_log("Error creating table: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ADLC System</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

    <div class="min-h-screen p-6">
        <!-- Header -->
        <header class="max-w-7xl mx-auto mb-8 flex justify-between items-center bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-blue-900">Dashboard</h1>
                    <p class="text-blue-600">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                </div>
            </div>
            <a href="logout.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-all duration-300 hover:shadow-lg flex items-center gap-2">
                <span>Logout</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </a>
        </header>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto bg-white/80 backdrop-blur-lg rounded-2xl shadow-xl p-6">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-blue-600 text-white">
                            <th class="px-6 py-3 text-left rounded-tl-lg">Id</th>
                            <th class="px-6 py-3 text-left">User Email</th>
                            <th class="px-6 py-3 text-left">User</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left rounded-tr-lg">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Insert current login
                            $stmt = $pdo->prepare("INSERT INTO user_logs (user_email, user, date, time) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$_SESSION['user_email'], $_SESSION['username'], $currentDate, $currentTime]);

                            // Fetch logs
                            $stmt = $pdo->prepare("SELECT * FROM user_logs WHERE user_email = ? ORDER BY date DESC, time DESC");
                            $stmt->execute([$_SESSION['user_email']]);
                            $id = 1;
                            
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr class="border-b border-blue-100 hover:bg-blue-50/50 transition-colors duration-200">
                                    <td class="px-6 py-4"><?php echo $id++; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['user_email']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['user']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['date']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['time']); ?></td>
                                </tr>
                            <?php endwhile;

                        } catch (PDOException $e) {
                            echo '<tr><td colspan="4" class="px-6 py-4 text-center text-red-500 bg-red-50">';
                            echo '<div class="flex items-center justify-center gap-2">';
                            echo '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">';
                            echo '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
                            echo '</svg>';
                            echo 'Error: ' . htmlspecialchars($e->getMessage());
                            echo '</div></td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>