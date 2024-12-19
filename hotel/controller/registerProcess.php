<?php
session_start(); // Start session for user login

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

// Retrieve user input from POST request
$fullName = $_POST['full_name'];
$hotelName = $_POST['hotel_name'];
$email = $_POST['email'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$password = $_POST['password'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the data into the PendingHotel table for review
$sql = "INSERT INTO PendingHotel (FullName, HotelName, Email, Address, Phone, Password) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $fullName, $hotelName, $email, $address, $phone, $hashedPassword);

if ($stmt->execute()) {
    // Redirect to a confirmation page or back to the login page
    header("Location: ../views/login.php");
    exit;
} else {
    echo "<script>
            alert('Error during registration. Please try again.');
            window.location.href = '../views/register.php';
          </script>";
}

$stmt->close();
$conn->close();
?>
