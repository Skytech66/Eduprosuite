<?php
// Direct database connection function
function db_conn(){
    // Database name & Connection
    $database_name = "../db/school.db"; // Adjust this path if needed
    $db = new SQLite3($database_name);
    return $db;
}

// Debugging: print out the URL parameters
echo "<pre>";
print_r($_GET);
echo "</pre>";

// Ensure 'class_name' is provided in the URL
if (!isset($_GET['class_name'])) {
    echo "Error: Class name is required.";
    exit;
}

$class_name = $_GET['class_name']; // Assign the class name from the URL

// Open a direct connection to the database
$conn = db_conn();

// Get students for the selected class
$students_query = "SELECT id, name FROM students WHERE class_name = :class_name";
$stmt = $conn->prepare($students_query);
$stmt->bindValue(':class_name', $class_name, SQLITE3_TEXT);
$result = $stmt->execute();

$students = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $students[] = $row;
}

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = date('Y-m-d'); // current date

    // Loop through students and save attendance
    if (isset($_POST['attendance'])) {
        foreach ($_POST['attendance'] as $student_id => $status) {
            $query = "INSERT INTO attendance (student_id, class_name, date, status) VALUES ('$student_id', '$class_name', '$date', '$status')";
            $conn->exec($query);
        }
        echo "Attendance marked successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
</head>
<body>
    <h2>Mark Attendance for Class <?php echo htmlspecialchars($class_name); ?></h2>
    
    <form method="POST" action="">
        <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($class_name); ?>">
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Attendance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td>
                            <select name="attendance[<?php echo $student['id']; ?>]">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Submit Attendance</button>
    </form>
</body>
</html>
