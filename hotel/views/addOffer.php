<?php
session_start();
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Offer</title>
    <link rel="stylesheet" href="../styles/addOffer.css">
</head>
<body>
    <div class="container">
        <h1>Add a New Offer</h1>
        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">Offer added and room price updated successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <form method="POST" action="../controller/AddOfferController.php">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="discount">Discount Percentage:</label>
            <input type="number" id="discount" name="discount" min="1" max="99" required>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="room_number">Room Number:</label>
            <input type="number" id="room_number" name="room_number" required>

            <button type="submit">Add Offer</button>
        </form>
        <button onclick="window.location.href='offers.php'">Back to Offers</button>
    </div>
</body>
</html>
