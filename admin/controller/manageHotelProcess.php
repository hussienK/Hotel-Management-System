<?php

// Check if the user is logged in and is an admin
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all approved hotels
$sql = "SELECT Hotels.HotelID, Hotels.Name, Hotels.Address, Hotels.Phone, Hotels.Email, Users.FullName AS OwnerName 
        FROM Hotels 
        INNER JOIN Users ON Hotels.HotelID = Users.UserID";
$result = $conn->query($sql);
$hotels = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all pending hotel requests
$sqlPending = "SELECT * FROM PendingHotel";
$resultPending = $conn->query($sqlPending);
$pendingRequests = $resultPending->fetch_all(MYSQLI_ASSOC);

// Handle hotel edit
if (isset($_POST['edit_hotel'])) {
    $hotelID = $_POST['hotel_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $sql = "UPDATE Hotels SET Name = ?, Address = ?, Phone = ?, Email = ? WHERE HotelID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $address, $phone, $email, $hotelID);

    if ($stmt->execute()) {
        echo "<script>alert('Hotel updated successfully.'); window.location.href = '../views/manage_hotels.php';</script>";
    } else {
        echo "<script>alert('Error updating hotel.'); window.location.href = '../views/manage_hotels.php';</script>";
    }
    $stmt->close();
}

// Handle hotel delete
if (isset($_POST['delete_hotel'])) {
    $hotelID = $_POST['hotel_id'];

    $sql = "DELETE FROM Hotels WHERE HotelID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hotelID);

    if ($stmt->execute()) {
        echo "<script>alert('Hotel deleted successfully.'); window.location.href = '../views/manage_hotels.php';</script>";
    } else {
        echo "<script>alert('Error deleting hotel.'); window.location.href = '../views/manage_hotels.php';</script>";
    }
    $stmt->close();
}

// Close the connection
$conn->close();
?>