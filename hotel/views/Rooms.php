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

// Get hotel ID of the logged-in user
$UserID = $_SESSION['UserID'];
$sql = "SELECT HotelID FROM Hotels WHERE Email = (SELECT Email FROM Users WHERE UserID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
$hotel_id = $result->fetch_assoc()['HotelID'];

// Fetch rooms for this hotel
$rooms_sql = "SELECT * FROM Rooms WHERE HotelID = ?";
$rooms_stmt = $conn->prepare($rooms_sql);
$rooms_stmt->bind_param('i', $hotel_id);
$rooms_stmt->execute();
$rooms_result = $rooms_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="../styles/rooms.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Manage Your Rooms</h1>
            <a href="addRoom.php" class="add-room-btn">+ Add Room</a>
        </header>
        <div class="rooms-list">
            <h2>Your Rooms</h2>
            <table>
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = $rooms_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['RoomNb']); ?></td>
                            <td><?php echo htmlspecialchars($room['RoomCapacity']); ?></td>
                            <td><?php echo htmlspecialchars($room['Price']); ?></td>
                            <td><?php echo $room['Availability'] ? 'Available' : 'Unavailable'; ?></td>
                            <td><?php echo htmlspecialchars($room['Description']); ?></td>
                            <td>
                                <!-- Remove Button -->
                                <form action="../controller/removeRoom.php" method="POST" class="action-form">
                                    <input type="hidden" name="room_id" value="<?php echo $room['RoomID']; ?>">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                                <!-- Edit Button -->
                                <form action="editRoom.php" method="GET" class="action-form">
                                    <input type="hidden" name="room_id" value="<?php echo $room['RoomID']; ?>">
                                    <button type="submit" class="edit-btn">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
