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



// Retrieve pending hotel requests
$sql = "SELECT * FROM PendingHotel";
$result = $conn->query($sql);

echo "<h1>Pending Hotel Requests</h1>";
echo "<table class='requests-table'>
        <tr>
            <th>Full Name</th>
            <th>Hotel Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . $row['FullName'] . "</td>
            <td>" . $row['HotelName'] . "</td>
            <td>" . $row['Email'] . "</td>
            <td>" . $row['Phone'] . "</td>
            <td>
                <a href='../controller/approveHotel.php?id=" . $row['PendingID'] . "' class='approve-btn'>Approve</a> | 
                <a href='../controller/deleteRequest.php?id=" . $row['PendingID'] . "' class='delete-btn'>Delete</a>
            </td>
          </tr>";
}

echo "</table>";

$conn->close();
?>

<!-- Reference to the external CSS stylesheet -->
<link rel="stylesheet" href="../styles/hotelRequest.css">
