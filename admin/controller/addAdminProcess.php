<?php

// Check if the user is logged in and is an admin
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add Admin
if (isset($_POST['add_admin'])) {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert into Users table with AccountType as 'Admin'
    $sql = "INSERT INTO Users (FullName, Email, Password, AccountType) VALUES (?, ?, ?, 'Admin')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullName, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin added successfully.'); window.location.href = '../views/addAdmin.php';</script>";
    } else {
        echo "<script>alert('Error adding admin.'); window.location.href = '../views/addAdmin.php';</script>";
    }
    $stmt->close();
}

// Remove Admin (after confirmation)
if (isset($_POST['confirm_remove_admin'])) {
    $userID = $_POST['user_id'];

    // Delete the admin from the Users table
    $sql = "DELETE FROM Users WHERE UserID = ? AND AccountType = 'Admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);

    if ($stmt->execute()) {
        echo "<script>alert('Admin removed successfully.'); window.location.href = '../views/addAdmin.php';</script>";
    } else {
        echo "<script>alert('Error removing admin.'); window.location.href = '../views/addAdmin.php';</script>";
    }
    $stmt->close();
}

// Fetch all admins
$sql = "SELECT UserID, FullName, Email FROM Users WHERE AccountType = 'Admin'";
$result = $conn->query($sql);
$admins = $result->fetch_all(MYSQLI_ASSOC);

// Close the connection
$conn->close();
?>