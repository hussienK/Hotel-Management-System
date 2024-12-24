<?php
session_start();
require '../../vendor/autoload.php'; // Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: ../views/login.php');
    exit;
}

// Get booking ID from query string
$booking_id = intval($_GET['booking_id']);

// Get HotelID from session
$hotel_id = $_SESSION['UserID'];

// Fetch booking details
$sql = "SELECT b.BookingID, b.UserID, b.RoomID, b.TotalPrice, b.CheckInDate, b.CheckOutDate, 
               r.RoomNb, r.Price, u.FullName, u.Email
        FROM Bookings b
        JOIN Rooms r ON b.RoomID = r.RoomID
        JOIN Users u ON b.UserID = u.UserID
        WHERE b.BookingID = ? AND r.HotelID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $booking_id, $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();

    // Update wallet and room status transactionally
    $conn->begin_transaction();
    try {
        $update_wallet_sql = "UPDATE Hotels SET Wallet = Wallet + ? WHERE HotelID = ?";
        $update_stmt = $conn->prepare($update_wallet_sql);
        $update_stmt->bind_param('di', $booking['Price'], $hotel_id);
        $update_stmt->execute();

        $update_room_sql = "UPDATE Rooms SET Availability = 0 WHERE RoomID = ?";
        $update_stmt = $conn->prepare($update_room_sql);
        $update_stmt->bind_param('i', $booking['RoomID']);
        $update_stmt->execute();

        $conn->commit();

        // Send confirmation email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hotel.finder.website@gmail.com';
        $mail->Password = '12345678Hotel'; // Use a secure App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('hotel.finder.website@gmail.com', 'Hotel Management');
        $mail->addAddress($booking['Email']);
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation';
        $mail->Body = "Your booking has been confirmed!<br>Room: {$booking['RoomNb']}<br>Check-in: {$booking['CheckInDate']}<br>Check-out: {$booking['CheckOutDate']}<br>Total Price: $ {$booking['TotalPrice']}";

        $mail->send();

        header("Location: ../views/manageBookings.php?message=Booking accepted successfully");
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: {$e->getMessage()}";
    }
} else {
    echo "Booking not found or permission denied.";
}
$conn->close();
?>
