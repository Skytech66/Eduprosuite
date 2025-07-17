<?php
$dbPath = 'C:/Users/LENOVO/Desktop/Main Project/www/teachers/school.db';
$conn = new SQLite3($dbPath);

// Query to fetch column information for the `marks` table
$result = $conn->query("PRAGMA table_info(marks);");

echo "Columns in 'marks' table:<br>";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "Column: " . $row['name'] . " (Type: " . $row['type'] . ")<br>";
}
?>
