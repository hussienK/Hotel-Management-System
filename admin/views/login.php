<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../styles/login.css">
</head>
<?php
session_start();
if (isset($_SESSION['error'])) {
    echo "<p class='error-msg'>" . $_SESSION['error'] . "</p>";
    unset($_SESSION['error']); // Clear error after displaying
}
?>

<body>
    <div class="login-container">
        <div class="form-container">
            <h1>Admin Login</h1>
            <form action="../controller/loginProcess.php" method="POST">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                
                <button type="submit" class="login-btn">Login</button>
            </form>
            <div class="register-link">
                <p>Don't have an account? Contact An Admin.</p>
            </div>
        </div>
    </div>
</body>
</html>
