<?php
session_start(); // Start session to manage messages

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Establish connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$fullName = trim($_POST['full_name']);
$hotelName = trim($_POST['hotel_name']);
$email = trim($_POST['email']);
$address = trim($_POST['address']);
$phone = trim($_POST['phone']);
$password = trim($_POST['password']);

// Validate form data
if (empty($fullName) || empty($hotelName) || empty($email) || empty($address) || empty($phone) || empty($password)) {
    echo "<script>
            alert('All fields are required!');
            window.location.href = 'register.php';
          </script>";
    exit;
}

// Check if email already exists in Users table
$sql = "SELECT Email FROM Users WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>
            alert('Email is already registered. Please use a different email.');
            window.location.href = 'register.php';
          </script>";
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into Users table
$accountType = 'Hotel'; // Explicitly set AccountType
$sql = "INSERT INTO Users (FullName, Email, Password, AccountType, Wallet) VALUES (?, ?, ?,'Hotel', 0.00)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $fullName, $email, $hashedPassword);

if ($stmt->execute()) {
    // Get the UserID of the newly registered user
    $userID = $stmt->insert_id;

    // Insert into Hotels table
    $sql = "INSERT INTO Hotels (Name, Address, Phone, Email, Wallet) VALUES (?, ?, ?, ?, 0.00)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $hotelName, $address, $phone, $email);

    if ($stmt->execute()) {
        // Registration successful
        echo "<script>
                alert('Registration successful! You can now log in.');
                window.location.href = '../views/login.php';
              </script>";
    } else {
        // Error inserting into Hotels table
        echo "<script>
                alert('Error occurred while registering your hotel. Please try again.');
                window.location.href = 'register.php';
              </script>";
    }
} else {
    // Error inserting into Users table
    echo "<script>
            alert('Error occurred while creating your account. Please try again.');
            window.location.href = 'register.php';
          </script>";
}

// Close connection
$stmt->close();
$conn->close();
?>
