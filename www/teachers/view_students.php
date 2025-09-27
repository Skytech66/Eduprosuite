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
                <i class="fas fa-users-class mr-1"></i> Class Roster
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
                        <i class="fas fa-users-class"></i> Class Roster Management
                    </h1>
                    <p class="card-subtitle">View and manage student information and photos</p>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="class" class="form-label">
                                <i class="fas fa-chalkboard mr-1"></i> Select Class
                            </label>
                            <select name="class" id="class" class="form-select" required>
                                <option value="">-- Select a class --</option>
                                <?php
                                try {
                                    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
                                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                    
                                    $classQuery = "SELECT DISTINCT class FROM marks WHERE class IS NOT NULL AND class != '' ORDER BY class";
                                    $classResult = $conn->query($classQuery);

                                    if ($classResult) {
                                        while ($row = $classResult->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<option value="' . htmlspecialchars($row['class']) . '">' . htmlspecialchars($row['class']) . '</option>';
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
                    
                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class'])): ?>
                        <?php
                        $selectedClass = $_POST['class'];
                        $studentQuery = "SELECT DISTINCT student, photo FROM marks WHERE class = :class ORDER BY student";
                        $stmt = $conn->prepare($studentQuery);
                        $stmt->bindParam(':class', $selectedClass);
                        $stmt->execute();
                        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $studentCount = count($students);
                        ?>

                        <div class="mt-6">
                            <h2 class="section-title">
                                <i class="fas fa-user-graduate"></i>
                                <?= htmlspecialchars($selectedClass) ?> Roster
                            </h2>
                            <span class="badge"><?= $studentCount ?> student<?= $studentCount !== 1 ? 's' : '' ?></span>

                            <?php if ($studentCount > 0): ?>
                                <form action="upload_image.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                                    <input type="hidden" name="class" value="<?= htmlspecialchars($selectedClass) ?>">

                                    <div class="student-grid" id="studentGrid">
                                        <?php foreach ($students as $student): ?>
                                            <div class="student-card">
                                                <img src="<?= htmlspecialchars($student['photo'] ? $student['photo'] : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23e5e7eb\'%3E%3Cpath d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z\'/%3E%3C/svg%3E') ?>"
                                                     alt="<?= htmlspecialchars($student['student']) ?>"
                                                     class="student-avatar"
                                                     id="preview-<?= htmlspecialchars($student['student']) ?>">
                                                <div class="student-info">
                                                    <h3 class="student-name"><?= htmlspecialchars($student['student']) ?></h3>

                                                    <div class="file-input-wrapper">
                                                        <label for="file-<?= htmlspecialchars($student['student']) ?>" class="file-input-label">
                                                            <i class="fas fa-camera mr-1"></i> Update Photo
                                                        </label>
                                                        <input type="file"
                                                               id="file-<?= htmlspecialchars($student['student']) ?>"
                                                               name="images[]"
                                                               accept="image/*"
                                                               class="file-input"
                                                               data-student-id="<?= htmlspecialchars($student['student']) ?>"
                                                               onchange="previewImage(this)">
                                                        <input type="hidden" name="iduser[]" value="<?= htmlspecialchars($student['student']) ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="text-center mt-6">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-upload mr-1"></i> Upload Selected Photos
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No Students Found</h3>
                                    <p>There are currently no students registered in this class.</p>
                                    <button class="btn btn-secondary" onclick="history.back()">
                                        <i class="fas fa-arrow-left mr-1"></i> Back to Class Selection
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Student List Management Card -->
            <div class="card" id="listCard" style="display: none;">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-list-check"></i> Student List Management
                    </h1>
                    <p class="card-subtitle">View and manage student entries by year and class</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="year" class="form-label">
                                <i class="fas fa-calendar-alt mr-1"></i> Select Year
                            </label>
                            <select name="year" id="year" class="form-select" required>
                                <option value="">-- Select a year --</option>
                                <?php
                                $yearQuery = "SELECT DISTINCT year FROM student_entries WHERE year IS NOT NULL AND year != '' ORDER BY year";
                                $yearResult = $conn->query($yearQuery);

                                if ($yearResult) {
                                    while ($row = $yearResult->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . htmlspecialchars($row['year']) . '">' . htmlspecialchars($row['year']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No years available</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="class" class="form-label">
                                <i class="fas fa-chalkboard mr-1"></i> Select Class
                            </label>
                            <select name="class" id="class" class="form-select" required>
                                <option value="">-- Select a class --</option>
                                <?php
                                $classQuery = "SELECT DISTINCT class FROM student_entries WHERE class IS NOT NULL AND class != '' ORDER BY class";
                                $classResult = $conn->query($classQuery);

                                if ($classResult) {
                                    while ($row = $classResult->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . htmlspecialchars($row['class']) . '">' . htmlspecialchars($row['class']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No classes available</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-eye mr-1"></i> View Student List
                        </button>
                    </form>

                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['year']) && isset($_POST['class'])): ?>
                        <?php
                        $selectedYear = $_POST['year'];
                        $selectedClass = $_POST['class'];

                        $studentListQuery = "SELECT id, name FROM student_entries WHERE year = :year AND class = :class ORDER BY name";
                        $stmt = $conn->prepare($studentListQuery);
                        $stmt->bindParam(':year', $selectedYear);
                        $stmt->bindParam(':class', $selectedClass);
                        $stmt->execute();
                        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $studentsCount = count($studentsList);
                        ?>

                        <div class="mt-6">
                            <h2 class="section-title">
                                <i class="fas fa-users mr-1"></i>
                                <?= htmlspecialchars($selectedClass) ?> - <?= htmlspecialchars($selectedYear) ?> Students
                            </h2>
                            <span class="badge"><?= $studentsCount ?> student<?= $studentsCount !== 1 ? 's' : '' ?></span>

                            <?php if ($studentsCount > 0): ?>
                                <ul class="student-list">
                                    <?php foreach ($studentsList as $student): ?>
                                        <li class="student-list-item">
                                            <span><?= htmlspecialchars($student['name']) ?></span>
                                            <a href="?delete_id=<?= htmlspecialchars($student['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete(event)">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <h3>No Students Found</h3>
                                    <p>There are currently no students registered for this year and class.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Add New Student Button -->
        <div class="action-buttons">
            <a href="form.php" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add New Student
            </a>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toast-message">Notification message</span>
    </div>

    <script>
        // Hide loading overlay when page is loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('loadingOverlay').style.opacity = '0';
                setTimeout(function() {
                    document.getElementById('loadingOverlay').style.display = 'none';
                }, 300);
            }, 500);
        });

        // Tab switching functionality
        const tabs = document.querySelectorAll('.tab');
        const rosterCard = document.getElementById('rosterCard');
        const listCard = document.getElementById('listCard');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show corresponding card
                const tabName = this.getAttribute('data-tab');
                if (tabName === 'roster') {
                    rosterCard.style.display = 'block';
                    listCard.style.display = 'none';
                    showToast('Showing Class Roster', 'success');
                } else {
                    rosterCard.style.display = 'none';
                    listCard.style.display = 'block';
                    showToast('Showing Student List', 'success');
                }
            });
        });

        // Image preview functionality
        function previewImage(input) {
            const studentId = input.getAttribute('data-student-id');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('preview-' + studentId).src = e.target.result;
                showToast('Image selected for ' + studentId, 'success');
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // Toast notification function
        function showToast(message, type = 'default') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            // Set message and type
            toastMessage.textContent = message;
            
            // Reset classes and set new type
            toast.className = 'toast';
            toast.classList.add('show', type);
            
            // Set icon based on type
            const icon = toast.querySelector('i');
            if (type === 'success') {
                icon.className = 'fas fa-check-circle';
            } else if (type === 'error') {
                icon.className = 'fas fa-exclamation-circle';
            } else if (type === 'warning') {
                icon.className = 'fas fa-exclamation-triangle';
            } else {
                icon.className = 'fas fa-info-circle';
            }
            
            // Hide after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Confirm before deleting
        function confirmDelete(event) {
            if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
                event.preventDefault();
                return false;
            }
            showToast('Student deleted successfully', 'success');
            return true;
        }

        // Show success toast if redirected from delete action
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('delete_success')) {
            showToast('Student deleted successfully', 'success');
        }
    </script>
</body>
</html>
<?php
// End output buffering and flush
ob_end_flush();
?>


                                <?= htmlspecialchars($
