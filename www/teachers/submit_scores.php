<?php
include 'config.php'; // Ensure this file defines $conn (MySQLi connection)

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam = $_POST['exam'];
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $admno = $_POST['regno'];
    $count = count($admno);

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO marks (examname, class, midterm, endterm, subject, student, admission_number, average, remarks, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssddssssss", $exam, $class, $midterm, $endterm, $subject, $jina, $regno, $average, $remarks, $position);

    for ($i = 0; $i < $count; $i++) {
        // Get individual values
        $mid = $_POST['midterm'][$i];
        $end = $_POST['endterm'][$i];
        $jina = $_POST['jina'][$i];
        $regno = $admno[$i];
        $position = $_POST['position'][$i];

        // Set midterm and endterm to 0 if they are empty
        $midterm = !empty($mid) ? floatval($mid) : 0;
        $endterm = !empty($end) ? floatval($end) : 0;
        $remarks = null;

        // Calculate average only if both midterm and endterm are present
        $average = null;
        if ($midterm !== 0 || $endterm !== 0) {
            $average = round(($midterm + $endterm));

            // Assign remarks based on the average
            if ($average < 30) {
                $remarks = "E";
            } else if ($average < 40) {
                $remarks = "D";
            } else if ($average < 60) {
                $remarks = "C";
            } else if ($average <= 80) {
                $remarks = "B";
            } else if ($average <= 100) {
                $remarks = "A";
            } else {
                $remarks = "Invalid";
            }
        }

        // Execute the prepared statement only if there are values for midterm or endterm
        if ($midterm !== 0 || $endterm !== 0) {
            $result = $stmt->execute();

            // Check for errors
            if (!$result) {
                echo "Error: " . $stmt->error;
            }
        }
    }

    // Close the statement
    $stmt->close();

    // Redirect or show success message
    echo "<script>alert('Marks added successfully!'); window.location = 'index.php?exams';</script>";
} else {
    // If the request method is not POST, redirect or show an error
    echo "<script>alert('Marks added successfully!'); window.location = 'index.php?exams';</script>";
}
?>
