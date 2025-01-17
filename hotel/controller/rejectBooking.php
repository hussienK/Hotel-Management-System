<?php
session_start();
require '../../vendor/autoload.php'; // Composer autoload

use SendGrid\Mail\Mail;

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
               r.RoomNb, u.FullName, u.Email
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

    // Start transaction
    $conn->begin_transaction();
    try {
        // 1. Fetch current wallet balance of the user
        $user_wallet_sql = "SELECT Wallet FROM Users WHERE UserID = ?";
        $user_wallet_stmt = $conn->prepare($user_wallet_sql);
        $user_wallet_stmt->bind_param('i', $booking['UserID']);
        $user_wallet_stmt->execute();
        $user_wallet_result = $user_wallet_stmt->get_result();

        if ($user_wallet_result->num_rows > 0) {
            $user_wallet = $user_wallet_result->fetch_assoc();
            $new_wallet_balance = $user_wallet['Wallet'] + $booking['TotalPrice'];

            // 2. Update the user's wallet balance
            $update_wallet_sql = "UPDATE Users SET Wallet = ? WHERE UserID = ?";
            $update_wallet_stmt = $conn->prepare($update_wallet_sql);
            $update_wallet_stmt->bind_param('di', $new_wallet_balance, $booking['UserID']);
            $update_wallet_stmt->execute();

            // 3. Update the booking status to REJECTED
            $update_booking_sql = "UPDATE Bookings SET Status = 'REJECTED' WHERE BookingID = ?";
            $update_stmt = $conn->prepare($update_booking_sql);
            $update_stmt->bind_param('i', $booking_id);
            $update_stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Send rejection email via SendGrid API
            $email = new Mail();
            $email->setFrom(".", "Hotel Management");
            $email->setSubject("Booking Rejection Notice");
            $email->addTo($booking['Email'], $booking['FullName']);
            $email->addContent("text/html", "Dear {$booking['FullName']},<br>We regret to inform you that your booking request for Room {$booking['RoomNb']} has been rejected.<br>If you have any questions, please contact support.");

            // Initialize SendGrid client and send the email
            $sendgrid = new \SendGrid('.'); // Use your SendGrid API key here
            $response = $sendgrid->send($email);

            // Check if email was sent successfully
            if ($response->statusCode() == 202) {
                header("Location: ../views/manageBookings.php?message=Booking rejected successfully");
            } else {
                echo "Error sending email: " . $response->body();
            }
        } else {
            echo "User wallet not found.";
        }
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        echo "Error: {$e->getMessage()}";
    }
} else {
    echo "Booking not found or permission denied.";
}

$conn->close();
?>
