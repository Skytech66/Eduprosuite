<?php
include 'config.php'; // Include the config file

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if marksid is set
if (isset($data['marksid'])) {
    $marksid = $data['marksid'];

    // Prepare the SQL statement to delete the score
    $stmt = $conn->prepare("DELETE FROM marks WHERE marksid = ?");
    $stmt->bind_param("i", $marksid);

    // Execute the statement
    if ($stmt->execute()) {
        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No record found with the given ID.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Error executing the query.']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}

// Close the database connection
$conn->close();
?>
