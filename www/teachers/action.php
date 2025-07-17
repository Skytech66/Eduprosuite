<?php
// Enable error reporting
error_reporting(E_ALL); // Report all types of errors
ini_set('display_errors', 1); // Display errors on the screen

include "../include/functions.php";
$conn = db_conn();

$action = $_POST['submit'];

switch($action) {
    case 'submit_marks':
        $exam = $_POST['exam'];
        $class = $_POST['class'];
        $admno = $_POST['regno'];
        $subject = encryptthis($_POST['subject'], $key);
        $count = count($_POST['regno']);

        // Debugging: Check if the form data is received correctly
        echo "Debugging: Received form data.<br>";
        echo "Exam: $exam, Class: $class, Count: $count<br>";

        if (is_array($admno)) {
            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO marks (examname, class, midterm, endterm, subject, student, admno, average, remarks, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            for ($i = 0; $i < $count; $i++) {
                // Get individual values
                $mid = $_POST['midterm'][$i];
                $end = $_POST['endterm'][$i];
                $jina = $_POST['jina'][$i];
                $regno = $_POST['regno'][$i];
                $position = $_POST['position'][$i]; // Capture position input

                // Debugging: Output the values being processed
                echo "Processing student $i: Midterm: $mid, Endterm: $end, Name: $jina, Regno: $regno, Position: $position<br>";

                // Encrypt values
                $midterm = !empty($mid) ? encryptthis($mid, $key) : null;
                $endterm = !empty($end) ? encryptthis($end, $key) : null;
                $jina = encryptthis($jina, $key);
                $remarks = null; // Default value for remarks

                // Calculate average only if both midterm and endterm are present
                $average = null;
                if (!empty($mid) && !empty($end)) {
                    $mid = floatval($mid);  // Ensure both are numeric
                    $end = floatval($end);
                    $average = $mid + $end;  // Calculate the average

                    // Assign remarks based on the average
                    if ($average < 30) {
                        $remarks = encryptthis("E", $key);
                    } else if ($average < 40) {
                        $remarks = encryptthis("D", $key);
                    } else if ($average < 60) {
                        $remarks = encryptthis("C", $key);
                    } else if ($average <= 80) {
                        $remarks = encryptthis("B", $key);
                    } else if ($average <= 100) {
                        $remarks = encryptthis("A", $key);
                    } else {
                        $remarks = encryptthis("Invalid", $key);
                    }

                    // Encrypt average
                    $average = encryptthis($average, $key);
                }

                // Debugging: Output the values before executing the statement
                echo "Inserting: $exam, $class, $midterm, $endterm, $subject, $jina, $regno, $average, $remarks, $position<br>";

                // Execute the prepared statement only if there are values for midterm or endterm
                if (!empty($mid) || !empty($end)) {
                    // Bind parameters using bindValue
                    $stmt->bindValue(1, $exam);
                    $stmt->bindValue(2, $class);
                    $stmt->bindValue(3, $midterm);
                    $stmt->bindValue(4, $endterm);
                    $stmt->bindValue(5, $subject);
                    $stmt->bindValue(6, $jina);
                    $stmt->bindValue(7, $regno);
                    $stmt->bindValue(8, $average);
                    $stmt->bindValue(9, $remarks);
                    $stmt->bindValue(10, $position);

                    $result = $stmt->execute();

                    // Check for errors
                    if (!$result) {
                        echo "Error: " . $conn->lastErrorMsg();
                    }
                }
            }

            // Close the statement
            $stmt->close();

            // Redirect or show success message
            echo "
            <script>alert('Marks added successfully!')</script>
            <script>window.location = 'index.php?exams'</script>
            ";   
        }
    break;
}
?>