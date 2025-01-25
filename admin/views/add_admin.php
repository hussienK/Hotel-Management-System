<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Fetch all admins (this will be moved to the controller later)
require_once '../controller/addAdminProcess.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Management</title>
    <link rel="stylesheet" href="../styles/addAdmin.css">
    <script>
        function confirmRemove(userID, fullName) {
            if (confirm(`Are you sure you want to remove ${fullName} as an admin?`)) {
                document.getElementById('user_id').value = userID;
                document.getElementById('remove_admin_form').submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Management</h1>

        <!-- Add Admin Form -->
        <div class="form-container">
            <form method="POST" action="../controller/addAdminProcess.php">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="add_admin">Add Admin</button>
            </form>
        </div>

        <!-- Remove Admin Section -->
        <h2>Admin List</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?= htmlspecialchars($admin['FullName']) ?></td>
                        <td><?= htmlspecialchars($admin['Email']) ?></td>
                        <td>
                            <button class="remove-btn" onclick="confirmRemove(<?= $admin['UserID'] ?>, '<?= htmlspecialchars($admin['FullName']) ?>')">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Hidden form for removing admin -->
        <form id="remove_admin_form" method="POST" action="../controller/addAdminProcess.php" style="display: none;">
            <input type="hidden" id="user_id" name="user_id">
            <input type="hidden" name="confirm_remove_admin">
        </form>
    </div>
</body>
</html>