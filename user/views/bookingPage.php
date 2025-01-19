<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit;
}

// Get room details from query parameters
$roomID = $_GET['roomID'];
$hotelName = $_GET['hotelName'];
$roomNb = $_GET['roomNb'];
$capacity = $_GET['capacity'];
$price = $_GET['price'];
$discount = $_GET['discount'];
$pricePerNight = $_GET['finalPrice'];
$cleanedString = str_replace(',', '', $pricePerNight); // Remove commas
$finalPrice = floatval($cleanedString);
$description = $_GET['description'];
$image = $_GET['image'];

// Get user's wallet balance
$wallet = $_SESSION['Wallet']; // Assuming wallet is stored in session
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Room</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <!-- Sidebar/Navbar -->
    <div class="w-1/4 bg-blue-700 h-screen sticky text-white p-6 flex flex-col">
        <h1 class="text-3xl font-bold mb-8 text-center">Dashboard</h1>
        <ul class="space-y-6">
            <li>
                <a href="./Rooms.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Rooms
                </a>
            </li>
            <li>
                <a href="./profile.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Profile
                </a>
            </li>
            <li>
                <a href="../Controller/logoutProcess.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Logout
                </a>
            </li>
            <li>
                <a href="./bookings.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Bookings
                </a>
            </li>
            <li>
                <a href="./transactions.php" class="block text-lg font-medium hover:text-blue-300 transition duration-300">
                    Transactions
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow p-6">
        <!-- User Wallet -->
        <div class="absolute top-6 right-6 bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md">
            <p class="text-lg font-semibold">Wallet: $<?php echo $wallet ?></p>
        </div>

        <!-- Booking Details -->
        <div class="container mx-auto">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="flex">
                    <!-- Room Image -->
                    <div class="w-1/3">
                        <img 
                            src="../../hotel/<?php echo htmlspecialchars($image); ?>" 
                            alt="Room Image" 
                            class="h-full object-cover w-full"
                        >
                    </div>
                    <!-- Room Details -->
                    <div class="p-6 w-2/3">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4"><?php echo htmlspecialchars($hotelName); ?></h2>
                        <p class="text-gray-600 mb-2"><strong>Room Number:</strong> <?php echo htmlspecialchars($roomNb); ?></p>
                        <p class="text-gray-600 mb-2"><strong>Capacity:</strong> <?php echo htmlspecialchars($capacity); ?> people</p>
                        <p class="text-gray-600 mb-2"><strong>Price:</strong> $<?php echo $price; ?></p>
                        <?php if ($discount > 0): ?>
                            <p class="text-red-500 font-semibold mb-2">
                                <strong>Discount:</strong> <?php echo htmlspecialchars($discount); ?>% OFF
                            </p>
                            <p class="text-gray-800 font-semibold"><strong>Final Price:</strong> $<?php echo number_format($pricePerNight, 2); ?></p>
                        <?php endif; ?>
                        <p class="text-gray-600 mt-4"><?php echo htmlspecialchars($description); ?></p>
                    </div>
                </div>

                <!-- Booking Form -->
                <form method="POST" action="#" class="p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Booking Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="checkInDate" class="block text-gray-700 font-medium mb-2">Check-In Date</label>
                            <input 
                                type="date" 
                                name="checkInDate" 
                                id="checkInDate" 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
                                required
                                onchange="calculateTotalPrice()"
                            >
                        </div>
                        <div>
                            <label for="checkOutDate" class="block text-gray-700 font-medium mb-2">Check-Out Date</label>
                            <input 
                                type="date" 
                                name="checkOutDate" 
                                id="checkOutDate" 
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
                                required
                                onchange="calculateTotalPrice()"
                            >
                        </div>
                        <div>
                            <label for="totalPrice" class="block text-gray-700 font-medium mb-2">Total Price</label>
                            <input 
                                type="text" 
                                name="totalPrice" 
                                id="totalPrice" 
                                value="Select Dates" 
                                class="w-full px-4 py-2 border rounded-lg bg-gray-100" 
                                readonly
                            >
                        </div>
                    </div>
                    <button 
                        type="submit" 
                        class="mt-6 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition"
                    >
                        Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>

        <script>
        // Function to calculate total price
        function calculateTotalPrice() {
            const checkInDate = document.getElementById("checkInDate").value;
            const checkOutDate = document.getElementById("checkOutDate").value;
            const finalPrice = <?php echo $finalPrice; ?>;

            if (checkInDate && checkOutDate) {
                const startDate = new Date(checkInDate);
                const endDate = new Date(checkOutDate);

                // Calculate the number of nights
                const timeDiff = endDate - startDate;
                const totalNights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                // Ensure the check-out date is after the check-in date
                if (totalNights > 0) {
                    const totalPrice = totalNights * finalPrice;
                    const formatter = new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD',
                    });

                    document.getElementById("totalPrice").value = formatter.format(totalPrice);
                } else {
                    document.getElementById("totalPrice").value = "Invalid Dates";
                }
            }
        }
    </script>
</body>
</html>
