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

// Get the HotelID for the logged-in user
$sql = "SELECT HotelID FROM Hotels WHERE OwnerUserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID); // Bind the UserID to the query
$stmt->execute();
$stmt->bind_result($hotelID);
$stmt->fetch();
$stmt->close(); // Close the statement after fetching the result

// If no hotel is found for the user, redirect to an error page
if (!$hotelID) {
    header('Location: error.php');
    exit;
}

// SQL query to fetch offers for the specific hotel
$sql = "SELECT * FROM Offers WHERE HotelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hotelID); // Bind the HotelID to the query
$stmt->execute();
$result = $stmt->get_result();
$stmt->close(); // Close the statement after fetching the result

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offers Management</title>
    <link rel="stylesheet" href="../styles/offers.css">
</head>
<body>
    <div class="container">
        <h1>Offers by Your Hotel</h1>
        <button onclick="window.location.href='addOffer.php'">Add Offer</button>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Discount</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Title']); ?></td>
                    <td><?php echo htmlspecialchars($row['Description']); ?></td>
                    <td><?php echo htmlspecialchars($row['DiscountPercentage']) . '%'; ?></td>
                    <td><?php echo htmlspecialchars($row['StartDate']); ?></td>
                    <td><?php echo htmlspecialchars($row['EndDate']); ?></td>
                    <td>
                        <button onclick="window.location.href='editOffer.php?id=<?php echo $row['OfferID']; ?>'">Edit</button>
                        <button onclick="window.location.href='removeOffer.php?id=<?php echo $row['OfferID']; ?>'">Remove</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
