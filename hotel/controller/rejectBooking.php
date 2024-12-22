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
$booking_id = $_GET['booking_id'];

// Get hotel ID from logged-in user
$hotel_id = $_SESSION['UserID']; // Assuming UserID is linked to the hotel

// Fetch booking details
$sql = "SELECT b.BookingID, b.UserID, b.RoomID, b.TotalPrice, b.CheckInDate, b.CheckOutDate, r.RoomNb, r.Price, u.FullName, u.Email
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
    $user_id = $booking['UserID'];
    $user_email = $booking['Email'];
    $room_price = $booking['Price'];
    $total_price = $booking['TotalPrice'];

    // Reject booking
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Add room price to user's wallet
        $update_wallet_sql = "UPDATE Users SET Wallet = Wallet + ? WHERE UserID = ?";
        $update_stmt = $conn->prepare($update_wallet_sql);
        $update_stmt->bind_param('di', $room_price, $user_id);
        $update_stmt->execute();

        // Send rejection email to user
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hotel.finder.website@gmail.com';
            $mail->Password = '12345678Hotel'; // Use an App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('hotel.finder.website@gmail.com', 'Hotel Management');
            $mail->addAddress($user_email);
            $mail->isHTML(true);
            $mail->Subject = 'Booking Rejected';
            $mail->Body = "Unfortunately, your booking has been rejected.<br>Booking Details:<br>Room: {$booking['RoomNb']}<br>Check-in: {$booking['CheckInDate']}<br>Check-out: {$booking['CheckOutDate']}<br>Amount refunded: $$room_price";

            $mail->send();

            // Update room status to available
            $update_room_sql = "UPDATE Rooms SET Availability = 1 WHERE RoomID = ?";
            $update_stmt = $conn->prepare($update_room_sql);
            $update_stmt->bind_param('i', $booking['RoomID']);
            $update_stmt->execute();

            // Redirect back to manage bookings page
            header("Location:../views/manageBookings.php?message=Booking rejected successfully");
        } catch (Exception $e) {
            echo "Error sending email: {$mail->ErrorInfo}";
        }
    }
} else {
    echo "Booking not found or you do not have permission to manage it.";
}

$conn->close();
?>
