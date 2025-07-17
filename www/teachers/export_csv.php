<?php
include 'config.php'; // Include the config file

try {
    // Create a new PDO instance for SQLite
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get class and subject from query parameters
    $class = $_GET['class'] ?? '';
    $subject = $_GET['subject'] ?? '';

    // Prepare the SQL query
    $query = "SELECT student, admno, midterm, endterm, average, remarks, position FROM marks WHERE class = :class AND subject = :subject";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':class', $class);
    $stmt->bindParam(':subject', $subject);
    $stmt->execute();

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="exam_scores.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add CSV column headers
    fputcsv($output, ['Student Name', 'Admission No', 'Midterm', 'Endterm', 'Average', 'Remarks', 'Position']);

    // Add data rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>