<?php
// functions.php

// Include database connection
require_once "config.php"; // Ensure you have a database connection file

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
$session_id = $_SESSION["id"] ?? '';

if ($session_id == "") {
    header("Location: ../index.php?error=Invalid username or password");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_start();
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header("Location: ../index.php?message=Logged out successfully");
    exit();
}

// Database connection function
function db_conn() {
    $db = new SQLite3('../include/database.db'); // Update with your database path
    if (!$db) {
        die("Connection failed: " . $db->lastErrorMsg());
    }
    return $db;
}

// Other functions can be added here as needed
?>
