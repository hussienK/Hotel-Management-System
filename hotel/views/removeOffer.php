<?php
session_start();
// Check if the user is logged in and is a HotelOwner
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}

// Get the UserID from the session
$userID = $_SESSION['UserID'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the OfferID from the query string
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: offers.php'); // If no OfferID is passed, redirect to the offers page
    exit;
}

$offerID = $_GET['id'];

// Fetch the offer details to get the room ID, current price, and discount
$sql = "SELECT RoomID, DiscountPercentage FROM Offers WHERE OfferID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $offerID);
$stmt->execute();
$stmt->bind_result($roomID, $oldDiscount);
$stmt->fetch();
$stmt->close();

// If no offer is found, redirect to the offers page
if (!$roomID) {
    header('Location: offers.php');
    exit;
}

// Fetch the current price of the room
$sql = "SELECT Price FROM Rooms WHERE RoomID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $roomID);
$stmt->execute();
$stmt->bind_result($priceNow);
$stmt->fetch();
$stmt->close();

// Calculate the original price using the old discount
$originalPrice = $priceNow / (1 - ($oldDiscount / 100));

// Begin transaction to ensure data consistency
$conn->begin_transaction();

try {
    // 1. Delete the offer from the Offers table
    $sql = "DELETE FROM Offers WHERE OfferID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $offerID);
    $stmt->execute();
    $stmt->close();

    // 2. Update the room price with the original price
    $sql = "UPDATE Rooms SET Price = ? WHERE RoomID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $originalPrice, $roomID);
    $stmt->execute();
    $stmt->close();

    // Commit the transaction
    $conn->commit();

    // Redirect back to the offers page
    header('Location: offers.php');
    exit;

} catch (Exception $e) {
    // Rollback the transaction if any error occurs
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>
