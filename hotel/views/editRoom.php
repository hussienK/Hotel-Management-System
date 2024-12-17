<?php
session_start();

// Check if the user is logged in and has the correct account type
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Hotel') {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'hotel_management');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted to update the room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form input
    $room_id = isset($_POST['room_id']) ? $_POST['room_id'] : null;
    $room_number = isset($_POST['room_number']) ? $_POST['room_number'] : null;
    $capacity = isset($_POST['capacity']) ? $_POST['capacity'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $availability = isset($_POST['availability']) ? $_POST['availability'] : null;

    // Check if all required fields are provided
    if ($room_id && $room_number && $capacity && $price && $description !== null && $availability !== null) {
        // Update room details in the database
        $sql = "UPDATE Rooms SET RoomNb = ?, RoomCapacity = ?, Price = ?, Description = ?, Availability = ? WHERE RoomID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sidsii', $room_number, $capacity, $price, $description, $availability, $room_id);
        
        if ($stmt->execute()) {
            $success_message = "Room updated successfully.";
            // Redirect to rooms.php after successful update
            header('Location: rooms.php');
            exit();
        } else {
            $error_message = "Failed to update the room: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// Fetch room details for editing (GET method)
if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];
    
    // Fetch the room details from the database
    $sql = "SELECT * FROM Rooms WHERE RoomID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $room = $result->fetch_assoc();
    } else {
        echo "Room not found.";
        exit();
    }
    $stmt->close();
} 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room</title>
    <link rel="stylesheet" href="../styles/editRoom.css">
</head>
<body>
    <div class="container">
        <h1>Edit Room</h1>
        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="editRoom.php" method="POST">
            <!-- Hidden field for room_id -->
            <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room['RoomID']); ?>">
            
            <label for="room_number">Room Number:</label>
            <input type="text" id="room_number" name="room_number" value="<?php echo htmlspecialchars($room['RoomNb']); ?>" required>
            
            <label for="capacity">Room Capacity:</label>
            <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($room['RoomCapacity']); ?>" required>
            
            <label for="price">Price per Night:</label>
            <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($room['Price']); ?>" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($room['Description']); ?></textarea>
            
            <label for="availability">Availability:</label>
            <select id="availability" name="availability">
                <option value="1" <?php echo $room['Availability'] ? 'selected' : ''; ?>>Available</option>
                <option value="0" <?php echo !$room['Availability'] ? 'selected' : ''; ?>>Not Available</option>
            </select>
            
            <button type="submit" class="save-btn">Save Changes</button>
        </form>
        <a href="rooms.php" class="back-link">Back to Rooms</a>
    </div>
</body>
</html>
