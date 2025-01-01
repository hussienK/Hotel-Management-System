<?php
session_start();

// Check if the user is logged in and if the account type is hotel
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}

// Database connection
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

// Retrieve the hotel name and wallet for the logged-in user
$hotel_name = "";
$owner_name = $_SESSION['FullName']; // User's full name from the session
$user_id = $_SESSION['UserID'];
$wallet = 0.0;
$available_rooms = 0;
$bookings_today = 0;

$sql = "SELECT h.Name, h.Wallet 
        FROM Hotels h
        JOIN Users u ON u.UserID = ? 
        WHERE h.HotelID = u.UserID"; // Assuming UserID in Users matches HotelID in Hotels

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hotel_name, $wallet);
$stmt->fetch();
$stmt->close();

// Count available rooms for the hotel
$sql = "SELECT COUNT(*) 
        FROM Rooms 
        WHERE HotelID = ? AND Availability = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($available_rooms);
$stmt->fetch();
$stmt->close();

// Count bookings today with 'ACCEPTED' status
$sql = "SELECT COUNT(*) 
        FROM Bookings b
        JOIN Rooms r ON b.RoomID = r.RoomID
        WHERE r.HotelID = ? 
        AND DATE(b.BookingDate) = CURDATE()
        AND b.Status = 'ACCEPTED'"; // Filter bookings with 'ACCEPTED' status

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bookings_today);
$stmt->fetch();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Owner Dashboard</title>
    <link rel="stylesheet" href="../styles/homePage.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2><?php echo htmlspecialchars($hotel_name, ENT_QUOTES, 'UTF-8'); ?></h2>
            <ul>
                <li><a href="Rooms.php">Manage Rooms</a></li>
                <li><a href="manageBookings.php">Manage Bookings</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="offers.php">Offers & Discounts</a></li>
                <li><a href="profile.php">Profile Settings</a></li>
                <li><a style="background-color:rgb(241, 34, 34)" href="../controller/logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($owner_name, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Your hotel management dashboard.</p>

            <div class="dashboard-stats">
                <div class="stat">
                    <h3>Bookings Today</h3>
                    <p><?php echo htmlspecialchars($bookings_today, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="stat">
                    <h3>Total Revenue</h3>
                    <p><?php echo number_format($wallet, 2); ?> USD</p>
                </div>
                <div class="stat">
                    <h3>Available Rooms</h3>
                    <p><?php echo htmlspecialchars($available_rooms, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
