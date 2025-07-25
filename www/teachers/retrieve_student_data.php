<?php
// Include the database configuration
require_once 'config.php';

// Get the year and class from the POST request
$year = isset($_POST['year']) ? $_POST['year'] : '';
$class = isset($_POST['class']) ? $_POST['class'] : '';

// Initialize an array to hold the student data
$students = [];

// Check if year and class are provided
if (!empty($year) && !empty($class)) {
    // Prepare the SQL query to fetch students from student_entries
    $sql = "SELECT * FROM student_entries WHERE year = $1 AND class = $2";
    $result = pg_query_params($conn, $sql, array($year, $class));

    // Check if the query was successful
    if ($result) {
        // Fetch the results and store them in the students array
        while ($row = pg_fetch_assoc($result)) {
            $students[] = $row;
        }
    } else {
        // Handle query error
        http_response_code(500);
        echo json_encode(['error' => 'Query failed: ' . pg_last_error($conn)]);
        exit;
    }
} else {
    // Handle missing parameters
    http_response_code(400);
    echo json_encode(['error' => 'Year and class must be provided.']);
    exit;
}

// Close the database connection
pg_close($conn);

// Return the student data as a JSON response
header('Content-Type: application/json');
echo json_encode($students);
?>


