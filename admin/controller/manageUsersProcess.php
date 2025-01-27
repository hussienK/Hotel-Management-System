<?php

// Check if the user is logged in and is an admin

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all users
$sql = "SELECT UserID, FullName, Email, AccountType, IsBanned FROM Users";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Handle user edit
if (isset($_POST['edit_user'])) {
    $userID = $_POST['user_id'];
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $accountType = $_POST['account_type'];
    $isBanned = isset($_POST['is_banned']) ? 1 : 0;

    $sql = "UPDATE Users SET FullName = ?, Email = ?, AccountType = ?, IsBanned = ? WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fullName, $email, $accountType, $isBanned, $userID);

    if ($stmt->execute()) {
        echo "<script>alert('User updated successfully.'); window.location.href = '../views/manage_users.php';</script>";
    } else {
        echo "<script>alert('Error updating user.'); window.location.href = '../views/manage_users.php';</script>";
    }
    $stmt->close();
}

// Handle user delete
if (isset($_POST['delete_user'])) {
    $userID = $_POST['user_id'];

    $sql = "DELETE FROM Users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully.'); window.location.href = '../views/manage_users.php';</script>";
    } else {
        echo "<script>alert('Error deleting user.'); window.location.href = '../views/manage_users.php';</script>";
    }
    $stmt->close();
}

// Close the connection
$conn->close();
?>