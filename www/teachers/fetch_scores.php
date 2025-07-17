<?php
include 'config.php'; // Include the config file

header('Content-Type: application/json'); // Set the content type to JSON

try {
    $class = $_GET['class'] ?? ''; // Use null coalescing operator to avoid undefined index
    $subject = $_GET['subject'] ?? '';

    // Check if subject is provided
    if (empty($subject)) {
        echo json_encode(['error' => 'Subject is required']);
        exit;
    }

    // Prepare the SQL query to fetch scores based on subject and class
    $sql = "SELECT student, admno, midterm, endterm, average, remarks, position 
            FROM marks 
            WHERE subject = ?"; // Start with filtering by subject

    // Add class filtering if a class is provided
    if (!empty($class)) {
        $sql .= " AND class = ?"; // Use 'class' as the column name
    }

    // Add sorting by position
    $sql .= " ORDER BY position ASC"; // Sort by position in ascending order

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    if (!empty($class)) {
        $stmt->bind_param("ss", $subject, $class); // "ss" means two strings
    } else {
        $stmt->bind_param("s", $subject); // "s" means one string
    }

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result === false) {
        throw new Exception("Getting result set failed: " . $stmt->error);
    }

    $scores = [];
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }

    // Return the results as JSON
    echo json_encode($scores);

    // Close statement and connection
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Handle any errors
    echo json_encode(['error' => $e->getMessage()]);
}
?>
