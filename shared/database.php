<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$dbname = "hotel_management"; 
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($dbname);

// SQL statements to create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS Users (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        FullName VARCHAR(100) NOT NULL,
        Email VARCHAR(150) UNIQUE NOT NULL,
        Password VARCHAR(255) NOT NULL, 
        AccountType ENUM('Admin', 'Guest', 'HotelOwner') NOT NULL DEFAULT 'Guest', 
        Wallet DECIMAL(10, 2) DEFAULT 0.00,
        IsBanned BOOLEAN DEFAULT FALSE
    )",
    "CREATE TABLE IF NOT EXISTS Hotels (
        HotelID INT PRIMARY KEY,
        Name VARCHAR(150) NOT NULL,
        Address TEXT NOT NULL,
        Phone VARCHAR(20),
        Email VARCHAR(150) UNIQUE NOT NULL,
        Wallet DECIMAL(10, 2) DEFAULT 0.00,
        FOREIGN KEY (HotelID) REFERENCES Users(UserID) ON DELETE CASCADE 
    )",
    "CREATE TABLE IF NOT EXISTS Rooms (
        RoomID INT AUTO_INCREMENT PRIMARY KEY,
        HotelID INT NOT NULL,
        RoomCapacity INT NOT NULL,
        Price DECIMAL(10, 2) NOT NULL,
        RoomNb INT NOT NULL,
        Availability BOOLEAN DEFAULT TRUE,
        Description TEXT,
        Image VARCHAR(255) DEFAULT NULL,
        FOREIGN KEY (HotelID) REFERENCES Hotels(HotelID) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS Bookings (
        BookingID INT AUTO_INCREMENT PRIMARY KEY,
        UserID INT NOT NULL,
        RoomID INT NOT NULL,
        BookingDate DATE NOT NULL DEFAULT CURRENT_TIMESTAMP, 
        CheckInDate DATE NOT NULL,
        CheckOutDate DATE NOT NULL,
        TotalPrice DECIMAL(10, 2) NOT NULL,
        Status ENUM('Pending', 'Confirmed', 'Cancelled') NOT NULL DEFAULT 'Pending', 
        FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
        FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS Offers (
        OfferID INT AUTO_INCREMENT PRIMARY KEY,
        HotelID INT NOT NULL,
        RoomID INT NOT NULL,
        Title VARCHAR(150),
        Description TEXT,
        DiscountPercentage DECIMAL(5, 2),
        StartDate DATE NOT NULL,
        EndDate DATE NOT NULL,
        FOREIGN KEY (HotelID) REFERENCES Hotels(HotelID) ON DELETE CASCADE,
        FOREIGN KEY (RoomID) REFERENCES Rooms(RoomID) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS Transactions (
        TransactionID INT AUTO_INCREMENT PRIMARY KEY,
        BookingID INT NOT NULL,
        Amount DECIMAL(10, 2) NOT NULL,
        TransactionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (BookingID) REFERENCES Bookings(BookingID) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS AdminLogs (
        LogID INT AUTO_INCREMENT PRIMARY KEY,
        AdminID INT NOT NULL,
        Action TEXT NOT NULL,
        ActionDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (AdminID) REFERENCES Users(UserID) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS PendingHotel (
        PendingID INT AUTO_INCREMENT PRIMARY KEY,
        FullName VARCHAR(100) NOT NULL,
        HotelName VARCHAR(150) NOT NULL,
        Email VARCHAR(150) UNIQUE NOT NULL,
        Address TEXT NOT NULL,
        Phone VARCHAR(20) NOT NULL,
        Password VARCHAR(255) NOT NULL, 
        RequestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $table) {
    if ($conn->query($table) === TRUE) {
        echo "Table created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
}

$conn->close();
?>