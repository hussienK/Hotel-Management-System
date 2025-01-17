<?php
session_start(); // Start the session

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve email and password from POST request
$email = $_POST['email'];
$password = $_POST['password'];

// Validate user credentials
$sql = "SELECT UserID, FullName, Password, AccountType FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['Password'])) {
        // Store user information in session
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['FullName'] = $user['FullName'];
        $_SESSION['AccountType'] = $user['AccountType'];

        // Redirect to the homepage
        header("Location: ../views/Rooms.php");
        exit;
    } else {
        // Invalid password
        $_SESSION['error'] = "Incorrect password. Please try again.";
        header("Location: ../views/login.php");
        exit;
    }
} else {
    // User not found
    $_SESSION['error'] = "No account found with that email. Please register first.";
    header("Location: ../views/login.php");
    exit;
}

$stmt->close();
$conn->close();
?>
