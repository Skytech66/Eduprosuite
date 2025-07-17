<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php'; // DB connection

// Redirect if not logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Get assigned class
$stmt = $conn->prepare("SELECT assigned_class FROM teacher WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$res = $stmt->get_result();
$teacher = $res->fetch_assoc();
if (!$teacher) {
    die("Teacher not found.");
}
$assigned_class = $teacher['assigned_class'];

// Fetch students in assigned class
$stmt = $conn->prepare("SELECT id, name FROM students WHERE class = ?");
$stmt->bind_param("s", $assigned_class);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle add student
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_student'])) {
    $name = trim($_POST['student_name']);
    if ($name) {
        $stmt = $conn->prepare("INSERT INTO students (name, class) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $assigned_class);
        $stmt->execute();
        $_SESSION['success'] = "Student added successfully!";
    } else {
        $_SESSION['error'] = "Student name cannot be empty.";
    }
    header("Location: mark_attendance.php");
    exit;
}

// Handle attendance submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_attendance'])) {
    $date = $_POST['attendance_date'] ?? date('Y-m-d');
    $week = $_POST['attendance_week'] ?? '';
    $day = $_POST['attendance_day'] ?? '';
    $attendance_data = $_POST['attendance'] ?? [];

    if (!$week || !$day || empty($attendance_data)) {
        $_SESSION['error'] = "Please complete all fields.";
        header("Location: mark_attendance.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO mark_attendance (student_id, class, date, week, day, status, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($attendance_data as $id => $status) {
        if ($status) {
            $stmt->bind_param("isssssi", $id, $assigned_class, $date, $week, $day, $status, $teacher_id);
            $stmt->execute();
        }
    }

    checkAbsentees($assigned_class);
    $_SESSION['success'] = "Attendance submitted successfully.";
    header("Location: mark_attendance.php");
    exit;
}

// Detect students with 3+ recent absences
function checkAbsentees($class) {
    global $conn;
    $three_days_ago = date('Y-m-d', strtotime('-3 days'));
    $sql = "
        SELECT s.name, COUNT(a.status) AS absent_count
        FROM students s
        JOIN mark_attendance a ON s.id = a.student_id
        WHERE s.class = ? AND a.status = 'Absent' AND a.date >= ?
        GROUP BY s.id HAVING absent_count >= 3";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $class, $three_days_ago);
    $stmt->execute();
    $result = $stmt->get_result();
    $names = array_column($result->fetch_all(MYSQLI_ASSOC), 'name');
    if ($names) {
        $_SESSION['ai_message'] = "⚠️ Attendance Alert: The following students have been absent 3+ consecutive days - " . implode(', ', $names);
    }
}

// Fetch recent absences for the assigned class
$recent_absences_sql = "
    SELECT s.name, a.status, a.date
    FROM mark_attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.class = ? AND a.status = 'Absent' AND a.date >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)
    ORDER BY a.date DESC
