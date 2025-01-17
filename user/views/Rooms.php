<?php
session_start();

// Check if the user is logged in and is a hotel owner
if (!isset($_SESSION['UserID']) || $_SESSION['AccountType'] !== 'user') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms</title>
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
    <div class="flex-grow h-screen overflow-y-auto p-6">
        <div class="container mx-auto">
            <h1 class="text-4xl font-extrabold text-center text-gray-800 mb-12">Available Rooms</h1>
            
            <!-- Filter and Search Bar -->
            <form method="GET" action="" class="mb-8 flex flex-wrap items-center gap-4 bg-white p-4 shadow rounded-lg">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by Hotel Name" 
                    class="flex-grow px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                >
                <select name="availability" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="">All Availability</option>
                    <option value="1" <?php echo (isset($_GET['availability']) && $_GET['availability'] == '1') ? 'selected' : ''; ?>>Available</option>
                    <option value="0" <?php echo (isset($_GET['availability']) && $_GET['availability'] == '0') ? 'selected' : ''; ?>>Unavailable</option>
                </select>
                <input 
                    type="number" 
                    name="capacity" 
                    placeholder="Filter by Capacity" 
                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
                    value="<?php echo htmlspecialchars($_GET['capacity'] ?? ''); ?>"
                >
                <input 
                    type="number" 
                    name="min_price" 
                    placeholder="Min Price" 
                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
                    value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>"
                >
                <input 
                    type="number" 
                    name="max_price" 
                    placeholder="Max Price" 
                    class="px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300"
                    value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>"
                >
                <select name="sort" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300">
                    <option value="">Sort By</option>
                    <option value="capacity_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'capacity_asc') ? 'selected' : ''; ?>>Capacity (Ascending)</option>
                    <option value="capacity_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'capacity_desc') ? 'selected' : ''; ?>>Capacity (Descending)</option>
                    <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Ascending)</option>
                    <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (Descending)</option>
                </select>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Apply
                </button>
            </form>

            <!-- Room Cards -->
            <div class="space-y-8">
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "hotel_management";

				$conn = new mysqli($servername, $username, $password, $dbname);

				// Check connection
				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				}

				// Base SQL query with default sorting (offers first)
				$sql = "
					SELECT 
						Rooms.RoomID, Rooms.Image, Rooms.RoomCapacity, Rooms.Price, Rooms.RoomNb, Rooms.Description, 
						Hotels.Name AS HotelName, 
						Offers.DiscountPercentage, Offers.Title AS OfferTitle,
						IF(Offers.DiscountPercentage IS NOT NULL, 1, 0) AS HasOffer
					FROM Rooms
					INNER JOIN Hotels ON Rooms.HotelID = Hotels.HotelID
					LEFT JOIN Offers ON Rooms.RoomID = Offers.RoomID 
						AND CURDATE() BETWEEN Offers.StartDate AND Offers.EndDate
					WHERE 1=1
				";

				// Apply filters
				if (!empty($_GET['search'])) {
					$search = $conn->real_escape_string($_GET['search']);
					$sql .= " AND Hotels.Name LIKE '%$search%'";
				}

				if (isset($_GET['availability']) && $_GET['availability'] !== '') {
					$availability = (int)$_GET['availability'];
					$sql .= " AND Rooms.Availability = $availability";
				}

				if (!empty($_GET['capacity'])) {
					$capacity = (int)$_GET['capacity'];
					$sql .= " AND Rooms.RoomCapacity >= $capacity";
				}

				if (!empty($_GET['min_price'])) {
					$min_price = (float)$_GET['min_price'];
					$sql .= " AND Rooms.Price >= $min_price";
				}

				if (!empty($_GET['max_price'])) {
					$max_price = (float)$_GET['max_price'];
					$sql .= " AND Rooms.Price <= $max_price";
				}

				// Default sorting: Offers first, then by price ascending
				$sql .= " ORDER BY HasOffer DESC, Rooms.Price ASC";

				// Apply custom sorting if specified
				if (!empty($_GET['sort'])) {
					switch ($_GET['sort']) {
						case 'capacity_asc':
							$sql .= ", Rooms.RoomCapacity ASC";
							break;
						case 'capacity_desc':
							$sql .= ", Rooms.RoomCapacity DESC";
							break;
						case 'price_asc':
							$sql .= ", Rooms.Price ASC";
							break;
						case 'price_desc':
							$sql .= ", Rooms.Price DESC";
							break;
					}
				}

				$result = $conn->query($sql);

				if ($result->num_rows > 0) {
					// Output data for each room
					while ($row = $result->fetch_assoc()) {
						$offerBadge = '';
						if (!empty($row['DiscountPercentage'])) {
							$offerBadge = "
								<div class='absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded'>
									{$row['DiscountPercentage']}% OFF
								</div>";
						}

						echo "
						<div class='relative flex bg-white shadow-md rounded-lg overflow-hidden hover:shadow-lg transition duration-300'>
							$offerBadge
							<img 
								class='flex-none w-48 h-48 object-cover' 
								src='../../hotel/" . htmlspecialchars($row['Image']) . "' 
								alt='Room Image'
							>
							<div class='p-6 flex-grow'>
								<h2 class='text-2xl font-bold text-gray-800 mb-2'>" . htmlspecialchars($row['HotelName']) . "</h2>
								<p class='text-gray-600'><span class=\"font-semibold\">Room Number:</span> " . htmlspecialchars($row['RoomNb']) . "</p>
								<p class='text-gray-600'><span class=\"font-semibold\">Capacity:</span> " . htmlspecialchars($row['RoomCapacity']) . " people</p>
								<p class='text-gray-600'><span class=\"font-semibold\">Price:</span> $" . number_format($row['Price'], 2) . "</p>";

						if (!empty($row['DiscountPercentage'])) {
							echo "<p class='text-red-600 font-semibold'>Special Offer: " . htmlspecialchars($row['OfferTitle']) . "</p>";
						}

						echo "
								<p class='text-gray-600 mt-4'>" . htmlspecialchars($row['Description']) . "</p>
							</div>
						</div>
						";
					}
				} else {
					echo "<p class='text-gray-800 text-center text-lg'>No available rooms match your criteria.</p>";
				}

				$conn->close();
				?>
            </div>
        </div>
    </div>
</body>
</html>
