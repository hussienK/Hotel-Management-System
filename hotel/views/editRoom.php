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
    $room_id = isset($_POST['room_id']) ? $_POST['room_id'] : null;
    $room_number = isset($_POST['room_number']) ? $_POST['room_number'] : null;
    $capacity = isset($_POST['capacity']) ? $_POST['capacity'] : null;
    $price = isset($_POST['price']) ? $_POST['price'] : null;
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $availability = isset($_POST['availability']) ? $_POST['availability'] : null;
    
    // Handle image upload
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = basename($_FILES['room_image']['name']);
        $image_path = $upload_dir . uniqid() . '_' . $image_name;

        if (move_uploaded_file($_FILES['room_image']['tmp_name'], $image_path)) {
            $image_path = str_replace('../', '', $image_path); // Store relative path
        } else {
            $error_message = "Failed to upload the image.";
            $image_path = null;
        }
    } else {
        $image_path = null;
    }

    if ($room_id && $room_number && $capacity && $price && $description !== null && $availability !== null) {
        // Update room details, including image if uploaded
        if ($image_path) {
            $sql = "UPDATE Rooms SET RoomNb = ?, RoomCapacity = ?, Price = ?, Description = ?, Availability = ?, Image = ? WHERE RoomID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sidsisi', $room_number, $capacity, $price, $description, $availability, $image_path, $room_id);
        } else {
            $sql = "UPDATE Rooms SET RoomNb = ?, RoomCapacity = ?, Price = ?, Description = ?, Availability = ? WHERE RoomID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sidsii', $room_number, $capacity, $price, $description, $availability, $room_id);
        }

        if ($stmt->execute()) {
            header('Location: rooms.php?message=Room+updated+successfully');
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
        <form action="editRoom.php" method="POST" enctype="multipart/form-data">
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

            <!-- Current Image -->
            <?php if (!empty($room['Image'])): ?>
                <p>Current Image:</p>
                <img src="../<?php echo htmlspecialchars($room['Image']); ?>" alt="Room Image" style="width:200px;">
            <?php endif; ?>

            <!-- File Input for Image -->
            <label for="room_image">Update Room Image:</label>
            <input type="file" id="room_image" name="room_image" accept="image/*">

            <button type="submit" class="save-btn">Save Changes</button>
        </form>
        <a href="rooms.php" class="back-link">Back to Rooms</a>
    </div>
</body>
</html>
