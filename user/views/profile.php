<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data
$UserID = $_SESSION['UserID'];
$sql = "SELECT FullName, Email, AccountType, Wallet FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $UserID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $_SESSION['Wallet'] = $user['Wallet']; // Update session wallet
} else {
    echo "Error fetching user data.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['recharge'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (empty($name)) {
        $error = "Name cannot be empty.";
    } else {
        // Update user data
        $updateSql = "UPDATE Users SET FullName = ?, Email = ? WHERE UserID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $name, $email, $UserID);

        if ($updateStmt->execute()) {
            $success = "Profile updated successfully.";
            // Refresh data to reflect changes
            $user['FullName'] = $name;
            $user['Email'] = $email;
            $_SESSION['Email'] = $email; // Update session email
        } else {
            $error = "Error updating profile.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recharge'])) {
    $rechargeValue = (int)$_POST['recharge'];

    if ($rechargeValue === 123456789) {
        $amountToAdd = 1000;
        $newWalletBalance = $user['Wallet'] + $amountToAdd;

        $updateWalletSql = "UPDATE Users SET Wallet = ? WHERE UserID = ?";
        $updateWalletStmt = $conn->prepare($updateWalletSql);
        $updateWalletStmt->bind_param("di", $newWalletBalance, $UserID);

        if ($updateWalletStmt->execute()) {
            $_SESSION['successRecharge'] = "Your wallet has been recharged by $$amountToAdd.";
            header("Location: profile.php"); // Redirect to avoid form resubmission
            exit();
        } else {
            $_SESSION['errorRecharge'] = "Failed to recharge your wallet. Please try again.";
            header("Location: profile.php"); // Redirect to avoid form resubmission
            exit();
        }
    } else {
        $_SESSION['errorRecharge'] = "Invalid recharge value. Please enter a number between 1 and 9.";
        header("Location: profile.php"); // Redirect to avoid form resubmission
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 flex">
    <!-- Dashboard -->
    <div class="w-1/4 bg-blue-700 h-screen sticky text-white p-6 flex flex-col">
        <h1 class="text-3xl font-bold mb-8 text-center">Dashboard</h1>
        <ul class="space-y-6">
			<li>
                <a href="./Rooms.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Rooms
                </a>
            </li>
            <li>
                <a href="./profile.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Profile
                </a>
            </li>
            <li>
                <a href="../Controller/logoutProcess.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Logout
                </a>
            </li>
            <li>
                <a href="./bookings.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Bookings
                </a>
            </li>
            <li>
                <a href="./transactions.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Transactions
                </a>
            </li>
        </ul>
    </div>

    <!-- Profile Content -->
    <div class="w-3/4 p-10 h-screen overflow-y-auto">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Your Profile</h2>

            <!-- Display Success/Error Message -->
            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif (isset($success)): ?>
                <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- User Information Card -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-md mb-6">
                <h3 class="text-xl font-bold text-gray-700 mb-4">Profile Details</h3>
                <table class="w-full text-left border-collapse">
                    <tr>
                        <th class="p-2 text-gray-600 border-b">Full Name</th>
                        <td class="p-2 border-b"><?php echo htmlspecialchars($user['FullName']); ?></td>
                    </tr>
                    <tr>
                        <th class="p-2 text-gray-600 border-b">Email</th>
                        <td class="p-2 border-b"><?php echo htmlspecialchars($user['Email']); ?></td>
                    </tr>
                    <tr>
                        <th class="p-2 text-gray-600 border-b">Account Type</th>
                        <td class="p-2 border-b"><?php echo htmlspecialchars($user['AccountType']); ?></td>
                    </tr>
                    <tr>
                        <th class="p-2 text-gray-600 border-b">Wallet Balance</th>
                        <td class="p-2 border-b">$<?php echo number_format($user['Wallet'], 2); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Update Button -->
            <button id="editButton" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">
                Update Profile
            </button>

            <!-- Update Form (Initially Hidden) -->
            <form id="updateForm" method="POST" action="" class="mt-6 hidden">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="w-full border border-gray-300 p-2 rounded mt-1"
                        value="<?php echo htmlspecialchars($user['FullName']); ?>" 
                        required
                    >
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="w-full border border-gray-300 p-2 rounded mt-1"
                        value="<?php echo htmlspecialchars($user['Email']); ?>" 
                        required
                    >
                </div>

                <button 
                    type="submit" 
                    class="bg-green-500 text-white font-bold py-2 px-4 rounded hover:bg-green-600 transition">
                    Save Changes
                </button>
            </form>

			 <!-- Recharge Account Form -->
			 <div class="bg-gray-50 p-6 rounded-lg shadow-md mt-6">
                <h3 class="text-xl font-bold text-gray-700 mb-4">Recharge Your Wallet</h3>
                <form method="POST" action="">
                    <label for="recharge" class="block text-gray-700 font-medium mb-2">
                        Enter your card value:
                    </label>
                    <input 
                        type="number" 
                        id="recharge" 
                        name="recharge" 
                        class="w-full border border-gray-300 p-2 rounded mb-4" 
                        min="0" 
                        max="999999999" 
                        required
                    >
                    <button 
                        type="submit" 
                        class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 transition">
                        Recharge
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script to Toggle Update Form -->
    <script>
        document.getElementById('editButton').addEventListener('click', function () {
            document.getElementById('updateForm').classList.toggle('hidden');
        });
    </script>
</body>
</html>
