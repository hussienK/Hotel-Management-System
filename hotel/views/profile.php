<?php
session_start();

// Check if the user is logged in and is a hotel owner
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'HotelOwner') {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get hotel and user details
$UserID = $_SESSION['UserID'];
$sql = "
    SELECT h.*, u.FullName, u.Email AS UserEmail 
    FROM Hotels h 
    INNER JOIN Users u ON h.Email = u.Email 
    WHERE u.UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

// Update hotel and user email
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $hotelEmail = $_POST['hotel_email'];

    $conn->begin_transaction();

    try {
        // Update hotel details
        $update_hotel_sql = "UPDATE Hotels SET Name = ?, Address = ?, Phone = ?, Email = ? WHERE HotelID = ?";
        $update_hotel_stmt = $conn->prepare($update_hotel_sql);
        $update_hotel_stmt->bind_param('ssssi', $name, $address, $phone, $hotelEmail, $hotel['HotelID']);
        $update_hotel_stmt->execute();

        // Update user email
        $update_user_sql = "UPDATE Users SET Email = ? WHERE UserID = ?";
        $update_user_stmt = $conn->prepare($update_user_sql);
        $update_user_stmt->bind_param('si', $hotelEmail, $UserID);
        $update_user_stmt->execute();

        $conn->commit();
        header('Location: profile.php?success=1');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Failed to update details: " . $e->getMessage();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Profile</title>
    <link rel="stylesheet" href="../styles/profile.css">
</head>
<body>
    <div class="container">
        <a href="homePage.php" class="back-home-btn">← Back</a>
        <header>
            <h1>Hotel Profile</h1>
        </header>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($_GET['success'])) echo "<p class='success'>Profile updated successfully!</p>"; ?>
        <form method="POST" class="profile-form">
            <div class="form-group">
                <label for="name">Hotel Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($hotel['Name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($hotel['Address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($hotel['Phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="hotel_email">Hotel Email:</label>
                <input type="email" id="hotel_email" name="hotel_email" value="<?php echo htmlspecialchars($hotel['Email']); ?>" required>
            </div>
            <button type="submit" class="save-btn">Save Changes</button>
        </form>
    </div>
</body>
</html>
