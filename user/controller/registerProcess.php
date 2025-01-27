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
$email = $_POST['email'];
$password = $_POST['password'];
$password2 = $_POST['confirm_password'];
$accountType = 'Guest';
$wallet = 0.00;

// Validate password matching
if ($password !== $password2) {
    $_SESSION['error'] = "Passwords do not match. Please try again.";
    header("Location: ../views/register.php");
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert the data into the Users table
$sql = "INSERT INTO Users (FullName, Email, Password, AccountType, Wallet) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Bind parameters: 'ssssd' -> string, string, string, string, double
$stmt->bind_param("ssssd", $fullName, $email, $hashedPassword, $accountType, $wallet);

try {
    if ($stmt->execute()) {
        // Redirect to the login page
        header("Location: ../views/login.php");
        exit;
    }
} catch (mysqli_sql_exception $e) {
    // Check for duplicate email error (MySQL error code 1062)
    if ($e->getCode() == 1062) {
        $_SESSION['error'] = "This email is already registered. Please try another email.";
    } else {
        $_SESSION['error'] = "Error during registration. Please try again.";
    }
    header("Location: ../views/register.php");
    exit;
}

$stmt->close();
$conn->close();
?>
