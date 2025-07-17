<?php
include 'config.php';

function formatScore($value) {
    return rtrim(rtrim(number_format($value, 2), '0'), '.');
}

function formatPosition($pos) {
    $pos = intval($pos);
    if ($pos <= 0) return '';
    $lastDigit = $pos % 10;
    $lastTwoDigits = $pos % 100;

    if ($lastTwoDigits >= 11 && $lastTwoDigits <= 13) {
        $suffix = "th";
    } else {
        switch ($lastDigit) {
            case 1:
                $suffix = "st";
                break;
            case 2:
                $suffix = "nd";
                break;
            case 3:
                $suffix = "rd";
                break;
            default:
                $suffix = "th";
                break;
        }
    }
    return $pos . $suffix;
}

// Initialize variables
$selectedClass = '';
$selectedSubject = '';
$scores = [];
$editScore = null;
$addErrors = [];
$addSuccess = false;
$errors = []; // for edit errors

// Handle delete request
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmtDelete = $conn->prepare("DELETE FROM marks WHERE id = ?");
    $stmtDelete->bind_param("i", $deleteId);
    $stmtDelete->execute();
    $stmtDelete->close();

    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $redirectUrl");
    exit;
}

// Fetch classes and subjects for dropdowns
$classQuery = "SELECT DISTINCT class FROM marks";
$classResult = $conn->query($classQuery);

$subjectQuery = "SELECT DISTINCT subject FROM marks";
$subjectResult = $conn->query($subjectQuery);

// Handle add score form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['update_id'])) {
    // Validate inputs
    $student = trim($_POST['student'] ?? '');
    $admission_number = trim($_POST['admission_number'] ?? '');
    $examname = trim($_POST['examname'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $midterm = floatval($_POST['midterm'] ?? 0);
    $endterm = floatval($_POST['endterm'] ?? 0);
    $average = isset($_POST['average']) && $_POST['average'] !== '' ? floatval($_POST['average']) : null;
    $remarks = trim($_POST['remarks'] ?? '');
    $position = intval($_POST['position'] ?? 0);

    if ($student === '') $addErrors[] = "Student name is required.";
    if ($admission_number === '') $addErrors[] = "Admission number is required.";
    if ($examname === '') $addErrors[] = "Exam name is required.";
    if ($class === '') $addErrors[] = "Class is required.";
    if ($subject === '') $addErrors[] = "Subject is required.";
    if ($midterm < 0) $addErrors[] = "Midterm score must be zero or positive.";
    if ($endterm < 0) $addErrors[] = "Endterm score must be zero or positive.";
    if ($position < 1) $addErrors[] = "Position must be a positive integer.";

    if (count($addErrors) === 0) {
        $stmtInsert = $conn->prepare("INSERT INTO marks (student, admission_number, examname, class, subject, midterm, endterm, average, remarks, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("ssssssddsi", $student, $admission_number, $examname, $class, $subject, $midterm, $endterm, $average, $remarks, $position);
        $executeResult = $stmtInsert->execute();
        $stmtInsert->close();

        if ($executeResult) {
            $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
            header("Location: $redirectUrl");
            exit;
        } else {
            $addErrors[] = "Database error: Could not insert new score.";
        }
    }
}

// Load distinct students based on selected class and subject
$students = [];
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['class']) && isset($_POST['subject'])) {
    $class = $_POST['class'];
    $subject = $_POST['subject'];
    $studentQuery = "SELECT DISTINCT student, admission_number FROM marks WHERE class = ? AND subject = ?";
    $stmtStudents = $conn->prepare($studentQuery);
    $stmtStudents->bind_param("ss", $class, $subject);
    $stmtStudents->execute();
    $resultStudents = $stmtStudents->get_result();

    while ($row = $resultStudents->fetch_assoc()) {
        $students[] = $row;
    }
    $stmtStudents->close();
}

