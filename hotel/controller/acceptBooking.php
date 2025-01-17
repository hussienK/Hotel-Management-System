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
        // Update hotel wallet (add the room price to the hotel's wallet)
        $update_wallet_sql = "UPDATE Hotels SET Wallet = Wallet + ? WHERE HotelID = ?";
        $update_stmt = $conn->prepare($update_wallet_sql);
        $update_stmt->bind_param('di', $booking['TotalPrice'], $hotel_id);
        $update_stmt->execute();

        // Update the booking status to 'ACCEPTED'
        $update_booking_sql = "UPDATE Bookings SET Status = 'ACCEPTED' WHERE BookingID = ?";
        $update_stmt = $conn->prepare($update_booking_sql);
        $update_stmt->bind_param('i', $booking_id);
        $update_stmt->execute();

        // Insert transaction record
        $insert_transaction_sql = "INSERT INTO Transactions (BookingID, Amount) VALUES (?, ?)";
        $transaction_stmt = $conn->prepare($insert_transaction_sql);
        $transaction_stmt->bind_param('id', $booking_id, $booking['TotalPrice']);
        $transaction_stmt->execute();

        // Commit transaction
        $conn->commit();

        // Send confirmation email via SendGrid API
        $email = new Mail();
        $email->setFrom(".", "Hotel Management");
        $email->setSubject("Booking Confirmation");
        $email->addTo($booking['Email'], $booking['FullName']);
        $email->addContent("text/html", "Your booking has been confirmed!<br>Room: {$booking['RoomNb']}<br>Check-in: {$booking['CheckInDate']}<br>Check-out: {$booking['CheckOutDate']}<br>Total Price: $ {$booking['TotalPrice']}");

        // Initialize SendGrid client and send the email
        $sendgrid = new \SendGrid('.'); // Use your API key here
        $response = $sendgrid->send($email);
        
        // Check if email was sent successfully
        if ($response->statusCode() == 202) {
            header("Location: ../views/manageBookings.php?message=Booking accepted successfully");
        } else {
            echo "Error sending email: " . $response->body();
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