";
$stmt = $conn->prepare($recent_absences_sql);
$stmt->bind_param("s", $assigned_class);
$stmt->execute();
$recent_absences = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management | EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container-fluid {
            max-width: 1700px;
            padding: 0 25px;
        }
        
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .dashboard-header {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            border-left: 4px solid var(--primary);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            margin-bottom: 25px;
        }
        
        .card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 25px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .student-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            transition: var(--transition);
        }
        
        .student-card:last-child {
            border-bottom: none;
        }
        
        .student-card:hover {
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .student-name {
            font-weight: 500;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .status-select {
            width: 140px;
            border-radius: 8px;
            border: 1px solid var(--light-gray);
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .status-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .badge {
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
        }
        
        .badge-present {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
        }
        
        .badge-late {
            background-color: rgba(248, 150, 30, 0.1);
            color: var(--warning);
        }
        
        .badge-absent {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid var(--light-gray);
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px 20px;
        }
        
        .ai-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            color: var(--dark);
            padding: 18px 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-width: 400px;
            display: flex;
            align-items: center;
            border-left: 4px solid var(--accent);
            transform: translateY(20px);
            opacity: 0;
            animation: slideIn 0.5s forwards;
        }
        
        @keyframes slideIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .ai-notification i {
            margin-right: 12px;
            font-size: 1.4rem;
            color: var(--accent);
        }
        
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            background-color: var(--primary);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 20px 25px;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .btn-close-white {
            filter: invert(1);
        }
        
        .attendance-date {
            background-color: var(--primary);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .attendance-date i {
            margin-right: 8px;
        }
        
        .floating-action-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3);
            z-index: 999;
            transition: var(--transition);
            border: none;
        }
        
        .floating-action-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px) scale(1.05);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--light-gray);
        }
        
        .empty-state h5 {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 20px;
            height: 100%;
        }
        
        .sidebar-title {
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .sidebar-title i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .class-info {
            background-color: rgba(67, 97, 238, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .class-info-title {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 5px;
        }
        
        .class-info-value {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .progress-thin {
            height: 6px;
            border-radius: 3px;
        }
        
        .attendance-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-value {
            font-weight: 600;
            font-size: 18px;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--gray);
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <div class="dashboard-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1"><i class="bi bi-calendar-check me-2"></i> Attendance Management</h2>
                            <p class="text-muted mb-0">Track and manage student attendance records</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="attendance-date me-3">
                                <i class="bi bi-calendar3"></i>
                                <span id="currentDate"><?= date('F j, Y') ?></span>
                            </span>
                            <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                             <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
                               </a>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-person-plus me-2"></i> Add Student
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="row">
                <div class="col">
                    <div class="alert alert-success alert-dismissible fade show">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="row">
                <div class="col">
                    <div class="alert alert-danger alert-dismissible fade show">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-people me-2"></i> Class Attendance</span>
                        <small class="text-muted"><?= count($students) ?> students</small>
                    </div>
                    <div class="card-body p-0">
                        <form method="POST">
                            <div class="p-4 border-bottom">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="attendance_date" class="form-control" required                                         value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Week</label>
                                        <select name="attendance_week" class="form-select" required>
                                            <option value="">Select Week</option>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <option value="Week <?= $i ?>">Week <?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Day</label>
                                        <select name="attendance_day" class="form-select" required>
                                            <?php foreach (["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"] as $day): ?>
                                                <option value="<?= $day ?>" <?= $day == date('l') ? 'selected' : '' ?>><?= $day ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $s): ?>
                                    <div class="student-card">
                                        <div class="student-name">
                                            <span class="student-avatar"><?= strtoupper(substr($s['name'], 0, 1)) ?></span>
                                            <?= htmlspecialchars($s['name']) ?>
                                        </div>
                                        <select name="attendance[<?= $s['id'] ?>]" class="status-select form-select">
                                            <option value="">Select Status</option>
                                            <option value="Present">Present</option>
                                            <option value="Late">Late</option>
                                            <option value="Absent">Absent</option>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-people"></i>
                                    <h5>No Students Found</h5>
                                    <p>You haven't added any students to this class yet.</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                        <i class="bi bi-person-plus me-2"></i> Add First Student
                                    </button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($students)): ?>
                                <div class="p-3 bg-light border-top">
                                    <input type="hidden" name="mark_attendance" value="1">
                                    <button type="submit" class="btn btn-primary w-100 py-3">
                                        <i class="bi bi-check-circle-fill me-2"></i> Submit Attendance Records
                                    </button>
                                    <a href="view_attendance.php" class="btn btn-outline-secondary w-100 py-3 mt-2">
                                        <i class="bi bi-eye me-2"></i> View Attendance
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sidebar">
                    <h5 class="sidebar-title">
                        <i class="bi bi-info-circle"></i> Class Information
                    </h5>
                    
                    <div class="class-info">
                        <div class="class-info-title">Class Name</div>
                        <div class="class-info-value"><?= htmlspecialchars($assigned_class) ?></div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="class-info-title">Attendance Rate</span>
                            <span class="class-info-title">78%</span>
                        </div>
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 78%"></div>
                        </div>
                        
                        <div class="attendance-stats">
                            <div class="stat-item">
                                <div class="stat-value text-success">78%</div>
                                <div class="stat-label">Present</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value text-warning">12%</div>
                                <div class="stat-label">Late</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value text-danger">10%</div>
                                <div class="stat-label">Absent</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="sidebar-title mt-4">
                        <i class="bi bi-clock-history"></i> Recent Absences
                    </h5>
                    
                    <div class="list-group list-group-flush">
                        <?php if (!empty($recent_absences)): ?>
                            <?php foreach ($recent_absences as $absence): ?>
                                <div class="list-group-item border-0 px-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-absent me-3"><?= htmlspecialchars($absence['status']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($absence['name']) ?> - <?= date('F j, Y', strtotime($absence['date'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item border-0 px-0 py-2">
                                <small class="text-muted">No recent absences found.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Add New Student</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Student Full Name</label>
                        <input type="text" name="student_name" class="form-control" placeholder="Enter student name" required>
                    </div>
                    <input type="hidden" name="add_student" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i> Save Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['ai_message'])): ?>
        <div class="ai-notification" id="aiNotification">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><?= $_SESSION['ai_message']; unset($_SESSION['ai_message']); ?></div>
        </div>
    <?php endif; ?>

    <button class="floating-action-btn" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-close notification after 7 seconds
        const aiNotification = document.getElementById('aiNotification');
        if (aiNotification) {
            setTimeout(() => {
                aiNotification.style.transition = 'opacity 0.5s';
                aiNotification.style.opacity = '0';
                setTimeout(() => aiNotification.remove(), 500);
            }, 7000);
        }
        
        // Format current date display
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);
        
        // Set default date to today in form
        document.querySelector('input[type="date"]').valueAsDate = new Date();
        
        // Add animation to student cards when page loads
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.student-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>
