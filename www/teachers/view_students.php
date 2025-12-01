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

// Handle student deletion
if (isset($_GET['delete_id'])) {
    try {
        $deleteStmt = $conn->prepare("DELETE FROM student_entries WHERE id = :id");
        $deleteStmt->bindParam(':id', $_GET['delete_id']);
        $deleteStmt->execute();
        
        // Redirect to avoid resubmission
        header("Location: " . str_replace("?delete_id=" . $_GET['delete_id'], "", $_SERVER['REQUEST_URI']) . "?delete_success=1");
        exit();
    } catch (PDOException $e) {
        $deleteError = "Error deleting student: " . $e->getMessage();
    }
}

// Start output buffering for smooth loading
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | Class Roster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Your CSS styles remain the same */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #e0e7ff;
            --secondary: #6366f1;
            --accent: #7c3aed;
            --dark: #111827;
            --darker: #0f172a;
            --light: #f8fafc;
            --lighter: #f9fafb;
            --gray: #6b7280;
            --light-gray: #e5e7eb;
            --lighter-gray: #f3f4f6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --border-radius-xs: 4px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--lighter);
            color: var(--dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            opacity: 0;
            animation: fadeIn 0.5s ease-in-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            margin-bottom: 1rem;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .dashboard {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            padding: 1rem 0 3rem;
        }

        @media (min-width: 1024px) {
            .dashboard {
                grid-template-columns: 1fr 1fr;
            }
        }

        .tab-container {
            display: flex;
            border-bottom: 1px solid var(--light-gray);
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            border-bottom: 2px solid transparent;
            transition: var(--transition);
            white-space: nowrap;
        }

        .tab.active {
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
        }

        .tab:hover:not(.active) {
            color: var(--dark);
            background-color: var(--lighter-gray);
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            opacity: 0;
            transform: translateY(20px);
            animation: cardEntry 0.5s ease forwards;
            animation-delay: 0.3s;
        }

        @keyframes cardEntry {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 1.25rem 1.5rem;
            position: relative;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-subtitle {
            font-size: 0.8125rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.875rem;
        }

        .form-select, .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-sm);
            font-size: 0.9375rem;
            transition: var(--transition);
            background-color: white;
        }

        .form-select:focus, .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: 0.9375rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--light-gray);
        }

        .btn-secondary:hover {
            background-color: var(--lighter-gray);
        }

        .btn-danger {
            background-color: var(--error);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
        }

        .btn-lg {
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 500;
            border-radius: var(--border-radius-xs);
            background-color: var(--primary-light);
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--darker);
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }

        @media (max-width: 640px) {
            .student-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        .student-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .student-avatar {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-gray);
            background-color: #f8fafc;
        }

        @media (max-width: 640px) {
            .student-avatar {
                height: 120px;
            }
        }

        .student-info {
            padding: 1rem;
        }

        .student-name {
            font-weight: 600;
            font-size: 0.9375rem;
            margin-bottom: 0.75rem;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .file-input-wrapper {
            position: relative;
            margin-top: 0.5rem;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: var(--lighter);
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
            cursor: pointer;
            transition: var(--transition);
            border: 1px dashed var(--light-gray);
            font-weight: 500;
            color: var(--gray);
        }

        .file-input-label:hover {
            background-color: var(--lighter-gray);
            border-color: var(--gray);
        }

        .file-input {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
            opacity: 0.7;
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .student-list {
            list-style: none;
            margin-top: 1.5rem;
        }

        .student-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .student-list-item:last-child {
            border-bottom: none;
        }

        .student-list-item:hover {
            background-color: var(--lighter-gray);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            padding: 0.875rem 1.25rem;
            background-color: var(--dark);
            color: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-lg);
            transform: translateY(100px);
            opacity: 0;
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            max-width: 90%;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast i {
            font-size: 1.1rem;
        }

        .toast.success {
            background-color: var(--success);
        }

        .toast.error {
            background-color: var(--error);
        }

        .toast.warning {
            background-color: var(--warning);
        }

        .mt-6 {
            margin-top: 1.5rem;
        }

        .text-center {
            text-align: center;
        }

        .mr-1 {
            margin-right: 0.25rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .btn {
                width: 100%;
            }
            
            .toast {
                bottom: 1rem;
                right: 1rem;
                left: 1rem;
                max-width: calc(100% - 2rem);
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header with navigation -->
        <div class="header">
            <h1 class="header-title">
                <i class="fas fa-users-class"></i> STUDENT PHOTO & LIST
            </h1>
            <div>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Tab navigation for mobile -->
        <div class="tab-container">
            <div class="tab active" data-tab="roster">
                <i class="fas fa-users mr-1"></i> Class Roster
            </div>
            <div class="tab" data-tab="list">
                <i class="fas fa-list-check mr-1"></i> Student List
            </div>
        </div>

        <!-- Main dashboard content -->
        <div class="dashboard">
            <!-- Class Roster Management Card -->
            <div class="card" id="rosterCard">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-users"></i> Class Roster Management
                    </h1>
                    <p class="card-subtitle">View and manage student information and photos</p>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="year_roster" class="form-label">
                                <i class="fas fa-calendar-alt mr-1"></i> Select Year
                            </label>
                            <select name="year" id="year_roster" class="form-select" required>
                                <option value="">-- Select a year --</option>
                                <!-- Hardcoded years 2025 and 2026 -->
                                <?php
                                $hardcodedYears = ['2025', '2026'];
                                foreach ($hardcodedYears as $year) {
                                    $selected = (isset($_POST['year']) && $_POST['year'] == $year) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($year) . '" ' . $selected . '>' . htmlspecialchars($year) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="class_roster" class="form-label">
                                <i class="fas fa-chalkboard mr-1"></i> Select Class
                            </label>
                            <select name="class" id="class_roster" class="form-select" required>
                                <option value="">-- Select a class --</option>
                                <?php
                                try {
                                    $classQuery = "SELECT DISTINCT class FROM marks WHERE class IS NOT NULL AND class != '' ORDER BY class";
                                    $classResult = $conn->query($classQuery);

                                    if ($classResult) {
                                        while ($row = $classResult->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = (isset($_POST['class']) && $_POST['class'] == $row['class']) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($row['class']) . '" ' . $selected . '>' . htmlspecialchars($row['class']) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No classes available</option>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<option value="">Error loading classes</option>';
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search mr-1"></i> View Class Roster
                        </button>
                    </form>
                    
                    <!-- UPDATED BLOCK WITH YEAR FILTERING -->
                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class']) && isset($_POST['year'])): ?>
                        <?php
                        $selectedClass = $_POST['class'];
                        $selectedYear = $_POST['year'];
                        try {
                            $studentQuery = "SELECT DISTINCT student, photo FROM marks WHERE class = :class AND year = :year ORDER BY student";
                            $stmt = $conn->prepare($studentQuery);
                            $stmt->bindParam(':class', $selectedClass);
                            $stmt->bindParam(':year', $selectedYear);
                            $stmt->execute();
                            $students = $stmt->fetchAll();
                            
                            if (count($students) > 0): ?>
                                <div class="badge">
                                    <i class="fas fa-users mr-1"></i> <?php echo count($students); ?> students found in <?php echo htmlspecialchars($selectedClass); ?> (Year: <?php echo htmlspecialchars($selectedYear); ?>)
                                </div>
                                
                                <div class="student-grid">
                                    <?php foreach ($students as $student): ?>
                                        <div class="student-card">
                                            <?php if (!empty($student['photo'])): ?>
                                                <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="<?php echo htmlspecialchars($student['student']); ?>" class="student-avatar" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFNUU3RUIiLz48cGF0aCBkPSJNNTAgMTAwQzc3LjYxNDIgMTAwIDEwMCA3Ny42MTQyIDEwMCA1MEMxMDAgMjIuODU3OCA3Ny42MTQyIDAgNTAgMEMyMi44NTc4IDAgMCAyMi44NTc4IDAgNTBDMCA3Ny42MTQyIDIyLjg1NzggMTAwIDUwIDEwMFpNNTAgMTIwQzIyLjM4NTggMTIwIDAgMTQyLjM4NiAwIDE3MFYxNjBDMCAxNzMuMjA1IDUuMzcyNTggMTgwIDEyIDE4MEg4OEM5NC42Mjc0IDE4MCAxMDAgMTczLjIwNSAxMDAgMTYwVjE3MEMxMDAgMTQyLjM4NiA3Ny42MTQyIDEyMCA1MCAxMjBaIiBmaWxsPSIjOTk5Q0FGIi8+PC9zdmc+'">
                                            <?php else: ?>
                                                <div class="student-avatar" style="display: flex; align-items: center; justify-content: center; background-color: #e0e7ff;">
                                                    <i class="fas fa-user-graduate" style="font-size: 3rem; color: #4f46e5;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="student-info">
                                                <div class="student-name"><?php echo htmlspecialchars($student['student']); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state mt-6">
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No students found</h3>
                                    <p>No students found in class <?php echo htmlspecialchars($selectedClass); ?> for year <?php echo htmlspecialchars($selectedYear); ?>.</p>
                                </div>
                            <?php endif;
                            
                        } catch (PDOException $e) {
                            echo '<div class="empty-state mt-6">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h3>Database Error</h3>
                                    <p>Error loading students: ' . htmlspecialchars($e->getMessage()) . '</p>
                                </div>';
                        }
                        ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student List Management Card -->
            <div class="card" id="listCard">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-list-check"></i> Student List Management
                    </h1>
                    <p class="card-subtitle">Manage all student entries in the database</p>
                </div>
                
                <div class="card-body">
                    <div class="action-buttons">
                        <a href="#" class="btn btn-primary" onclick="showAddForm()">
                            <i class="fas fa-plus mr-1"></i> Add New Student
                        </a>
                        <a href="#" class="btn btn-secondary" onclick="refreshStudentList()">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh List
                        </a>
                    </div>
                    
                    <!-- Add Student Form (Hidden by default) -->
                    <div id="addStudentForm" style="display: none; margin-bottom: 1.5rem; padding: 1.5rem; background-color: var(--lighter); border-radius: var(--border-radius-sm); border: 1px solid var(--light-gray);">
                        <h3 class="section-title">
                            <i class="fas fa-user-plus"></i> Add New Student
                        </h3>
                        <form method="POST" action="add_student.php" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="student_name" class="form-label">Student Name</label>
                                <input type="text" id="student_name" name="student_name" class="form-input" required placeholder="Enter student name">
                            </div>
                            <div class="form-group">
                                <label for="student_class" class="form-label">Class</label>
                                <select id="student_class" name="student_class" class="form-select" required>
                                    <option value="">-- Select class --</option>
                                    <?php
                                    try {
                                        $classQuery = "SELECT DISTINCT class FROM marks WHERE class IS NOT NULL AND class != '' ORDER BY class";
                                        $classResult = $conn->query($classQuery);

                                        if ($classResult) {
                                            while ($row = $classResult->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . htmlspecialchars($row['class']) . '">' . htmlspecialchars($row['class']) . '</option>';
                                            }
                                        }
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error loading classes</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="student_photo" class="form-label">Student Photo</label>
                                <div class="file-input-wrapper">
                                    <label for="student_photo" class="file-input-label">
                                        <i class="fas fa-camera mr-1"></i> Choose Photo
                                    </label>
                                    <input type="file" id="student_photo" name="student_photo" class="file-input" accept="image/*">
                                </div>
                                <small style="display: block; margin-top: 0.5rem; color: var(--gray); font-size: 0.75rem;">Optional: Upload a photo for the student</small>
                            </div>
                            <div class="form-group">
                                <label for="student_year" class="form-label">Year</label>
                                <select id="student_year" name="student_year" class="form-select" required>
                                    <option value="">-- Select year --</option>
                                    <!-- Hardcoded years 2025 and 2026 -->
                                    <?php
                                    $hardcodedYears = ['2025', '2026'];
                                    foreach ($hardcodedYears as $year) {
                                        echo '<option value="' . htmlspecialchars($year) . '">' . htmlspecialchars($year) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div style="display: flex; gap: 1rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Save Student
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="hideAddForm()">
                                    <i class="fas fa-times mr-1"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Student List -->
                    <h3 class="section-title">
                        <i class="fas fa-users"></i> All Students
                    </h3>
                    
                    <?php
                    try {
                        $allStudentsQuery = "SELECT * FROM student_entries ORDER BY created_at DESC";
                        $allStudentsResult = $conn->query($allStudentsQuery);
                        $allStudents = $allStudentsResult->fetchAll();
                        
                        if (count($allStudents) > 0): ?>
                            <div class="badge">
                                <i class="fas fa-users mr-1"></i> <?php echo count($allStudents); ?> total students
                            </div>
                            
                            <ul class="student-list">
                                <?php foreach ($allStudents as $student): ?>
                                    <li class="student-list-item">
                                        <div style="flex: 1;">
                                            <strong><?php echo htmlspecialchars($student['student_name']); ?></strong>
                                            <br>
                                            <small style="color: var(--gray);">
                                                Class: <?php echo htmlspecialchars($student['class']); ?> | 
                                                Year: <?php echo htmlspecialchars($student['year']); ?> | 
                                                Added: <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="?delete_id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <h3>No students found</h3>
                                <p>No students have been added yet. Use the "Add New Student" button to get started.</p>
                            </div>
                        <?php endif;
                        
                    } catch (PDOException $e) {
                        echo '<div class="empty-state">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h3>Database Error</h3>
                                <p>Error loading students: ' . htmlspecialchars($e->getMessage()) . '</p>
                            </div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast notifications -->
    <?php if (isset($_GET['delete_success']) && $_GET['delete_success'] == 1): ?>
        <div class="toast success" id="successToast">
            <i class="fas fa-check-circle"></i>
            Student deleted successfully!
        </div>
    <?php endif; ?>
    
    <?php if (isset($deleteError)): ?>
        <div class="toast error" id="errorToast">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($deleteError); ?>
        </div>
    <?php endif; ?>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding card
                if (tabId === 'roster') {
                    document.getElementById('rosterCard').style.display = 'block';
                    document.getElementById('listCard').style.display = 'none';
                } else if (tabId === 'list') {
                    document.getElementById('rosterCard').style.display = 'none';
                    document.getElementById('listCard').style.display = 'block';
                }
            });
        });

        // Toast notification handling
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                ${message}
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }

        // Auto-hide existing toasts
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);
                
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }, 3000);
            });
        });

        // Add Student Form functions
        function showAddForm() {
            document.getElementById('addStudentForm').style.display = 'block';
            document.getElementById('addStudentForm').scrollIntoView({ behavior: 'smooth' });
        }

        function hideAddForm() {
            document.getElementById('addStudentForm').style.display = 'none';
        }

        function refreshStudentList() {
            location.reload();
        }

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = 'var(--error)';
                    } else {
                        field.style.borderColor = '';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    showToast('Please fill in all required fields.', 'error');
                }
            });
        });

        // Image preview for file inputs
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        // You could add image preview functionality here
                        console.log('Image selected:', file.name);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>
<?php
// End output buffering
ob_end_flush();
?>
