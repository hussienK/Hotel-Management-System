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

// Get hotel ID of the logged-in user
$UserID = $_SESSION['UserID'];
$sql = "SELECT HotelID FROM Hotels WHERE Email = (SELECT Email FROM Users WHERE UserID = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $UserID);
$stmt->execute();
$result = $stmt->get_result();
$hotel_id = $result->fetch_assoc()['HotelID'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = $_POST['room_number'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $availability = isset($_POST['availability']) ? 1 : 0;

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
        }
    } else {
        $image_path = null;
    }

    // Insert room details into the database
    $insert_sql = "INSERT INTO Rooms (HotelID, RoomNb, RoomCapacity, Price, Availability, Description, Image) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param('iiidiss', $hotel_id, $room_number, $capacity, $price, $availability, $description, $image_path);

    if ($insert_stmt->execute()) {
        header("Location: rooms.php?message=Room+is+added+successfully");
    } else {
        $error_message = "Error adding room: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room</title>
    <link rel="stylesheet" href="../styles/addRoom.css">
</head>
<body>
    <div class="container">
    <a href="rooms.php" style="     
        background-color: #007bff;
        color: white;
        text-decoration: none;
        padding: 10px 15px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
    " >← Back</a>
        <h1>Add a New Room</h1>
        <?php if (isset($success_message)): ?>
            <p class="success"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="addRoom.php" method="POST" enctype="multipart/form-data">
            <label for="room_number">Room Number:</label>
            <input type="text" id="room_number" name="room_number" placeholder="Enter room number" required>
            
            <label for="capacity">Room Capacity:</label>
            <input type="number" id="capacity" name="capacity" placeholder="Enter room capacity" required>
            
            <label for="price">Price per Night:</label>
            <input type="text" id="price" name="price" placeholder="Enter price per night" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" placeholder="Enter room description" required></textarea>
            
            <label for="availability">Availability:</label>
            <input type="checkbox" id="availability" name="availability" checked>

            <label for="room_image">Upload Room Image:</label>
            <input type="file" id="room_image" name="room_image" accept="image/*">

            <button type="submit">Add Room</button>
        </form>
        <a href="rooms.php" class="back-link">Back to Rooms</a>
    </div>
</body>
</html>
