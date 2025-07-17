<?php
require_once "db_connection.php"; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marksid = $_POST['marksid'];
    $student = $_POST['student'];
    $admno = $_POST['admno'];
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $class_score = $_POST['class_score'];
    $exam_score = $_POST['exam_score'];
    $total = ($class_score + $exam_score) / 2; // Calculate total
    $grade = $_POST['grade'];

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE marks SET student = ?, admno = ?, class = ?, subject = ?, midterm = ?, endterm = ?, average = ?, remarks = ? WHERE marksid = ?");
    $stmt->bind_param("sssssddsi", $student, $admno, $class, $subject, $class_score, $exam_score, $total, $grade, $marksid);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update record.']);
    }

    $stmt->close();
    $conn->close();
}
?>