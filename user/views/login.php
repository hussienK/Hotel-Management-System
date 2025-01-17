<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white shadow-lg rounded-lg p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">User Login</h1>
            <p class="text-gray-600 mt-2">Access your account to manage bookings, view your history, and more.</p>
        </div>

        <!-- Display Error Message -->
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo "<p class='text-red-500 text-center mb-4'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']); // Clear error after displaying
        }
        ?>

        <form action="../controller/loginProcess.php" method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-gray-700 font-medium">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email" 
                    required 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200"
                >
            </div>
            <div>
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password" 
                    required 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200"
                >
            </div>
            <div>
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                    Login
                </button>
            </div>
        </form>
        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Don't have an account? 
                <a href="register.php" class="text-blue-600 hover:underline">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
