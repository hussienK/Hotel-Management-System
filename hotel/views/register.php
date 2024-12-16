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
            <h1>Register as Hotel Owner</h1>
            <p>Create an account to manage your hotel.</p>
            <form action="process_register.php" method="POST">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required>
                
                <button type="submit" class="register-btn">Register</button>
            </form>
        </div>
    </div>
</body>
</html>
