<?php
session_start();
include 'config.php';

$message = '';

// On POST submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $names = $_POST['name'] ?? [];
    $classes = $_POST['class'] ?? [];
    $years = $_POST['year'] ?? [];

    if (count($names) === 0) {
        $_SESSION['message'] = "No students to add.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Manual transaction handling for PostgreSQL
    pg_query($conn, "BEGIN");

    try {
        // Prepare the INSERT statement
        $insert_query = "INSERT INTO student_entries (name, class, admission_number, year) VALUES ($1, $2, $3, $4)";
        $insert_result = pg_prepare($conn, "insert_student", $insert_query);

        foreach ($names as $index => $name) {
            $name = trim($name);
            $class = $classes[$index] ?? '';
            $year = (int)($years[$index] ?? 0);

            if ($name === '' || $class === '' || $year === 0) {
                throw new Exception("All fields are required for each student.");
            }

            // Generate unique admission number
            $baseAdmission = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 3));
            if ($baseAdmission === '') $baseAdmission = 'ADM';

            $admission_number = '';
            $attempt = 0;
            $is_unique = false;

            do {
                $randomNumber = rand(100, 999);
                $admission_number = $baseAdmission . $randomNumber;

                $check_query = "SELECT COUNT(*) FROM student_entries WHERE admission_number = $1";
                $check_result = pg_prepare($conn, "check_admission", $check_query);
                $check_result = pg_execute($conn, "check_admission", array($admission_number));
                $count = pg_fetch_result($check_result, 0, 0);

                if ($count == 0) {
                    $is_unique = true;
                } else {
                    $attempt++;
                    if ($attempt > 10) {
                        throw new Exception("Failed to generate unique admission number for student: $name");
                    }
                }
            } while (!$is_unique);

            // Execute the INSERT with the unique admission number
            $insert_result = pg_execute($conn, "insert_student", array($name, $class, $admission_number, $year));
            if (!$insert_result) {
                throw new Exception("Error inserting student: " . pg_last_error());
            }
        }

        pg_query($conn, "COMMIT");
        $_SESSION['message'] = "Students added successfully!";
    } catch (Exception $e) {
        pg_query($conn, "ROLLBACK");
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
         header("Location: form.php");
    exit();
}

// On GET request display the message if any
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Enrollment System</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: #333;
        background-color: #f5f5f5;
        padding: 20px;
    }
    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h1 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 30px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #3498db;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    input[type="text"], select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }
    .btn {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
    }
    .btn-primary {
        background-color: #3498db;
        color: white;
    }
    .btn-primary:hover {
        background-color: #2980b9;
    }
    .btn-danger {
        background-color: #e74c3c;
        color: white;
    }
    .btn-danger:hover {
        background-color: #c0392b;
    }
    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
<script>
function addRow() {
    const table = document.getElementById("studentTable");
    const newRow = table.insertRow(-1);
    
    newRow.innerHTML = `
        <td><input type="text" name="name[]" required placeholder="Student Name"></td>
        <td>
            <select name="class[]" required>
                <option value="">Select Class</option>
                <option value="Basic 1">Basic One B</option>
                <option value="Basic 2">Basic 3B</option>
                <option value="Basic 3">Basic 3A</option>
                <option value="Basic 4">Basic 4</option>
                <option value="Basic 5">Basic 5</option>
                <option value="Basic 6">Basic Six A</option>
                <option value="Basic 7">Basic 7</option>
                <option value="Basic 8">Basic 8</option>
                <option value="Basic 9">Basic 9</option>
            </select>
        </td>
        <td>
            <select name="year[]" required>
                <option value="">Select Year</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
    `;
}

function removeRow(btn) {
    const row = btn.closest('tr');
    const table = document.getElementById("studentTable");
    if (table.rows.length > 2) {  // Keep at least one row (header + one row)
        row.remove();
    } else {
        alert("At least one student entry is required");
    }
}
</script>
</head>
<body>
    <div class="container">
        <h1>Student Enrollment System</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === 0 ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <table id="studentTable">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Year</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="name[]" required placeholder="Student Name"></td>
                        <td>
                            <select name="class[]" required>
                                <option value="">Select Class</option>
                                <option value="Basic 1">Basic 1</option>
                                <option value="Basic 2">Basic 2</option>
                                <option value="Basic 3">Basic 3</option>
                                <option value="Basic 4">Basic 4</option>
                                <option value="Basic 5">Basic 5</option>
                                <option value="Basic 6">Basic 6</option>
                                <option value="Basic 7">Basic 7</option>
                                <option value="Basic 8">Basic 8</option>
                                <option value="Basic 9">Basic 9</option>
                            </select>
                        </td>
                        <td>
                            <select name="year[]" required>
                                <option value="">Select Year</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 20px;">
                <button type="button" class="btn btn-primary" onclick="addRow()">Add Student</button>
                <button type="submit" class="btn btn-primary">Submit All</button>
            </div>
        </form>
    </div>
</body>
</html>
