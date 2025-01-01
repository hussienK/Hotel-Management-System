<?php
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: ../views/login.php');
    exit;
}

$userID = $_SESSION['UserID']; // User ID from session
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve Hotel ID using User ID
$sqlHotelID = "
    SELECT Hotels.HotelID 
    FROM Hotels 
    INNER JOIN Users ON Users.UserID = Hotels.OwnerUserID 
    WHERE Users.UserID = ?";
$stmtHotelID = $conn->prepare($sqlHotelID);
$stmtHotelID->bind_param("i", $userID);
$stmtHotelID->execute();
$resultHotelID = $stmtHotelID->get_result();

if ($resultHotelID->num_rows > 0) {
    $row = $resultHotelID->fetch_assoc();
    $hotelID = $row['HotelID'];
} else {
    header('Location: ../views/addOffer.php?error=Hotel not found for the user.');
    exit;
}
$stmtHotelID->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $discount = $_POST['discount'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $roomNumber = $_POST['room_number'];

    if ($discount < 1 || $discount > 99) {
        header('Location: ../views/addOffer.php?error=Discount must be between 1 and 99.');
        exit;
    }

    $sqlRoomID = "SELECT RoomID FROM Rooms WHERE RoomNb = ? AND HotelID = ? AND Availability = TRUE";
    $stmtRoomID = $conn->prepare($sqlRoomID);
    $stmtRoomID->bind_param("ii", $roomNumber, $hotelID);
    $stmtRoomID->execute();
    $resultRoomID = $stmtRoomID->get_result();

    if ($resultRoomID->num_rows > 0) {
        $room = $resultRoomID->fetch_assoc();
        $roomID = $room['RoomID'];

        $sqlOffer = "INSERT INTO Offers (HotelID, RoomID, Title, Description, DiscountPercentage, StartDate, EndDate) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtOffer = $conn->prepare($sqlOffer);
        $stmtOffer->bind_param("iissdds", $hotelID, $roomID, $title, $description, $discount, $startDate, $endDate);

        if ($stmtOffer->execute()) {
            $sqlRoomPrice = "UPDATE Rooms SET Price = Price - (Price * ? / 100) WHERE RoomID = ?";
            $stmtRoomPrice = $conn->prepare($sqlRoomPrice);
            $stmtRoomPrice->bind_param("di", $discount, $roomID);

            if ($stmtRoomPrice->execute()) {
                header('Location: ../views/addOffer.php?success=1');
            } else {
                header('Location: ../views/addOffer.php?error=Failed to update room price.');
            }
        } else {
            header('Location: ../views/addOffer.php?error=Failed to add offer.');
        }

        $stmtOffer->close();
        $stmtRoomPrice->close();
    } else {
        header('Location: ../views/addOffer.php?error=Room not found or unavailable.');
    }

    $stmtRoomID->close();
}

$conn->close();
?>
