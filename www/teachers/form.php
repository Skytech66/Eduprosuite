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

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO student_entries (name, class, admission_number, year) VALUES (?, ?, ?, ?)");
        foreach ($names as $index => $name) {
            $name = trim($name);
            $class = $classes[$index] ?? '';
            $year = (int)($years[$index] ?? 0);

            if ($name === '' || $class === '' || $year === 0) {
                throw new Exception("All fields are required for each student.");
            }

            // Generate admission number prefix from name (first 3 letters uppercase)
            $baseAdmission = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 3));
            if ($baseAdmission === '') $baseAdmission = 'ADM';

            // Ensure admission_number is unique by retrying if needed
            $admission_number = '';
            $attempt = 0;
            do {
                $randomNumber = rand(100, 999);
                $admission_number = $baseAdmission . $randomNumber;

                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM student_entries WHERE admission_number = ?");
                $checkStmt->bind_param("s", $admission_number);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                $attempt++;
                if ($attempt > 10) {
                    throw new Exception("Failed to generate unique admission number for student: $name");
                }
            } while ($count > 0);

            $stmt->bind_param("sssi", $name, $class, $admission_number, $year);
            $stmt->execute();
        }
        $conn->commit();
        $_SESSION['message'] = "Students added successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Error adding students: " . $e->getMessage();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Students</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: #f3f4f6;
    color: #111827;
    padding: 2rem;
    max-width: 900px;
    margin: auto;
  }
  h1 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2563eb;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
    background: white;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.05);
    border-radius: 12px;
    overflow: hidden;
  }
  th, td {
    padding: 0.75rem 1rem;
    text-align: left;
  }
  thead {
    background: #2563eb;
    color: white;
  }
  input[type="text"], select {
    width: 100%;
    padding: 0.4rem 0.6rem;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
  }
  input[type="text"]:focus, select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 4px #2563eba0;
  }
  button {
    background: #2563eb;
    border: none;
    color: white;
    padding: 0.6rem 1.4rem;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background: #1d4ed8;
  }
  button.remove-btn {
    background: #ef4444;
  }
  button.remove-btn:hover {
    background: #b91c1c;
  }
  .btn-inline {
    margin-right: 0.5rem;
  }
  .message {
    margin-bottom: 1rem;
    font-weight: 600;
    color: #15803d;
  }
  .error {
    color: #b91c1c;
  }
  .back-link {
    display: inline-block;
    margin-top: 1rem;
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
  }
  .back-link:hover {
    text-decoration: underline;
  }
</style>
<script>
function addRow() {
    const table = document.getElementById("studentTable");
    const rowCount = table.rows.length;
    const row = table.insertRow(rowCount);

    row.innerHTML = `
    <td><input type="text" name="name[]" required autocomplete="off" placeholder="Student Name"></td>
    <td>
      <select name="class[]" required>
        <option value="" disabled selected>Select Class</option>
        <option value="Class 1">Class 1</option>
        <option value="Class 2">Class 2</option>
        <option value="Class 3">Class 3</option>
        <option value="Class 4">Class 4</option>
        <option value="Class 5">Class 5</option>
      </select>
    </td>
    <td>
      <select name="year[]" required>
        <option value="" disabled selected>Select Year</option>
        <option value="2025">2025</option>
      </select>
    </td>
    <td><button type="button" class="remove-btn" onclick="removeRow(this)">Remove</button></td>
    `;
}

function removeRow(btn) {
    const row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
}
</script>
</head>
<body>
    <h1>Add Students</h1>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') === 0 ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
      <table id="studentTable" aria-label="Students Entry Table">
        <thead>
          <tr>
            <th scope="col">Student Name</th>
            <th scope="col">Class</th>
            <th scope="col">Year</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="text" name="name[]" required autocomplete="off" placeholder="Student Name"></td>
            <td>
              <select name="class[]" required>
                <option value="" disabled selected>Select Class</option>
                                
                  <option value="Basic 1">Basic 1(B)</option>
                  <option value="Basic 2">Basic 6(A)</option>
                  <option value="Basic 3">Basic 3(A)</option>
                  <option value="Basic 4">Basic 3(B)</option>
                  <option value="Basic 5">Basic 5</option>
                  <option value="Basic 6">Basic 6</option>
                  <option value="Basic 7">Basic 7</option>
                  <option value="Basic 8">Basic 8</option>
                  <option value="Basic 9">Basic 9</option>
                </select>

            </td>
            <td>
              <select name="year[]" required>
                <option value="" disabled selected>Select Year</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
              </select>
            </td>
            <td><button type="button" class="remove-btn" onclick="removeRow(this)">Remove</button></td>
          </tr>
        </tbody>
      </table>
      <button type="button" class="btn-inline" onclick="addRow()">Add Row</button>
      <button type="submit">Submit</button>
    </form>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
</body>
</html>