// Handle edit form submission (update)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_id'])) {
    $updateId = intval($_POST['update_id']);
    
    // Validate inputs
    $student = trim($_POST['student']) ?? '';
    $admission_number = trim($_POST['admission_number']) ?? '';
    $midterm = floatval($_POST['midterm']);
    $endterm = floatval($_POST['endterm']);
    $average = floatval($_POST['average']);
    $remarks = trim($_POST['remarks']) ?? '';
    $position = intval($_POST['position']);
    $class = trim($_POST['class']) ?? '';
    $subject = trim($_POST['subject']) ?? '';
    $examname = trim($_POST['examname']) ?? '';

    // Basic validation
    if ($student === '') $errors[] = "Student name is required.";
    if ($admission_number === '') $errors[] = "Admission number is required.";
    if ($class === '') $errors[] = "Class is required.";
    if ($subject === '') $errors[] = "Subject is required.";
    if ($examname === '') $errors[] = "Exam name is required.";
    if ($position < 1) $errors[] = "Position must be a positive integer.";

    if (count($errors) === 0) {
        $stmtUpdate = $conn->prepare("UPDATE marks SET student = ?, admission_number = ?, midterm = ?, endterm = ?, average = ?, remarks = ?, position = ?, class = ?, subject = ?, examname = ? WHERE id = ?");
        $stmtUpdate->bind_param("ssdddsisssi", $student, $admission_number, $midterm, $endterm, $average, $remarks, $position, $class, $subject, $examname, $updateId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // Redirect back to main page with filter params preserved if possible
        $redirectUrl = strtok($_SERVER["REQUEST_URI"], '?');
        header("Location: $redirectUrl");
        exit;
    } else {
        // If errors, refill editScore to show form with submitted data
        $editScore = [
            'id' => $updateId,
            'student' => $student,
            'admission_number' => $admission_number,
            'midterm' => $midterm,
            'endterm' => $endterm,
            'average' => $average,
            'remarks' => $remarks,
            'position' => $position,
            'class' => $class,
            'subject' => $subject,
            'examname' => $examname
        ];
    }
}

// Load score for editing if edit_id GET parameter set
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmtEdit = $conn->prepare("SELECT * FROM marks WHERE id = ?");
    $stmtEdit->bind_param("i", $editId);
    $stmtEdit->execute();
    $resultEdit = $stmtEdit->get_result();
    $editScore = $resultEdit->fetch_assoc();
    $stmtEdit->close();
}

