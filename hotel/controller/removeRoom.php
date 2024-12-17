<?php
session_start();

// Check if the user is logged in and is a hotel owner
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Hotel') {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];

    // Delete the room
    $sql = "DELETE FROM Rooms WHERE RoomID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $room_id);

    if ($stmt->execute()) {
        // Redirect to rooms page with success message in URL
        header("Location: ../views/rooms.php?message=Room-is-removed");
        exit(); // Ensure no further code is executed after redirection
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>
