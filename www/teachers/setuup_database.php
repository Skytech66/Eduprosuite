<?php
// Database connection
$db = new SQLite3('students_records.db');

// Create students table if not exists
$db->exec("CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
)");

echo "Database setup complete!";
