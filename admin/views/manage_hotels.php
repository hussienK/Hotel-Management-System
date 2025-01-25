<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Fetch all approved hotels and pending requests
require_once '../controller/manageHotelProcess.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hotels</title>
    <link rel="stylesheet" href="../styles/manageHotels.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function openEditModal(hotelID, name, address, phone, email) {
            document.getElementById('edit_hotel_id').value = hotelID;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_address').value = address;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_email').value = email;
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function approveRequest(pendingID) {
            if (confirm('Are you sure you want to approve this hotel request?')) {
                $.ajax({
                    url: '../controller/approveHotel.php',
                    type: 'GET',
                    data: { id: pendingID },
                    success: function(response) {
                        alert('Hotel request approved successfully.');
                        location.reload(); // Refresh the page to update the list
                    },
                    error: function() {
                        alert('Error approving hotel request.');
                    }
                });
            }
        }

        function deleteRequest(pendingID) {
            if (confirm('Are you sure you want to delete this hotel request?')) {
                $.ajax({
                    url: '../controller/deleteRequest.php',
                    type: 'GET',
                    data: { id: pendingID },
                    success: function(response) {
                        alert('Hotel request deleted successfully.');
                        location.reload(); // Refresh the page to update the list
                    },
                    error: function() {
                        alert('Error deleting hotel request.');
                    }
                });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Hotels</h1>

        <!-- Pending Requests Section -->
        <h2>Pending Hotel Requests</h2>
        <table class="hotel-table">
            <thead>
                <tr>
                    <th>Hotel Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRequests as $request): ?>
                    <tr>
                        <td><?= htmlspecialchars($request['HotelName']) ?></td>
                        <td><?= htmlspecialchars($request['Address']) ?></td>
                        <td><?= htmlspecialchars($request['Phone']) ?></td>
                        <td><?= htmlspecialchars($request['Email']) ?></td>
                        <td><?= htmlspecialchars($request['FullName']) ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="approveRequest(<?= $request['PendingID'] ?>)">Approve</button>
                            <button class="action-btn delete-btn" onclick="deleteRequest(<?= $request['PendingID'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Approved Hotels Section -->
        <h2>Approved Hotels</h2>
        <table class="hotel-table">
            <thead>
                <tr>
                    <th>Hotel Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hotels as $hotel): ?>
                    <tr>
                        <td><?= htmlspecialchars($hotel['Name']) ?></td>
                        <td><?= htmlspecialchars($hotel['Address']) ?></td>
                        <td><?= htmlspecialchars($hotel['Phone']) ?></td>
                        <td><?= htmlspecialchars($hotel['Email']) ?></td>
                        <td><?= htmlspecialchars($hotel['OwnerName']) ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openEditModal(
                                '<?= $hotel['HotelID'] ?>',
                                '<?= htmlspecialchars($hotel['Name']) ?>',
                                '<?= htmlspecialchars($hotel['Address']) ?>',
                                '<?= htmlspecialchars($hotel['Phone']) ?>',
                                '<?= htmlspecialchars($hotel['Email']) ?>'
                            )">Edit</button>
                            <form method="POST" action="../controller/manageHotelsProcess.php" style="display: inline;">
                                <input type="hidden" name="hotel_id" value="<?= $hotel['HotelID'] ?>">
                                <button type="submit" name="delete_hotel" class="action-btn delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Edit Hotel Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <h2>Edit Hotel</h2>
                <form method="POST" action="../controller/manageHotelsProcess.php">
                    <input type="hidden" id="edit_hotel_id" name="hotel_id">
                    <input type="text" id="edit_name" name="name" placeholder="Hotel Name" required>
                    <textarea id="edit_address" name="address" placeholder="Address" required></textarea>
                    <input type="text" id="edit_phone" name="phone" placeholder="Phone" required>
                    <input type="email" id="edit_email" name="email" placeholder="Email" required>
                    <button type="submit" name="edit_hotel">Save Changes</button>
                    <button type="button" onclick="closeEditModal()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>