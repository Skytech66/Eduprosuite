<?php
session_start();
require '../db_connect.php';  // Adjust path as needed

// Redirect if not logged in
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Fetch teacher's assigned class
$query = $db->prepare("SELECT assigned_class FROM teachers WHERE id = ?");
$query->execute([$teacher_id]);
$teacher = $query->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    echo "Error: Teacher not found.";
    exit();
}

$assigned_class = $teacher['assigned_class'];

// Fetch students from the assigned class
$query = $db->prepare("SELECT id, name FROM students WHERE class = ?");
$query->execute([$assigned_class]);
$students = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle Attendance Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $date = $_POST['attendance_date'];

    foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt = $db->prepare("INSERT INTO attendance (student_id, class, teacher_id, status, date) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $assigned_class, $teacher_id, $status, $date]);
    }

    echo "<script>alert('Attendance marked successfully!'); window.location='dashboard.php';</script>";
}

// Handle Adding a New Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_name = trim($_POST['student_name']);
    if (!empty($student_name)) {
        $stmt = $db->prepare("INSERT INTO students (name, class) VALUES (?, ?)");
        $stmt->execute([$student_name, $assigned_class]);
        echo "<script>alert('Student added successfully!'); window.location.reload();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack | AI Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4F46E5;
            --ai-accent: #10B981;
            --surface: #F8FAFC;
            --border: #E2E8F0;
        }
        body {
            font-family: 'Inter', system-ui;
            background-color: var(--surface);
        }
        .ai-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--ai-accent) 100%);
            color: white;
            border-radius: 12px;
            padding: 15px;
        }
        .student-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .student-card i {
            margin-right: 8px;
            color: var(--primary);
        }
        .student-card select {
            min-width: 120px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="ai-header mb-4">
            <h3><i class="fas fa-robot"></i> AI-Powered Attendance</h3>
            <p class="mb-0">Smart tracking with pattern recognition</p>
        </div>

        <!-- Date Picker -->
        <form method="POST">
            <div class="mb-3">
                <label for="attendanceDate" class="form-label"><i class="fas fa-calendar-alt"></i> Select Date</label>
                <input type="date" name="attendance_date" id="attendanceDate" class="form-control" required>
            </div>

            <!-- Student List -->
            <div id="studentList">
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <div class="student-card">
                            <div class="fw-medium"><i class="fas fa-user"></i> <?php echo htmlspecialchars($student['name']); ?></div>
                            <select name="attendance[<?php echo $student['id']; ?>]" class="form-select form-select-sm">
                                <option value="Present">Present</option>
                                <option value="Late">Late</option>
                                <option value="Absent">Absent</option>
                                <option value="Excused">Excused</option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No students found in this class.</p>
                <?php endif; ?>
            </div>

            <input type="hidden" name="mark_attendance" value="1">
            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-cloud-upload-alt"></i> Submit Attendance</button>
        </form>

        <!-- Add Student Button -->
        <button class="btn btn-secondary mt-4" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fas fa-user-plus"></i> Add Student
        </button>

        <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="add_student" value="1">
                            <div class="mb-3">
                                <label for="studentName" class="form-label">Student Name</label>
                                <input type="text" class="form-control" name="student_name" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
