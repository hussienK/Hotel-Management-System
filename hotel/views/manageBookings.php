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

// Fetch HotelID
$sql = "SELECT HotelID FROM Hotels WHERE Email = (SELECT Email FROM Users WHERE UserID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
$hotel_id = $result->fetch_assoc()['HotelID'];

// Fetch bookings by status
$statuses = ['ON', 'ACCEPTED', 'REJECTED'];
$bookings = [];
foreach ($statuses as $status) {
    $sql = "SELECT b.BookingID, b.BookingDate, b.CheckInDate, b.CheckOutDate, b.TotalPrice,
                   u.FullName, u.Email, r.RoomNb, r.RoomCapacity
            FROM Bookings b
            JOIN Users u ON b.UserID = u.UserID
            JOIN Rooms r ON b.RoomID = r.RoomID
            WHERE r.HotelID = ? AND b.Status = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('is', $hotel_id, $status);
    $stmt->execute();
    $bookings[$status] = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="../styles/manageBookings.css">
    <style>
        .hidden {
            display: none;
        }
        button {
            display: block;
            margin: 10px auto;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons button {
            background-color: #008CBA; /* Blue */
        }
        .action-buttons .reject {
            background-color: #f44336; /* Red */
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Bookings</h1>

    <!-- ON Status -->
    <h2>ON Status</h2>
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
            <?php while ($row = $bookings['ON']->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['RoomNb']) ?></td>
                    <td><?= htmlspecialchars($row['RoomCapacity']) ?></td>
                    <td><?= htmlspecialchars($row['BookingDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckInDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckOutDate']) ?></td>
                    <td><?= htmlspecialchars($row['TotalPrice']) ?></td>
                    <td class="action-buttons">
                        <!-- Accept Action (pass booking_id in query string) -->
                        <form method="POST" action="../controller/acceptBooking.php?booking_id=<?= $row['BookingID'] ?>">
                            <input type="hidden" name="booking_id" value="<?= $row['BookingID'] ?>">
                            <button type="submit">Accept</button>
                        </form>
                        <!-- Reject Action -->
                        <form method="POST" action="../controller/rejectBooking.php?booking_id=<?= $row['BookingID'] ?>">
                            <input type="hidden" name="booking_id" value="<?= $row['BookingID'] ?>">
                            <button type="submit" class="reject">Reject</button>
                        </form>
 
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Accepted Bookings -->
    <h2>Accepted Bookings</h2>
    <button id="toggleAccepted">Show/Hide Accepted</button>
    <table id="acceptedBookings" class="hidden">
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
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $bookings['ACCEPTED']->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['RoomNb']) ?></td>
                    <td><?= htmlspecialchars($row['RoomCapacity']) ?></td>
                    <td><?= htmlspecialchars($row['BookingDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckInDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckOutDate']) ?></td>
                    <td><?= htmlspecialchars($row['TotalPrice']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Rejected Bookings -->
    <h2>Rejected Bookings</h2>
    <button id="toggleRejected">Show/Hide Rejected</button>
    <table id="rejectedBookings" class="hidden">
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
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $bookings['REJECTED']->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['RoomNb']) ?></td>
                    <td><?= htmlspecialchars($row['RoomCapacity']) ?></td>
                    <td><?= htmlspecialchars($row['BookingDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckInDate']) ?></td>
                    <td><?= htmlspecialchars($row['CheckOutDate']) ?></td>
                    <td><?= htmlspecialchars($row['TotalPrice']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script src="../scripts/manageBookings.js"></script>
</body>
</html>
