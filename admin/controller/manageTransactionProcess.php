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

// Fetch all transactions with booking details
$sql = "SELECT Transactions.TransactionID, Transactions.Amount, Transactions.TransactionDate, 
               Bookings.BookingID, Bookings.CheckInDate, Bookings.CheckOutDate, 
               Users.FullName AS UserName, Hotels.Name AS HotelName
        FROM Transactions
        INNER JOIN Bookings ON Transactions.BookingID = Bookings.BookingID
        INNER JOIN Users ON Bookings.UserID = Users.UserID
        INNER JOIN Hotels ON Bookings.RoomID = Hotels.HotelID";
$result = $conn->query($sql);
$transactions = $result->fetch_all(MYSQLI_ASSOC);

// Handle transaction delete
if (isset($_POST['delete_transaction'])) {
    $transactionID = $_POST['transaction_id'];

    $sql = "DELETE FROM Transactions WHERE TransactionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $transactionID);

    if ($stmt->execute()) {
        echo "<script>alert('Transaction deleted successfully.'); window.location.href = '../views/manage_transactions.php';</script>";
    } else {
        echo "<script>alert('Error deleting transaction.'); window.location.href = '../views/manage_transactions.php';</script>";
    }
    $stmt->close();
}

// Close the connection
$conn->close();
?>