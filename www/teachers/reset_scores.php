<?php
// Relative database connection setup using __DIR__
$dbFile = __DIR__ . '/db/school.db'; // Relative path to the database file
$conn = new SQLite3($dbFile);

try {
    // SQL query to delete all exam scores (delete all records in the marks table)
    $sql = "DELETE FROM marks";
    $conn->exec($sql);

    header("Location: index.php?success=1");
    exit();
} catch (Exception $e) {
    // Redirect to the main page if an error occurs
    header("Location: index.php?error=1");
    exit();
}
?>
