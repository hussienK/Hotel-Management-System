<?php
session_start();

// Ensure the user is logged in and is a hotel owner
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
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

// Get the hotel ID from session
$hotel_id = $_SESSION['UserID']; // Assuming the hotel owner is logged in and has a matching UserID

// Fetch transactions for the hotel
$sql = "SELECT t.TransactionID, t.Amount, t.TransactionDate, b.BookingID, b.UserID, b.TotalPrice, 
               b.CheckInDate, b.CheckOutDate, r.RoomNb, r.RoomCapacity
        FROM Transactions t
        JOIN Bookings b ON t.BookingID = b.BookingID
        JOIN Rooms r ON b.RoomID = r.RoomID
        WHERE r.HotelID = ?"; // Filter by the hotel's ID
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if transactions exist
if ($result->num_rows > 0) {
    echo "<h1>Transaction Details for Your Hotel</h1>";
    echo "<table class='transactions-table'>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Booking ID</th>
                    <th>Room Number</th>
                    <th>Room Capacity</th>
                    <th>Total Price</th>
                    <th>Amount</th>
                    <th>Check-in Date</th>
                    <th>Check-out Date</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";

    // Loop through each transaction and display the details
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['TransactionID']) . "</td>
                <td>" . htmlspecialchars($row['BookingID']) . "</td>
                <td>" . htmlspecialchars($row['RoomNb']) . "</td>
                <td>" . htmlspecialchars($row['RoomCapacity']) . "</td>
                <td>" . htmlspecialchars($row['TotalPrice']) . "</td>
                <td>" . htmlspecialchars($row['Amount']) . "</td>
                <td>" . htmlspecialchars($row['CheckInDate']) . "</td>
                <td>" . htmlspecialchars($row['CheckOutDate']) . "</td>
                <td>" . htmlspecialchars($row['TransactionDate']) . "</td>
              </tr>";
    }

    echo "</tbody>
        </table>";
} else {
    echo "<p>No transactions found for your hotel.</p>";
}

$conn->close();
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Details</title>
    <link rel="stylesheet" href="../styles/transactions.css"> <!-- Link to the CSS file -->
</head>
