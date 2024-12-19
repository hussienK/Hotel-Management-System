<?php
session_start(); // Start session for admin login

// Database connection details
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

// Only admin should have access to this page


if (isset($_GET['id'])) {
    $pendingID = $_GET['id'];

    // Delete the request
    $sqlDelete = "DELETE FROM PendingHotel WHERE PendingID = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $pendingID);
    if ($stmtDelete->execute()) {
        echo "<script>
                alert('Request deleted successfully.');
                window.location.href = '../views/hotelRequest.php';
              </script>";
    } else {
        echo "<script>
                alert('Failed to delete request.');
                window.location.href = '../views/hotelRequest.php';
              </script>";
    }
} else {
    echo "<script>
            alert('Invalid request.');
            window.location.href = '../views/hotelRequest.php';
          </script>";
}

$conn->close();
?>
