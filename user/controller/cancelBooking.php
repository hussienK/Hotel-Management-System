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

// Validate input
if (!isset($_POST['bookingID'])) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: bookings.php');
    exit;
}

$bookingID = intval($_POST['bookingID']);
$userID = $_SESSION['UserID'];

// Fetch the booking details
$sql = "SELECT TotalPrice, Status FROM Bookings WHERE BookingID = ? AND UserID = ? AND Status = 'ON'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bookingID, $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Booking not found or cannot be cancelled.";
    header('Location: bookings.php');
    exit;
}

$booking = $result->fetch_assoc();
$totalPrice = floatval($booking['TotalPrice']);

// Update booking status to "Cancelled"
$sql = "UPDATE Bookings SET Status = 'CANCELLED' WHERE BookingID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bookingID);
$stmt->execute();

// Refund the user
$sql = "UPDATE Users SET Wallet = Wallet + ? WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("di", $totalPrice, $userID);
$stmt->execute();

// Success message
$_SESSION['success'] = "Booking cancelled successfully. The amount of $" . number_format($totalPrice, 2) . " has been refunded to your wallet.";
header('Location: ../views/bookings.php');
exit;

// Close connection
$conn->close();
?>