// Handle viewing scores (filter) only if no active edit or add form
if ($editScore === null && $_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['student'])) {
    $selectedClass = $_POST['class'] ?? '';
    $selectedSubject = $_POST['subject'] ?? '';

    if ($selectedClass !== '' && $selectedSubject !== '') {
        $scoreQuery = "SELECT * FROM marks WHERE class = ? AND subject = ?";
        $stmt = $conn->prepare($scoreQuery);
        $stmt->bind_param("ss", $selectedClass, $selectedSubject);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Exam Scores Management | Academic Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="loader.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #6366f1;
            --secondary: #10b981;
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --warning: #f59e0b;
            --info: #3b82f6;
            --light: #f9fafb;
            --dark: #111827;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --gray-lighter: #f3f4f6;
            --border-radius: 0.5rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.15s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            color: var(--dark);
            background-color: var(--gray-lighter);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1rem 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
            position: relative;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1600px;
            margin: 0 auto;
        }

        .nav-buttons {
            display: flex;
            gap: 0.75rem;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        h2 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            color: var(--dark);
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--gray-light);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .btn i {
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #0d9e6e;
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: var(--danger-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-info {
            background-color: var(--info);
            color: white;
        }

        .btn-info:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-light {
            background-color: var(--gray-light);
            color: var(--dark);
        }

        .btn-light:hover {
            background-color: #d1d5db;
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
        }

        .btn-icon {
            padding: 0.5rem;
            min-width: 2.25rem;
            min-height: 2.25rem;
            justify-content: center;
        }

        .btn-icon i {
            margin-right: 0;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .form-col {
            flex: 1 0 0;
            padding: 0 0.75rem;
            min-width: 200px;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.875rem;
        }

        input, select {
            width: 100%;
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
            background-color: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .readonly-input {
            background-color: var(--gray-lighter);
            cursor: not-allowed;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        th, td {
            padding: 0.875rem 1rem;
            text-align: left;
            vertical-align: middle;
            border-top: 1px solid var(--gray-light);
        }

        thead th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        tbody tr:nth-child(even) {
            background-color: var(--gray-lighter);
        }

        tbody tr:hover {
            background-color: rgba(79, 70, 229, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
        }

        .badge-success {
            color: white;
            background-color: var(--secondary);
        }

        .badge-warning {
            color: var(--dark);
            background-color: var(--warning);
        }

        .badge-danger {
            color: white;
            background-            color: var(--danger);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
        }

        .alert-danger {
            color: #7f1d1d;
            background-color: #fee2e2;
            border-color: #fecaca;
        }

        .alert ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: var(--gray);
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
        }

        .empty-state h3 {
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        /* Modern toggle switch for form visibility */
        .toggle-container {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .toggle-label {
            margin-left: 0.5rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .container {
                padding: 1rem;
            }
            
            .form-col {
                flex: 0 0 50%;
            }
        }

        @media (max-width: 768px) {
            .form-col {
                flex: 0 0 100%;
            }
            
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .nav-buttons {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .btn {
                flex: 1 1 auto;
                padding: 0.5rem 0.75rem;
                font-size: 0.8125rem;
            }
            
            .card {
                padding: 1rem;
            }
            
            th, td {
                padding: 0.75rem;
            }
            
            .actions {
                flex-direction: column;
                gap: 0.25rem;
            }
        }

        @media (max-width: 480px) {
            .btn i {
                margin-right: 0;
            }
            
            .btn span {
                display: none;
            }
            
            .btn-icon {
                min-width: auto;
            }
        }
    </style>
    <script>
        function confirmDelete(studentName) {
            return confirm('Are you sure you want to delete the scores for "' + studentName + '"?');
        }

        function toggleAddForm() {
            const form = document.getElementById('addScoreForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            window.scrollTo({
                top: form.offsetTop - 20,
                behavior: 'smooth'
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const studentSelect = document.getElementById('student_add');
            const admissionNumberInput = document.getElementById('admission_number_add');

            if (studentSelect && admissionNumberInput) {
                studentSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    admissionNumberInput.value = selectedOption.dataset.admission_number || '';
                });
            }

            // Calculate average when midterm or endterm changes
            const midtermInput = document.getElementById('midterm_add');
            const endtermInput = document.getElementById('endterm_add');
            const averageInput = document.getElementById('average_add');

            if (midtermInput && endtermInput && averageInput) {
                function calculateAverage() {
                    const midterm = parseFloat(midtermInput.value) || 0;
                    const endterm = parseFloat(endtermInput.value) || 0;
                    const average = (midterm + endterm) / 2;
                    averageInput.value = average.toFixed(2);
                }

                midtermInput.addEventListener('input', calculateAverage);
                endtermInput.addEventListener('input', calculateAverage);
            }
        });
    </script>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>
                <i class="fas fa-graduation-cap"></i>
                <span>Exam Scores Management System</span>
            </h1>
            <div class="nav-buttons">
                <a href="dashboard.php" class="btn btn-light">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Back to Dashboard</span>
                </a>
                <a href="exam.php" class="btn btn-info">
                    <i class="fas fa-chart-bar"></i>
                    <span>Statistics & Analysis</span>
                </a>
                <a href="marks.php" class="btn btn-secondary">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Marks</span>
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Add Score Button -->
        <div class="toggle-container">
            <button class="btn btn-primary" onclick="toggleAddForm()">
                <i class="fas fa-plus"></i>
                <span>Add New Score</span>
            </button>
        </div>

        <!-- Add Score Form -->
        <div id="addScoreForm" class="card" style="display:none; margin-top:0;">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add New Score Record</span>
                </h2>
            </div>
            
            <?php if (!empty($addErrors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($addErrors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="class">Class</label>
                            <select name="class" id="class" required>
                                <option value="">Select Class</option>
                                <?php while ($row = $classResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['class']); ?>">
                                        <?php echo htmlspecialchars($row['class']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select name="subject" id="subject" required>
                                <option value="">Select Subject</option>
                                <?php while ($row = $subjectResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['subject']); ?>">
                                        <?php echo htmlspecialchars($row['subject']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="student_add">Student</label>
                            <select name="student" id="student_add" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo htmlspecialchars($student['student']); ?>" 
                                            data-admission_number="<?php echo htmlspecialchars($student['admission_number']); ?>">
                                        <?php echo htmlspecialchars($student['student']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="admission_number_add">Admission Number</label>
                            <input type="text" id="admission_number_add" name="admission_number" class="readonly-input" readonly />
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="examname_add">Exam Name</label>
                            <input type="text" id="examname_add" name="examname" required />
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="midterm_add">Midterm Score</label>
                            <input type="number" step="0.01" id="midterm_add" name="midterm" required min="0" max="100" />
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="endterm_add">Endterm Score</label>
                            <input type="number" step="0.01" id="endterm_add" name="endterm" required min="0" max="100" />
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="average_add">Average</label>
                            <input type="number" step="0.01" id="average_add" name="average" class="readonly-input" readonly />
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="remarks_add">Remarks</label>
                            <select id="remarks_add" name="remarks">
                                <option value="">Select Remarks</option>
                                <option value="A">A (Excellent)</option>
                                <option value="B">B (Good)</option>
                                <option value="C">C (Average)</option>
                                <option value="D">D (Below Average)</option>
                                <option value="E">E (Poor)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="position_add">Position</label>
                            <input type="number" id="position_add" name="position" min="1" required />
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Save Score</span>
                    </button>
                    <button type="button" onclick="toggleAddForm()" class="btn btn-light" style="margin-left:0.5rem;">
                        <i class="fas fa-times"></i>
                        <span>Cancel</span>
                    </button>
                </div>
            </form>
        </div>

        <?php if ($editScore !== null): ?>
            <!-- Edit Score Form -->
            <div class="card" style="margin-top:1.5rem;">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-edit"></i>
                        <span>Edit Score for <?php echo htmlspecialchars($editScore['student']); ?></span>
                    </h2>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="update_id" value="<?php echo intval($editScore['id']); ?>" />

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="examname">Exam Name</label>
                                <input type="text" id="examname" name="examname" required 
                                       value="<?php echo htmlspecialchars($editScore['examname']); ?>" />
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="class">Class</label>
                                <input type="text" id="class" name="class" required 
                                       value="<?php echo htmlspecialchars($editScore['class']); ?>" />
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" required 
                                       value="<?php echo htmlspecialchars($editScore['subject']); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="student">Student</label>
                                <input type="text" id="student" name="student" required 
                                       value="<?php echo htmlspecialchars($editScore['student']); ?>" />
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="admission_number">Admission Number</label>
                                <input type="text" id="admission_number" name="admission_number" required 
                                       value="<?php echo htmlspecialchars($editScore['admission_number']); ?>" />
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="number" id="position" name="position" min="1" 
                                       value="<?php echo htmlspecialchars($editScore['position']); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="midterm">Midterm</label>
                                <input type="number" step="0.01" id="midterm" name="midterm" required min="0" max="100"
                                       value="<?php echo htmlspecialchars(formatScore($editScore['midterm'])); ?>" />
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="endterm">Endterm</label>
                                <input type="number" step="0.01" id="endterm" name="endterm" required min="0" max="100"
                                       value="<?php echo htmlspecialchars(formatScore($editScore['endterm'])); ?>" />
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="average">Average</label>
                                <input type="number" step="0.01" id="average" name="average" 
                                       value="<?php echo htmlspecialchars(formatScore($editScore['average'])); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="remarks">Remarks</label>
                                <select id="remarks" name="remarks">
                                    <option value="">Select Remarks</option>
                                    <option value="A" <?php echo ($editScore['remarks'] === 'A') ? 'selected' : ''; ?>>A (Excellent)</option>
                                    <option value="B" <?php echo ($editScore['remarks'] === 'B') ? 'selected' : ''; ?>>B (Good)</option>
                                    <option value="C" <?php echo ($editScore['remarks'] === 'C') ? 'selected' : ''; ?>>C (Average)</option>
                                    <option value="D" <?php echo ($editScore['remarks'] === 'D') ? 'selected' : ''; ?>>D (Below Average)</option>
                                    <option value="E" <?php echo ($editScore['remarks'] === 'E') ? 'selected' : ''; ?>>E (Poor)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top:1.5rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <span>Update Score</span>
                        </button>
                        <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="btn btn-light" style="margin-left:0.5rem;">
                            <i class="fas fa-times"></i>
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Filter Form -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-filter"></i> Filter Scores</h2>
            </div>
            
            <form method="POST" action="" id="filterForm">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="class">Class</label>
                            <select name="class" id="class" required>
                                <option value="">Select Class</option>
                                <?php
                                $classResult->data_seek(0);
                                while ($row = $classResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['class']); ?>" 
                                        <?php echo ($row['class'] === $selectedClass) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['class']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select name="subject" id="subject" required>
                                <option value="">Select Subject</option>
                                <?php
                                $subjectResult->data_seek(0);
                                while ($row = $subjectResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($row['subject']); ?>" 
                                        <?php echo ($row['subject'] === $selectedSubject) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($row['subject']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-col" style="align-self:flex-end;">
                        <div class="form-group">
                                                       <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($editScore === null && !empty($scores)): ?>
            <!-- Scores Table -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-table"></i> Scores for <?php echo htmlspecialchars($selectedClass); ?> - <?php echo htmlspecialchars($selectedSubject); ?>
                    </h2>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th>Midterm</th>
                                <th>Endterm</th>
                                <th>Average</th>
                                <th>Remarks</th>
                                <th>Position</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scores as $score): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($score['student']); ?></td>
                                    <td><?php echo htmlspecialchars($score['admission_number']); ?></td>
                                    <td><?php echo htmlspecialchars(formatScore($score['midterm'])); ?></td>
                                    <td><?php echo htmlspecialchars(formatScore($score['endterm'])); ?></td>
                                    <td><?php echo htmlspecialchars(formatScore($score['average'])); ?></td>
                                    <td>
                                        <?php if ($score['remarks']): ?>
                                            <span class="badge <?php 
                                                echo $score['remarks'] === 'A' ? 'badge-success' : 
                                                     ($score['remarks'] === 'B' ? 'badge-warning' : 'badge-danger'); 
                                            ?>">
                                                <?php echo htmlspecialchars($score['remarks']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(formatPosition($score['position'])); ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?edit_id=<?php echo intval($score['id']); ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete_id=<?php echo intval($score['id']); ?>" class="btn btn-danger btn-sm" 
                                               onclick="return confirmDelete('<?php echo addslashes(htmlspecialchars($score['student'])); ?>')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif ($editScore === null && $_SERVER["REQUEST_METHOD"] == "POST" && empty($addErrors)): ?>
            <!-- Empty State -->
            <div class="card">
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No Scores Found</h3>
                    <p>No scores found for Class "<?php echo htmlspecialchars($selectedClass); ?>" and Subject "<?php echo htmlspecialchars($selectedSubject); ?>".</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
