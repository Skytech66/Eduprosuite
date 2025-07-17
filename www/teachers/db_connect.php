<?php
// Adjust the relative path to go up one directory to 'www' and then into 'db'
$dbFile = __DIR__ . '/../db/school.db';

if (!file_exists($dbFile)) {
    die("Error: Database file not found at $dbFile");
}

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
