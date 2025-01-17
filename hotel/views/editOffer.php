<?php
session_start();
// Check if the user is logged in and is a HotelOwner
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: offers.php'); // If no OfferID is passed, redirect to the offers page
    exit;
}

$offerID = $_GET['id'];
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

// Fetch the offer details based on OfferID
$sql = "SELECT * FROM Offers WHERE OfferID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $offerID);
$stmt->execute();
$offer = $stmt->get_result()->fetch_assoc();

if (!$offer) {
    header('Location: offers.php'); // If no offer is found, redirect to the offers page
    exit;
}

// Get the current room price and old discount percentage
$roomID = $offer['RoomID'];

// Fetch the room details
$sql = "SELECT Price FROM Rooms WHERE RoomID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $roomID);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
$priceNow = $room['Price'];

$oldDiscount = $offer['DiscountPercentage']; // Store old discount to calculate the original price

$stmt->close();

// Handle form submission for updating the offer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $newDiscount = $_POST['discount'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Validate the new discount percentage to ensure it's between 1 and 99
    if ($newDiscount < 1 || $newDiscount > 99) {
        $error_message = "Discount percentage must be between 1% and 99%.";
    } else {
        // 1. Calculate the original price using the old discount
        $originalPrice = $priceNow / (1 - ($oldDiscount / 100));

        // 2. Apply the new discount to the original price
        $newPrice = $originalPrice * (1 - ($newDiscount / 100));

        // Update the offer details
        $sql = "UPDATE Offers SET Title = ?, Description = ?, DiscountPercentage = ?, StartDate = ?, EndDate = ? WHERE OfferID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdsdi", $title, $description, $newDiscount, $startDate, $endDate, $offerID);
        $stmt->execute();

        // Update the room price based on the new discount
        $sql = "UPDATE Rooms SET Price = ? WHERE RoomID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $newPrice, $roomID);
        $stmt->execute();

        $stmt->close();

        // Redirect back to the offers page after successful update
        header('Location: offers.php');
        exit;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Offer</title>
    <link rel="stylesheet" href="../styles/editoffers.css">
</head>
<body>
    <div class="container">
        <h1>Edit Offer</h1>
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($offer['Title']); ?>" required><br>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($offer['Description']); ?></textarea><br>

            <label for="discount">Discount Percentage (%):</label>
            <input type="number" id="discount" name="discount" value="<?php echo htmlspecialchars($offer['DiscountPercentage']); ?>" required min="1" max="99" step="1"><br>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($offer['StartDate']); ?>" required><br>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($offer['EndDate']); ?>" required><br>

            <button type="submit">Apply</button>
            <button type="button" onclick="window.location.href='offers.php'">Back</button>
        </form>
    </div>
</body>
</html>
