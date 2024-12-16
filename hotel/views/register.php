<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Register</title>
    <link rel="stylesheet" href="../styles/register.css">
</head>
<body>
    <div class="register-container">
        <div class="form-container">
            <h1>Register Your Hotel</h1>
            <p>Provide your details to create an account.</p>
            <form action="../controller/registerProcess.php" method="POST">
                <!-- Full Name -->
                <label for="full-name">Full Name</label>
                <input type="text" id="full-name" name="full_name" placeholder="Enter your full name" required>

                <!-- Hotel Name -->
                <label for="hotel-name">Hotel Name</label>
                <input type="text" id="hotel-name" name="hotel_name" placeholder="Enter your hotel name" required>

                <!-- Email -->
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <!-- Address -->
                <label for="address">Hotel Address</label>
                <input id="address" name="address" placeholder="Enter your hotel's address" rows="3" required></input>

                <!-- Phone -->
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>

                <!-- Password -->
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>

                <button type="submit" class="register-btn">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
