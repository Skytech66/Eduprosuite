<?php
$host = "localhost"; // Or your database host
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$database = "school"; // Replace with your database name
$socket = "/opt/lampp/var/mysql/mysql.sock"; // Replace with your actual socket path if different

// Connect using socket (XAMPP MySQL)
$conn = new mysqli($host, $username, $password, $database, null, $socket);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set (recommended)
$conn->set_charset("utf8mb4"); // Or "utf8" if you don't need full UTF-8 support

// Optional:  Set the default timezone (recommended)
date_default_timezone_set('Africa/Nairobi'); // Replace with your timezone

?>
