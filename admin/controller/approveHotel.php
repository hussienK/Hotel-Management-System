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

        // Insert the user into the Users table
        $sqlInsertUser = "INSERT INTO Users (FullName, Email, Password, AccountType, Wallet) VALUES (?, ?, ?, 'HotelOwner', 0)";
        $stmtInsertUser = $conn->prepare($sqlInsertUser);
        $stmtInsertUser->bind_param("sss", $hotelRequest['FullName'], $hotelRequest['Email'], $hotelRequest['Password']);
        $stmtInsertUser->execute();

        // Get the last inserted UserID
        $userID = $conn->insert_id;

        // Insert data into the Hotels table using the same UserID
        $sqlInsertHotel = "INSERT INTO Hotels (HotelID, Name, Address, Phone, Email, Wallet) VALUES (?, ?, ?, ?, ?, 0)";
        $stmtInsertHotel = $conn->prepare($sqlInsertHotel);
        $stmtInsertHotel->bind_param("issss", $userID, $hotelRequest['HotelName'], $hotelRequest['Address'], $hotelRequest['Phone'], $hotelRequest['Email']);
        $stmtInsertHotel->execute();

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
