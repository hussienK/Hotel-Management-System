<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Fetch all transactions
require_once '../controller/manageTransactionProcess.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Transactions</title>
    <link rel="stylesheet" href="../styles/transactionManagement.css">
    <script>
        function confirmDelete(transactionID) {
            if (confirm('Are you sure you want to delete this transaction?')) {
                document.getElementById('transaction_id').value = transactionID;
                document.getElementById('delete_transaction_form').submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Transactions</h1>

        <!-- Transactions Table -->
        <table class="transaction-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Transaction Date</th>
                    <th>Booking ID</th>
                    <th>Check-In Date</th>
                    <th>Check-Out Date</th>
                    <th>User</th>
                    <th>Hotel</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= htmlspecialchars($transaction['TransactionID']) ?></td>
                        <td>$<?= htmlspecialchars($transaction['Amount']) ?></td>
                        <td><?= htmlspecialchars($transaction['TransactionDate']) ?></td>
                        <td><?= htmlspecialchars($transaction['BookingID']) ?></td>
                        <td><?= htmlspecialchars($transaction['CheckInDate']) ?></td>
                        <td><?= htmlspecialchars($transaction['CheckOutDate']) ?></td>
                        <td><?= htmlspecialchars($transaction['UserName']) ?></td>
                        <td><?= htmlspecialchars($transaction['HotelName']) ?></td>
                        <td>
                            <button class="action-btn view-btn" onclick="window.location.href='view_transaction.php?id=<?= $transaction['TransactionID'] ?>'">View</button>
                            <button class="action-btn" onclick="confirmDelete(<?= $transaction['TransactionID'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Hidden form for deleting transactions -->
        <form id="delete_transaction_form" method="POST" action="../controller/manageTransactionsProcess.php" style="display: none;">
            <input type="hidden" id="transaction_id" name="transaction_id">
            <input type="hidden" name="delete_transaction">
        </form>
    </div>
</body>
</html>