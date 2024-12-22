<?php
session_start();

if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'hotel_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$UserID = $_SESSION['UserID'];
$sql = "SELECT HotelID FROM Hotels WHERE Email = (SELECT Email FROM Users WHERE UserID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
$hotel_id = $result->fetch_assoc()['HotelID'];

$sql = "SELECT b.BookingID, b.BookingDate, b.CheckInDate, b.CheckOutDate, b.TotalPrice,
               u.FullName, u.Email, r.RoomNb, r.RoomCapacity
        FROM Bookings b
        JOIN Users u ON b.UserID = u.UserID
        JOIN Rooms r ON b.RoomID = r.RoomID
        WHERE r.HotelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $hotel_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="../styles/manageBookings.css">
</head>
<body>
<div class="container">
    <h1>Manage Bookings</h1>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Email</th>
                <th>Room Number</th>
                <th>Capacity</th>
                <th>Booking Date</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $bookings->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['RoomNb']) ?></td>
                    <td><?= htmlspecialchars($row['RoomCapacity']) ?></td>
                    <td><?= htmlspecialchars($row['BookingDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckInDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckOutDate']) ?></td>
                    <td><?= htmlspecialchars($row['TotalPrice']) ?></td>
                    <td>
                        <a href="../controller/acceptBooking.php?booking_id=<?= $row['BookingID'] ?>" class="accept-btn">Accept</a>
                        <a href="../controller/rejectBooking.php?booking_id=<?= $row['BookingID'] ?>" class="reject-btn">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php $conn->close(); ?>
