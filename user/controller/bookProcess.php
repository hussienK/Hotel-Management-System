<?php
session_start();
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function float_to_number($num) {
    if (isset($num)) {
        $price = $num;
        $cleanedString = str_replace(',', '', $price); // Remove commas
        return floatval($cleanedString);
    }
    return 0;
}

// Initialize variables for error handling
$error = "";
$success = "";

// Get user input
$userID = $_SESSION['UserID'];
$roomID = $_POST['roomID'];
$checkInDate = $_POST['checkInDate'];
$checkOutDate = $_POST['checkOutDate'];
$totalPrice = float_to_number($_POST['totalPrice']);

// Check if the room is available during the reservation time
$sql = "
    SELECT * 
    FROM Bookings 
    WHERE RoomID = ? 
    AND Status = 'ON' 
    AND (CheckInDate < ? AND CheckOutDate > ?)
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $roomID, $checkOutDate, $checkInDate);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $error = "The room is unavailable during the selected dates.";
} else {
    // Fetch user's wallet balance
    $sql = "SELECT Wallet FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $walletBalance = float_to_number($user['Wallet']);

        // Check if user has enough money
        if ($walletBalance >= $totalPrice) {
            // Deduct money from the user's wallet
            $newWalletBalance = $walletBalance - $totalPrice;
            $sql = "UPDATE Users SET Wallet = ? WHERE UserID = ?";
			$_SESSION['Wallet'] = $newWalletBalance;
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $newWalletBalance, $userID);
            $stmt->execute();

            // Insert the booking into the Bookings table
            $sql = "INSERT INTO Bookings (UserID, RoomID, BookingDate, CheckInDate, CheckOutDate, TotalPrice, Status) 
                    VALUES (?, ?, CURDATE(), ?, ?, ?, 'ON')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissd", $userID, $roomID, $checkInDate, $checkOutDate, $totalPrice);
            $stmt->execute();

            $success = "Room booked successfully!";
        } else {
            $error = "Insufficient balance in your wallet.";
        }
    } else {
        $error = "Error fetching user details. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-6 w-96 text-center">
        <?php if (!empty($error)): ?>
            <h1 class="text-xl font-bold text-red-500 mb-4">Booking Failed</h1>
            <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($error); ?></p>
            <a href="../views/Rooms.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Back to Rooms
            </a>
        <?php elseif (!empty($success)): ?>
            <h1 class="text-xl font-bold text-green-500 mb-4">Booking Successful</h1>
            <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($success); ?></p>
            <a href="../views/Rooms.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Back to Rooms
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
