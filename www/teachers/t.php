<?php
try {
    $db = new PDO('sqlite:C:\xampp\htdocs\MainProjecttt\www\students_records.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get all tables
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);

    echo "Available tables: " . implode(", ", $tables);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
