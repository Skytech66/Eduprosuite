<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
include 'config.php'; // Make sure this path is correct

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO students (name, class, admission_number, year) VALUES (?, ?, ?, ?)");
    
    // Check if the statement was prepared successfully
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("sssi", $name, $class, $admission_number, $year);

    // Get the class and year from the POST data
    $class = $_POST['class'];
    $year = $_POST['year'];

    // Loop through the valid names and admission numbers
    foreach ($_POST['valid_name'] as $index => $name) {
        $admission_number = $_POST['valid_admno'][$index];

        // Debugging output
        error_log("Inserting: Name: $name, Class: $class, Admission Number: $admission_number, Year: $year");

        // Execute the statement
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Error inserting data: ' . $stmt->error]);
            exit;
        }
    }

    // Close the statement
    $stmt->close();

    // Return success response
    echo json_encode(['status' => 'success', 'message' => 'Students added successfully!']);
}

// Close the connection
$conn->close();
?>
