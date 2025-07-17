<?php
// db.php: Database connection file

$dbFile = 'attendance.db';

try {
    // Connect to the SQLite database
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Remove the echo statement below
    // echo "Connected to the SQLite database successfully.\n";
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}

// Export the database connection
return $db;
?>