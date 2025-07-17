<?php
// Path to the SQLite database
$db_path = __DIR__ . "/teachers.db"; // Update to match your exact file name

try {
    // Create the database file if it doesn't exist
    $conn = new PDO("sqlite:" . $db_path);

    // Set error mode to exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // If the database is newly created, create the table
    $query = "CREATE TABLE IF NOT EXISTS lesson_notes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        class TEXT,
        periods INTEGER,
        week_ending DATE,
        class_size INTEGER,
        strand TEXT,
        sub_strand TEXT,
        indicator TEXT,
        content_standard TEXT,
        performance_indicator TEXT,
        core_competencies TEXT,
        keywords TEXT,
        tlm TEXT,
        reference TEXT,
        starter TEXT,
        main TEXT,
        plenary TEXT
    )";
    $conn->exec($query);

    echo "Database and table created successfully!";
} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>
