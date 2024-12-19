<?php
session_start();

// Check if the user is logged in and if the account type is hotel
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}

// Assuming data will be fetched from the database in the future
$hotel_name = "Hotel Name"; // This will be replaced with data from the database
$owner_name = $_SESSION['FullName']; // This should be retrieved from the session after login

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
            <h2><?php echo $hotel_name; ?></h2>
            <ul>
                <li><a href="Rooms.php">Manage Rooms</a></li>
                <li><a href="manage_bookings.php">Manage Bookings</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="offers.php">Offers & Discounts</a></li>
                <li><a href="profile.php">Profile Settings</a></li>
                <li><a style="background-color:rgb(241, 34, 34) " href="../controller/logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Welcome, <?php echo $owner_name; ?></h1>
            <p>Your hotel management dashboard.</p>

            <div class="dashboard-stats">
                <div class="stat">
                    <h3>Bookings Today</h3>
                    <p>Data will be displayed here</p>
                </div>
                <div class="stat">
                    <h3>Total Revenue</h3>
                    <p>Data will be displayed here</p>
                </div>
                <div class="stat">
                    <h3>Available Rooms</h3>
                    <p>Data will be displayed here</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
