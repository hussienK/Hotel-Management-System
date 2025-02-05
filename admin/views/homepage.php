<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="stylesheet" href="../styles/homePage.css">
    <style>
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #ff4d4d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        .logout-btn:hover {
            background-color: #e60000;
        }
    </style>
</head>
<body>
    <a href="../controller/logout.php" class="logout-btn">Logout</a>
    <div class="container">
        <h1>Welcome, Admin</h1>
        <div class="card-container">
            <div class="card">
                <h2>Add New Admin</h2>
                <p>Create and manage admin accounts.</p>
                <a href="add_admin.php" class="btn">Go</a>
            </div>
            <div class="card">
                <h2>Manage Users</h2>
                <p>View, edit, and manage user accounts.</p>
                <a href="manage_users.php" class="btn">Go</a>
            </div>
            <div class="card">
                <h2>Manage Hotels</h2>
                <p>Review and manage hotel profiles.</p>
                <a href="manage_hotels.php" class="btn">Go</a>
            </div>
            <div class="card">
                <h2>Manage Transactions</h2>
                <p>View and track all transactions.</p>
                <a href="manage_transactions.php" class="btn">Go</a>
            </div>
        </div>
    </div>
</body>
</html>
