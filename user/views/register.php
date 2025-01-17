<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white shadow-lg rounded-lg p-8">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">User Registration</h1>
            <p class="text-gray-600 mt-2">Create an account to access all features.</p>
        </div>

        <!-- Display Error Message -->
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo "<p class='text-red-500 text-center mb-4'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']); // Clear error after displaying
        }
        ?>

        <form action="../controller/registerProcess.php" method="POST" class="space-y-6">
            <!-- Full Name -->
            <div>
                <label for="full-name" class="block text-gray-700 font-medium">Full Name</label>
                <input 
                    type="text" 
                    id="full-name" 
                    name="full_name" 
                    placeholder="Enter your full name" 
                    required 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200"
                >
            </div>

            <!-- Email -->
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

            <!-- Password -->
            <div>
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Create a password" 
                    required 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200"
                >
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm-password" class="block text-gray-700 font-medium">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm-password" 
                    name="confirm_password" 
                    placeholder="Confirm your password" 
                    required 
                    class="w-full px-4 py-2 mt-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200"
                >
            </div>

			<p class="text-gray-600 text-center mt-2">
				Already a user? 
				<a href="./login.php" class="text-blue-600 hover:underline hover:text-blue-700">login.</a>
			</p>


            <!-- Submit Button -->
            <div>
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition"
                >
                    Register
                </button>
            </div>
        </form>
    </div>
</body>
</html>
