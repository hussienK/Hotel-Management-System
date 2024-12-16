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

        // Redirect to the home page
        header("Location: ../views/homePage.php");
        exit;
    } else {
        // Invalid password
        echo "<script>
                alert('Incorrect password. Please try again.');
                window.location.href = 'login.php';
              </script>";
    }
} else {
    // User not found
    echo "<script>
            alert('No account found with that email. Please register first.');
            window.location.href = 'register.php';
          </script>";
}

$stmt->close();
$conn->close();
?>
