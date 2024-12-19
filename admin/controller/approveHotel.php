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

    // Retrieve the pending hotel details
    $sql = "SELECT * FROM PendingHotel WHERE PendingID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pendingID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $hotelRequest = $result->fetch_assoc();

        // Insert data into the Hotels table
        $sqlInsertHotel = "INSERT INTO Hotels (Name, Address, Phone, Email, Wallet) VALUES (?, ?, ?, ?, 0)";
        $stmtInsertHotel = $conn->prepare($sqlInsertHotel);
        $stmtInsertHotel->bind_param("ssss", $hotelRequest['HotelName'], $hotelRequest['Address'], $hotelRequest['Phone'], $hotelRequest['Email']);
        $stmtInsertHotel->execute();

        // Insert the user into the Users table with the same email and details
        $sqlInsertUser = "INSERT INTO Users (FullName, Email, Password, AccountType, Wallet) VALUES (?, ?, ?, 'HotelOwner', 0)";
        $stmtInsertUser = $conn->prepare($sqlInsertUser);
        $stmtInsertUser->bind_param("sss", $hotelRequest['FullName'], $hotelRequest['Email'], $hotelRequest['Password']);
        $stmtInsertUser->execute();

        // Delete the request after approval
        $sqlDelete = "DELETE FROM PendingHotel WHERE PendingID = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $pendingID);
        $stmtDelete->execute();

        echo "<script>
                alert('Hotel request approved successfully.');
                window.location.href = '../views/hotelRequest.php';
              </script>";
    } else {
        echo "<script>
                alert('Request not found.');
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
