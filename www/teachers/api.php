<?php
// api.php: Backend API endpoints

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow cross-origin requests
header("Access-Control-Allow-Methods: GET, POST"); // Allow specific HTTP methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

require 'db.php'; // Include the database connection file

// Function to handle errors
function handleError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

// Add a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $name = $_POST['name'] ?? '';
    $rollNumber = $_POST['rollNumber'] ?? '';

    if (empty($name) || empty($rollNumber)) {
        handleError('Name and roll number are required.');
    }

    try {
        $query = "INSERT INTO students (name, roll_number) VALUES (:name, :rollNumber)";
        $stmt = $db->prepare($query);
        $stmt->execute([':name' => $name, ':rollNumber' => $rollNumber]);
        echo json_encode(['id' => $db->lastInsertId(), 'name' => $name, 'rollNumber' => $rollNumber]);
    } catch (PDOException $e) {
        handleError('Error adding student: ' . $e->getMessage());
    }
}

// Fetch all attendance records
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_attendance') {
    try {
        $query = "
            SELECT attendance.id, students.name, students.roll_number, attendance.date, attendance.time
            FROM attendance
            INNER JOIN students ON attendance.student_id = students.id
        ";
        $stmt = $db->query($query);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($records);
    } catch (PDOException $e) {
        handleError('Error fetching attendance records: ' . $e->getMessage());
    }
}
?>