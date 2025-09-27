<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase connection (must use pooler)
$host = "aws-1-eu-north-1.pooler.supabase.com"; 
$port = "6543";                                
$dbname = "postgres";                          
$user = "postgres.mqtuzltstbshtjigzujz";       
$password = "Ernestbizz..123";                 

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$classSelected = '';
$subjectSelected = '';
$yearSelected = '';
$examName = '';
$students = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_marks'])) {
        $examName = $_POST['examname'] ?? '';
        $classSelected = $_POST['class'] ?? '';
        $subjectSelected = $_POST['subject'] ?? '';
        $yearSelected = $_POST['year'] ?? '';
        
        // Flag to check if any empty scores exist
        $emptyScoresExist = false;
        $emptyScoreRows = [];

        foreach ($_POST['students'] as $studentId => $data) {
            $classScore = isset($data['class_score']) && $data['class_score'] !== '' ? (float)$data['class_score'] : 0;
            $examScore = isset($data['exam_score']) && $data['exam_score'] !== '' ? (float)$data['exam_score'] : 0;
            $totalScore = $classScore + $examScore;
            $admissionNumber = $data['admission_number'] ?? '';
            $position = $data['position'] ?? null;
            
            // Check for empty scores
            if ((!isset($data['class_score']) || $data['class_score'] === '') || 
                (!isset($data['exam_score']) || $data['exam_score'] === '')) {
                $emptyScoresExist = true;
                $emptyScoreRows[] = $studentId;
                continue; // Skip this iteration but continue processing
            }

            if ($totalScore >= 80) {
                $remarks = 'A';
            } elseif ($totalScore >= 70) {
                $remarks = 'B';
            } elseif ($totalScore >= 60) {
                $remarks = 'C';
            } elseif ($totalScore >= 50) {
                $remarks = 'D';
            } elseif ($totalScore >= 40) {
                $remarks = 'E';
            } else {
                $remarks = 'F';
            }

            $stmt = $conn->prepare("INSERT INTO marks (examname, class, class_score, exam_score, subject, student, admission_number, average, remarks, position, created_at) 
                                   VALUES (:examname, :class, :class_score, :exam_score, :subject, :student, :admission_number, :average, :remarks, :position, CURRENT_TIMESTAMP)");
            $stmt->execute([
                'examname' => $examName,
                'class' => $classSelected,
                'class_score' => $classScore,
                'exam_score' => $examScore,
                'subject' => $subjectSelected,
                'student' => $data['student_name'],
                'admission_number' => $admissionNumber,
                'average' => $totalScore / 2,
                'remarks' => $remarks,
                'position' => $position
            ]);
        }
        
        if ($emptyScoresExist) {
            echo "<script>document.addEventListener('DOMContentLoaded', function() { 
                showNotification('warning', 'Some scores were empty and set to zero. Please review.'); 
                highlightEmptyRows(" . json_encode($emptyScoreRows) . ");
            });</script>";
        } else {
            echo "<script>document.addEventListener('DOMContentLoaded', function() { showNotification('success', 'Marks submitted successfully!'); });</script>";
        }
    } else {
        $classSelected = $_POST['class'] ?? '';
        $subjectSelected = $_POST['subject'] ?? '';
        $yearSelected = $_POST['year'] ?? '';
        $examName = $_POST['examname'] ?? '';

        if ($classSelected && $yearSelected) {
            $stmt = $conn->prepare("SELECT * FROM student_entries WHERE class = :class AND year = :year");
            $stmt->execute(['class' => $classSelected, 'year' => $yearSelected]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Scores Management | Academic Portal</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #4895ef;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #4ade80;
            --warning-color: #fbbf24;
            --danger-color: #f87171;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --gray-light: #f1f5f9;
            --gray-medium: #cbd5e1;
            --gray-dark: #64748b;
            --border-radius: 12px;
            --box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 280px;
            --max-content-width: 1400px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--dark-color);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        
        .navbar {
            background-color: white;
            box-shadow: var(--box-shadow);
            padding: 1rem 1.5rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            font-size: 1.25rem;
        }
        
        .navbar-brand img {
            height: 36px;
            margin-right: 12px;
        }
        
        .container {
            max-width: var(--max-content-width);
            padding-left: 2rem;
            padding-right: 2rem;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            margin-bottom: 2rem;
            background-color: white;
            overflow: hidden;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .card-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0;
            display: flex;
            align-items: center;
            font-size: 1.25rem;
        }
        
        .card-title i {
            margin-right: 12px;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .btn {
            border-radius: var(--border-radius);
            padding: 0.625rem 1.25rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: var(--transition);
            border-width: 2px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 222, 128, 0.25);
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.875rem;
            border-radius: 50px;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }
        
        .table-responsive {
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            min-width: 900px;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            position: sticky;
            top: 0;
            white-space: nowrap;
        }
        
        .table tbody tr {
            transition: var(--transition);
            background-color: white;
        }
        
        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.03);
            transform: translateX(4px);
        }
        
        .table tbody tr.empty-row {
            background-color: rgba(248, 113, 113, 0.08);
            animation: pulseWarning 1.5s infinite;
        }
        
        .table tbody td {
            padding: 1.25rem 1rem;
            vertical-align: middle;
            border-top: 1px solid rgba(0, 0, 0, 0.03);
            font-size: 0.9375rem;
        }
        
        .form-control, .form-select {
            border-radius: var(--border-radius);
            padding: 0.625rem 0.875rem;
            border: 2px solid var(--gray-medium);
            transition: var(--transition);
            font-size: 0.9375rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .score-input {
            width: 80px;
            text-align: center;
            font-weight: 600;
            padding: 0.625rem;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
        }
        
        .score-input:focus {
            transform: scale(1.05);
            z-index: 10;
            position: relative;
        }
        
        .valid-score {
            background-color: rgba(74, 222, 128, 0.1);
            border-color: var(--success-color);
        }
        
        .invalid-score {
            background-color: rgba(248, 113, 113, 0.1);
            border-color: var(--danger-color);
        }
        
        .subject-header {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
            align-items: center;
            background-color: white;
            padding: 1.25rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .notification {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            padding: 1rem 1.5rem;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 500;
            box-shadow: var(--box-shadow);
            z-index: 9999;
            display: none;
            animation: fadeIn 0.4s, fadeOut 0.4s 2.5s;
            min-width: 300px;
            backdrop-filter: blur(10px);
            background-color: rgba(0, 0, 0, 0.8);
            border-left: 4px solid transparent;
        }
        
        .notification.success {
            border-left-color: var(--success-color);
        }
        
        .notification.warning {
            border-left-color: var(--warning-color);
        }
        
        .notification.error {
            border-left-color: var(--danger-color);
        }
        
        .position-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.875rem;
            transition: var(--transition);
        }
        
        .position-1 {
            background-color: #ffd700;
            color: #333;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3);
        }
        
        .position-2 {
            background-color: #c0c0c0;
            color: #333;
            box-shadow: 0 0 0 3px rgba(192, 192, 192, 0.3);
        }
        
        .position-3 {
            background-color: #cd7f32;
            color: white;
            box-shadow: 0 0 0 3px rgba(205, 127, 50, 0.3);
        }
        
        .position-other {
            background-color: var(--gray-light);
            color: var(--dark-color);
        }
        
        .remarks-badge {
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8125rem;
            min-width: 36px;
            text-align: center;
            display: inline-block;
        }
        
        .remarks-A {
            background-color: var(--success-color);
            color: white;
        }
        
        .remarks-B {
            background-color: #38bdf8;
            color: white;
        }
        
        .remarks-C {
            background-color: var(--warning-color);
            color: #333;
        }
        
        .remarks-D {
            background-color: #f97316;
            color: white;
        }
        
        .remarks-E {
            background-color: #d946ef;
            color: white;
        }
        
        .remarks-F {
            background-color: var(--danger-color);
            color: white;
        }
        
        .progress-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: transparent;
            z-index: 9998;
        }
        
        .progress-bar {
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .student-name {
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        
        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
            background-color: white;
            border-radius: var(--border-radius);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--gray-medium);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }
        
        .empty-state h5 {
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .empty-state p {
            color: var(--gray-dark);
            margin-bottom: 1.75rem;
            font-size: 0.9375rem;
        }
        
        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
        }
        
        .modal-title i {
            margin-right: 12px;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-size: 0.9375rem;
        }
        
        .total-cell {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            font-size: 0.875rem;
        }
        
        .breadcrumb-item.active {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .back-to-top:hover {
            background-color: var(--primary-light);
            transform: translateY(-3px);
        }

        @media (max-width: 1200px) {
            .container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .card-header {
                padding: 1.25rem;
            }
            
            .table tbody td {
                padding: 1rem 0.75rem;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .navbar {
                padding: 0.875rem 1rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1.25rem 1rem;
            }
            
            .subject-header {
                padding: 1rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .table-responsive {
                border: none;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .empty-state {
                padding: 2rem 1rem;
            }
            
            .empty-state i {
                font-size: 2.5rem;
            }
            
            .notification {
                min-width: 280px;
                right: 1rem;
                left: 1rem;
                margin: 0 auto;
                max-width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            .modal-body {
                padding: 1.25rem;
            }
            
            .score-input {
                width: 70px;
                padding: 0.5rem;
            }
            
            .student-avatar {
                width: 36px;
                height: 36px;
                margin-right: 8px;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-20px); }
        }
        
        @keyframes pulseWarning {
            0% { background-color: rgba(248, 113, 113, 0.08); }
            50% { background-color: rgba(248, 113, 113, 0.15); }
            100% { background-color: rgba(248, 113, 113, 0.08); }
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-light);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }
    </style>
</head>
<body>
    <!-- Progress Bar -->
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="Logo">
                Edupro suite 2.0
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="form.php"><i class="fas fa-users me-1"></i> Add Student</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Exam_scores.php"><i class="fas fa-chart-line me-1"></i> Exam Scores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-cog me-1"></i> Settings</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-2"><i class="fas fa-clipboard-check me-2"></i>Exam Scores Management</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#">Examinations</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Scores Entry</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#marksheetModal">
                    <i class="fas fa-plus-circle me-2"></i>New Marksheet
                </button>
            </div>
        </div>

        <?php if ($classSelected && $subjectSelected): ?>
        <div class="subject-header animate__animated animate__fadeIn">
            <h5 class="mb-0 me-3">Results for:</h5>
            <span class="badge bg-primary">
                <i class="fas fa-users me-1"></i> <?= htmlspecialchars($classSelected) ?>
            </span>
            <span class="badge bg-info">
                <i class="fas fa-book me-1"></i> <?= htmlspecialchars($subjectSelected) ?>
            </span>
            <span class="badge bg-dark">
                <i class="fas fa-calendar me-1"></i> <?= htmlspecialchars($yearSelected) ?>
            </span>
            <span class="badge bg-warning text-dark">
                <i class="fas fa-clipboard-list me-1"></i> <?= htmlspecialchars($examName) ?>
            </span>
        </div>
        <?php endif; ?>

        <div class="card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-table me-2"></i>Student Scores
                </h5>
                <?php if ($classSelected && $subjectSelected): ?>
                <div class="d-flex align-items-center">
                    <span class="badge bg-light text-dark me-2">
                        <i class="fas fa-user-graduate me-1"></i> <?= count($students) ?> Students
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-percentage me-1"></i> Max Score: 100
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <form method="POST" action="" id="marksForm">
                    <input type="hidden" name="class" value="<?= htmlspecialchars($classSelected) ?>">
                    <input type="hidden" name="subject" value="<?= htmlspecialchars($subjectSelected) ?>">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($yearSelected) ?>">
                    <input type="hidden" name="examname" value="<?= htmlspecialchars($examName) ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-hover" id="scoresTable">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Student</th>
                                    <th width="140">Admission No.</th>
                                    <th width="140">Class (50%)</th>
                                    <th width="140">Exam (50%)</th>
                                    <th width="100">Total</th>
                                    <th width="100">Position</th>
                                    <th width="100">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($students)): ?>
                                    <?php foreach ($students as $index => $row): ?>
                                        <tr data-student-id="<?= $row['id'] ?>">
                                            <td data-label="#"><?= $index + 1 ?></td>
                                            <td data-label="Student">
                                                <div class="student-name">
                                                    <div class="student-avatar">
                                                        <?= substr(htmlspecialchars($row['name']), 0, 1) ?>
                                                    </div>
                                                    <?= htmlspecialchars($row['name']) ?>
                                                </div>
                                            </td>
                                            <td data-label="Admission No."><?= htmlspecialchars($row['admission_number']) ?></td>
                                            <td data-label="Class (50%)">
                                                <input type="number" class="form-control class-score score-input" 
                                                    name="students[<?= $row['id'] ?>][class_score]" 
                                                    data-student-id="<?= $row['id'] ?>" 
                                                    max="50" min="0" placeholder="0-50"
                                                    tabindex="<?= $index * 2 + 1 ?>">
                                            </td>
                                            <td data-label="Exam (50%)">
                                                <input type="number" class="form-control exam-score score-input" 
                                                    name="students[<?= $row['id'] ?>][exam_score]" 
                                                    data-student-id="<?= $row['id'] ?>" 
                                                    max="50" min="0" placeholder="0-50"
                                                    tabindex="<?= $index * 2 + 2 ?>">
                                            </td>
                                            <td data-label="Total" class="total-cell fw-bold">0</td>
                                            <td data-label="Position" class="position-cell">
                                                <span class="position-badge position-other">-</span>
                                            </td>
                                            <td data-label="Grade" class="remarks-cell">
                                                <span class="remarks-badge">-</span>
                                            </td>
                                            <input type="hidden" name="students[<?= $row['id'] ?>][admission_number]" value="<?= htmlspecialchars($row['admission_number']) ?>">
                                            <input type="hidden" name="students[<?= $row['id'] ?>][student_name]" value="<?= htmlspecialchars($row['name']) ?>">
                                            <input type="hidden" name="students[<?= $row['id'] ?>][position]" class="position-input" value="">
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="fas fa-clipboard-list"></i>
                                                <h5>No students found</h5>
                                                <p>Select class, subject, and year to view students</p>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#marksheetModal">
                                                    <i class="fas fa-plus-circle me-2"></i>Select Criteria
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (!empty($students)): ?>
                    <div class="p-4 border-top">
                        <button type="submit" name="submit_marks" class="btn btn-success w-100 py-3" id="submitMarks">
                            <i class="fas fa-save me-2"></i>Submit Exam Scores
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification"></div>

    <!-- Modal -->
    <div class="modal fade" id="marksheetModal" tabindex="-1" aria-labelledby="marksheetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="marksheetModalLabel">
                        <i class="fas fa-filter me-2"></i>Select Criteria
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="marksheetForm" method="POST" action="">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Class</label>
                            <select class="form-select" name="class" required>
                                <option value="">Select Class</option>
                                <?php
                                $classes = $conn->query("SELECT DISTINCT class FROM student_entries");
                                while ($class = $classes->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$class['class']}'".($class['class'] == $classSelected ? ' selected' : '').">{$class['class']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Subject</label>
                            <select class="form-select" name="subject" required>
                                <option value="">Select Subject</option>
                                <?php
                                $subjects = $conn->query("SELECT name FROM subject");
                                while ($subject = $subjects->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$subject['name']}'".($subject['name'] == $subjectSelected ? ' selected' : '').">{$subject['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Exam Name</label>
                            <select class="form-select" name="examname" required>
                                <option value="">Select Exam</option>
                                <option value="Term One" <?= $examName == 'Term One' ? 'selected' : '' ?>>Term One</option>
                                <option value="Term Two" <?= $examName == 'Term Two' ? 'selected' : '' ?>>Term Two</option>
                                <option value="Term Three" <?= $examName == 'Term Three' ? 'selected' : '' ?>>Term Three</option>
                                <option value="Mid-Term" <?= $examName == 'Mid-Term' ? 'selected' : '' ?>>Mid-Term</option>
                                <option value="Final Exam" <?= $examName == 'Final Exam' ? 'selected' : '' ?>>Final Exam</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Academic Year</label>
                            <select class="form-select" name="year" required>
                                <option value="">Select Year</option>
                                <?php
                                $years = $conn->query("SELECT DISTINCT year FROM student_entries");
                                while ($year = $years->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$year['year']}'".($year['year'] == $yearSelected ? ' selected' : '').">{$year['year']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-search me-2"></i>Generate Marksheet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop"><i class="fas fa-chevron-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
                // Initialize progress bar
        function initProgressBar() {
            $(window).on('scroll', function() {
                var scrollTop = $(this).scrollTop();
                var docHeight = $(document).height();
                var winHeight = $(this).height();
                var progress = (scrollTop / (docHeight - winHeight)) * 100;
                $('#progressBar').width(progress + '%');

                // Show or hide the back to top button
                if (scrollTop > 200) {
                    $('#backToTop').fadeIn();
                } else {
                    $('#backToTop').fadeOut();
                }
            });
        }
        initProgressBar();
        
        // Back to Top button functionality
        $('#backToTop').on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 500);
        });

        // Notification function
        function showNotification(type, message) {
            const notification = $('#notification');
            notification.removeClass('success error warning').addClass(type).text(message).fadeIn();
            
            setTimeout(function() {
                notification.fadeOut();
            }, 3000);
        }
        
        // Highlight empty rows
        function highlightEmptyRows(studentIds) {
            studentIds.forEach(id => {
                $(`tr[data-student-id="${id}"]`).addClass('empty-row');
            });
        }

        // Handle score input validation and calculations
        $('#scoresTable').on('input', '.class-score, .exam-score', function() {
            const $input = $(this);
            const maxScore = 50;
            let value = parseInt($input.val()) || 0;
            
            // Validate score doesn't exceed maximum
            if (value > maxScore) {
                $input.val(maxScore);
                value = maxScore;
                $input.addClass('invalid-score').removeClass('valid-score');
                showNotification('error', 'Maximum score is 50');
            } else if (value < 0) {
                $input.val(0);
                value = 0;
                $input.addClass('invalid-score').removeClass('valid-score');
                showNotification('error', 'Score cannot be negative');
            } else {
                $input.addClass('valid-score').removeClass('invalid-score');
                $(this).closest('tr').removeClass('empty-row');
            }

            // Calculate total for this row
            const $row = $input.closest('tr');
            const classScore = parseInt($row.find('.class-score').val()) || 0;
            const examScore = parseInt($row.find('.exam-score').val()) || 0;
            const total = classScore + examScore;
            
            $row.find('.total-cell').text(total);
            
            // Update remarks based on total score
            let remarks = '-';
            let remarksClass = '';
            if (total >= 80) {
                remarks = 'A';
                remarksClass = 'remarks-A';
            } else if (total >= 70) {
                remarks = 'B';
                remarksClass = 'remarks-B';
            } else if (total >= 60) {
                remarks = 'C';
                remarksClass = 'remarks-C';
            } else if (total >= 50) {
                remarks = 'D';
                remarksClass = 'remarks-D';
            } else if (total >= 40) {
                remarks = 'E';
                remarksClass = 'remarks-E';
            } else if (total > 0) {
                remarks = 'F';
                remarksClass = 'remarks-F';
            }
            
            $row.find('.remarks-cell span')
                .text(remarks)
                .removeClass()
                .addClass('remarks-badge ' + remarksClass);

            // Update all positions based on new totals
            updatePositions();
        });

        // Function to calculate and update student positions
        function updatePositions() {
            const rows = $('#scoresTable tbody tr').get();
            
            // Create array of objects with total scores and row elements
            const studentScores = rows.map((row) => {
                return {
                    element: row,
                    total: parseInt($(row).find('.total-cell').text()) || 0
                };
            });
            
            // Sort by total score descending
            studentScores.sort((a, b) => b.total - a.total);
            
            // Update position numbers and styling
            studentScores.forEach((student, index) => {
                const position = index + 1;
                const $positionBadge = $(student.element).find('.position-cell span');
                const $positionInput = $(student.element).find('.position-input');
                
                $positionBadge.text(position);
                $positionInput.val(position);
                
                // Update position badge styling
                $positionBadge.removeClass('position-1 position-2 position-3 position-other');
                
                if (position === 1) {
                    $positionBadge.addClass('position-1');
                } else if (position === 2) {
                    $positionBadge.addClass('position-2');
                } else if (position === 3) {
                    $positionBadge.addClass('position-3');
                } else {
                    $positionBadge.addClass('position-other');
                }
            });
        }
        
        // Keyboard navigation between cells
        $('#scoresTable').on('keydown', '.score-input', function(e) {
            const $currentInput = $(this);
            const currentRow = $currentInput.closest('tr');
            const currentIndex = currentRow.index();
            const isClassScore = $currentInput.hasClass('class-score');
            
            // Handle arrow keys
            switch(e.keyCode) {
                case 38: // Up arrow
                    e.preventDefault();
                    if (currentIndex > 0) {
                        const prevRow = currentRow.prev();
                        const targetInput = isClassScore ? prevRow.find('.class-score') : prevRow.find('.exam-score');
                        targetInput.focus().select();
                    }
                    break;
                case 40: // Down arrow
                    e.preventDefault();
                    if (currentIndex < $('#scoresTable tbody tr').length - 1) {
                        const nextRow = currentRow.next();
                        const targetInput = isClassScore ? nextRow.find('.class-score') : nextRow.find('.exam-score');
                        targetInput.focus().select();
                    }
                    break;
                case 37: // Left arrow
                    if (isClassScore) {
                        e.preventDefault();
                        currentRow.find('.exam-score').focus().select();
                    }
                    break;
                case 39: // Right arrow
                    if (!isClassScore) {
                        e.preventDefault();
                        currentRow.find('.class-score').focus().select();
                    }
                    break;
                case 13: // Enter
                    e.preventDefault();
                    if (isClassScore) {
                        currentRow.find('.exam-score').focus().select();
                    } else if (currentIndex < $('#scoresTable tbody tr').length - 1) {
                        const nextRow = currentRow.next();
                        nextRow.find('.class-score').focus().select();
                    }
                    break;
            }
        });

        // Form submission validation
        $('#marksForm').on('submit', function(e) {
            let emptyScores = [];
            let hasEmptyScores = false;
            
            // Check for empty scores
            $('.score-input').each(function() {
                const $input = $(this);
                if ($input.val() === '') {
                    hasEmptyScores = true;
                    emptyScores.push($input.data('student-id'));
                    $input.closest('tr').addClass('empty-row');
                    $input.val(0); // Set empty values to zero
                }
            });
            
            if (hasEmptyScores) {
                showNotification('warning', 'Empty scores were set to zero. Please review highlighted rows.');
                // Scroll to first empty row
                $('html, body').animate({
                    scrollTop: $(`tr[data-student-id="${emptyScores[0]}"]`).offset().top - 100
                }, 500);
                
                // Continue with submission after setting empty values to zero
                return true;
            }
            
            return true;
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Show success notification if marks were submitted
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_marks'])): ?>
        $(document).ready(function() {
            <?php if ($emptyScoresExist): ?>
            showNotification('warning', 'Some scores were empty and set to zero. Please review.');
            highlightEmptyRows(<?= json_encode($emptyScoreRows) ?>);
            <?php else: ?>
            showNotification('success', 'Marks submitted successfully!');
            <?php endif; ?>
        });
        <?php endif; ?>
        
        // Focus first input when modal is shown
        $('#marksheetModal').on('shown.bs.modal', function () {
            $(this).find('select[name="class"]').focus();
        });
        
        // Add smooth scrolling to anchor links
        $('a[href^="#"]').on('click', function(event) {
            event.preventDefault();
            $('html, body').animate({
                scrollTop: $($(this).attr('href')).offset().top - 20
            }, 500);
        });

        // Auto-focus first score input when page loads with students
        <?php if (!empty($students)): ?>
        $(document).ready(function() {
            setTimeout(function() {
                $('#scoresTable tbody tr:first-child .class-score').focus();
            }, 300);
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>














