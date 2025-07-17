<?php
include "../include/functions.php"; // Include your functions file
$conn = db_conn(); // Establish the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class = $_POST['class'];

    // Find duplicate names
    $duplicateQuery = "SELECT name, COUNT(*) as count FROM student WHERE class = :class GROUP BY name HAVING count > 1";
    $stmt = $conn->prepare($duplicateQuery);
    $stmt->bindValue(':class', $class, SQLITE3_TEXT);
    $result = $stmt->execute();

    $deletedCount = 0;

    // Loop through duplicates and delete them
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $name = $row['name'];

        // Get all IDs of the duplicate names
        $idQuery = "SELECT id FROM student WHERE class = :class AND name = :name";
        $idStmt = $conn->prepare($idQuery);
        $idStmt->bindValue(':class', $class, SQLITE3_TEXT);
        $idStmt->bindValue(':name', $name, SQLITE3_TEXT);
        $idResult = $idStmt->execute();

        $ids = [];
        while ($idRow = $idResult->fetchArray(SQLITE3_ASSOC)) {
            $ids[] = $idRow['id'];
        }

        // Keep the first ID and delete the rest
        array_shift($ids); // Remove the first ID from the array
        foreach ($ids as $id) {
            $deleteQuery = "DELETE FROM student WHERE id = :id";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bindValue(':id', $id, SQLITE3_INTEGER);
            $deleteStmt->execute();
            $deletedCount++;
        }
    }

    echo json_encode(['success' => true, 'message' => "$deletedCount duplicate(s) removed."]);
} else {
        echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

$conn->close();
?>