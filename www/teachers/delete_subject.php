<?php
require_once "header.php"; 
require_once "db_connection.php"; // Include your database connection file
$conn = db_conn(); // Establish the database connection

// Check if the subject ID is provided
if (isset($_POST['subject_id'])) {
    $subject_id = intval($_POST['subject_id']); // Ensure it's an integer

    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM subject WHERE subjectid = :subjectid");
    $stmt->bindValue(':subjectid', $subject_id, SQLITE3_INTEGER);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        // Return a success response
        echo json_encode(['status' => 'success', 'message' => 'Subject deleted successfully.']);
    } else {
        // Return an error response
        echo json_encode(['status' => 'error', 'message' => 'Error deleting subject: ' . $stmt->errorInfo()[2]]);
    }
} else {
    // Return an error response if no subject ID is provided
    echo json_encode(['status' => 'error', 'message' => 'No subject ID provided.']);
}

// Close the database connection
$conn = null;
?>