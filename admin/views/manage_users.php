<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Fetch all users
require_once '../controller/manageUsersProcess.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../styles/manageUsers.css">
    <script>
        function openEditModal(userID, fullName, email, accountType, isBanned) {
            document.getElementById('edit_user_id').value = userID;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_account_type').value = accountType;
            document.getElementById('edit_is_banned').checked = isBanned == 1;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Users</h1>

        <!-- Users Table -->
        <table class="user-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Account Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['FullName']) ?></td>
                        <td><?= htmlspecialchars($user['Email']) ?></td>
                        <td><?= htmlspecialchars($user['AccountType']) ?></td>
                        <td><?= $user['IsBanned'] ? 'Banned' : 'Active' ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?= $user['UserID'] ?>',
                                '<?= htmlspecialchars($user['FullName']) ?>',
                                '<?= htmlspecialchars($user['Email']) ?>',
                                '<?= htmlspecialchars($user['AccountType']) ?>',
                                '<?= $user['IsBanned'] ?>'
                            )">Edit</button>
                            <form method="POST" action="../controller/manageUsersProcess.php" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">
                                <button type="submit" name="delete_user" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit User Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <h2>Edit User</h2>
                <form method="POST" action="../controller/manageUsersProcess.php">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <input type="text" id="edit_full_name" name="full_name" placeholder="Full Name" required>
                    <input type="email" id="edit_email" name="email" placeholder="Email" required>
                    <select id="edit_account_type" name="account_type" required>
                        <option value="Admin">Admin</option>
                        <option value="Guest">Guest</option>
                        <option value="HotelOwner">Hotel Owner</option>
                    </select>
                    <label>
                        <input type="checkbox" id="edit_is_banned" name="is_banned"> Banned
                    </label>
                    <button type="submit" name="edit_user">Save Changes</button>
                    <button type="button" onclick="closeEditModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>