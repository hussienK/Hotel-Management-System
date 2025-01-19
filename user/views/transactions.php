<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'user') {
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

$userID = $_SESSION['UserID'];

// Fetch transactions for the user
$sql = "
    SELECT 
        Transactions.TransactionID, Transactions.Amount, Transactions.TransactionDate,
        Bookings.CheckInDate, Bookings.CheckOutDate, Bookings.Status,
        Rooms.RoomNb,
        Hotels.Name AS HotelName
    FROM Transactions
    INNER JOIN Bookings ON Transactions.BookingID = Bookings.BookingID
    INNER JOIN Rooms ON Bookings.RoomID = Rooms.RoomID
    INNER JOIN Hotels ON Rooms.HotelID = Hotels.HotelID
    WHERE Bookings.UserID = ?
    ORDER BY Transactions.TransactionDate DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Transactions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <!-- Sidebar/Navbar -->
    <div class="w-1/4 bg-blue-700 h-screen sticky text-white p-6 flex flex-col">
        <h1 class="text-3xl font-bold mb-8 text-center">Dashboard</h1>
        <ul class="space-y-6">
            <li><a href="./Rooms.php" class="block text-lg font-medium hover:text-blue-300 transition">Rooms</a></li>
            <li><a href="./profile.php" class="block text-lg font-medium hover:text-blue-300 transition">Profile</a></li>
            <li><a href="../Controller/logoutProcess.php" class="block text-lg font-medium hover:text-blue-300 transition">Logout</a></li>
            <li><a href="./bookings.php" class="block text-lg font-medium hover:text-blue-300 transition">Bookings</a></li>
            <li><a href="./transactions.php" class="block text-lg font-medium hover:text-blue-300 transition">Transactions</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow h-screen overflow-y-auto p-6">
        <div class="container mx-auto">
            <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-12">Your Transactions</h1>
            
            <!-- Transaction Cards -->
            <div class="space-y-8">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="relative flex bg-white shadow-md rounded-lg overflow-hidden hover:shadow-lg transition duration-300">
                            <div class="p-6 flex-grow">
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Hotel: <?php echo htmlspecialchars($row['HotelName']); ?></h2>
                                <p class="text-gray-600"><span class="font-semibold">Room Number:</span> <?php echo htmlspecialchars($row['RoomNb']); ?></p>
                                <p class="text-gray-600"><span class="font-semibold">Check-In:</span> <?php echo htmlspecialchars($row['CheckInDate']); ?></p>
                                <p class="text-gray-600"><span class="font-semibold">Check-Out:</span> <?php echo htmlspecialchars($row['CheckOutDate']); ?></p>
                                <p class="text-gray-600"><span class="font-semibold">Transaction Amount:</span> $<?php echo number_format($row['Amount'], 2); ?></p>
                                <p class="text-gray-600"><span class="font-semibold">Transaction Date:</span> <?php echo htmlspecialchars($row['TransactionDate']); ?></p>
                                <p class="text-gray-600"><span class="font-semibold">Booking Status:</span>
                                    <span class="px-2 py-1 rounded font-semibold text-sm 
                                    <?php 
                                        if ($row['Status'] === 'ON') echo 'bg-blue-100 text-blue-800';
                                        elseif ($row['Status'] === 'ACCEPTED') echo 'bg-green-100 text-green-800';
                                        elseif ($row['Status'] === 'REJECTED'  || $row == 'CANCELLED') echo 'bg-red-100 text-red-800';
                                    ?>">
                                        <?php echo htmlspecialchars($row['Status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-gray-800 text-center text-lg">You have no transactions at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
