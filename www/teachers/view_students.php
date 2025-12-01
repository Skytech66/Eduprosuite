<?php
// Start output buffering immediately to prevent header issues
ob_start();

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

// ----------------------------
// Handle student deletion safely
// ----------------------------
$deleteError = '';
if (isset($_GET['delete_id'])) {
    try {
        $deleteStmt = $conn->prepare("DELETE FROM student_entries WHERE id = :id");
        $deleteStmt->bindParam(':id', $_GET['delete_id']);
        $deleteStmt->execute();
        
        // Redirect safely to same page without delete_id param
        $params = $_GET;
        unset($params['delete_id']); // remove delete_id
        $params['delete_success'] = 1; // add success flag
        $redirectUrl = $_SERVER['PHP_SELF'];
        if (!empty($params)) {
            $redirectUrl .= '?' . http_build_query($params);
        }
        header("Location: $redirectUrl");
        exit();
    } catch (PDOException $e) {
        $deleteError = "Error deleting student: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | Class Roster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .header-title {
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .tab-container {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 8px;
            margin-bottom: 30px;
        }

        .tab {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            color: white;
            cursor: pointer;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            padding: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .card-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card-subtitle {
            opacity: 0.9;
            font-size: 16px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .form-select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background: white;
        }

        .form-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-lg {
            padding: 18px 40px;
            font-size: 18px;
        }

        .mt-6 {
            margin-top: 60px;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .badge {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .student-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .student-card:hover {
            transform: translateY(-5px);
        }

        .student-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 5px solid #f0f0f0;
        }

        .student-name {
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        .file-input-wrapper {
            position: relative;
            margin-top: 15px;
        }

        .file-input-label {
            display: block;
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-top: 30px;
        }

        .empty-state i {
            font-size: 64px;
            color: #6c757d;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
        }

        .student-list {
            list-style: none;
            margin-top: 30px;
        }

        .student-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }

        .student-list-item span {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .action-buttons {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 1000;
        }

        .text-center {
            text-align: center;
        }

        .mr-1 {
            margin-right: 8px;
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 20px 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            transform: translateX(150%);
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 9999;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            border-left: 5px solid #28a745;
        }

        .toast.error {
            border-left: 5px solid #dc3545;
        }

        .toast.warning {
            border-left: 5px solid #ffc107;
        }

        .toast i {
            font-size: 24px;
            margin-right: 15px;
        }

        .toast.success i {
            color: #28a745;
        }

        .toast.error i {
            color: #dc3545;
        }

        .toast.warning i {
            color: #ffc107;
        }

        #toast-message {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .student-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                bottom: 20px;
                right: 20px;
            }

            .btn-lg {
                width: 100%;
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
                    
                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class']) && !isset($_POST['year'])): ?>
                        <?php
                        $selectedClass = $_POST['class'];
                        try {
                            $studentQuery = "SELECT DISTINCT student, photo FROM marks WHERE class = :class ORDER BY student";
                            $stmt = $conn->prepare($studentQuery);
                            $stmt->bindParam(':class', $selectedClass);
                            $stmt->execute();
                            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $studentCount = count($students);
                        } catch (PDOException $e) {
                            $error = "Error loading students: " . $e->getMessage();
                            $studentCount = 0;
                            $students = [];
                        }
                        ?>

                        <div class="mt-6">
                            <h2 class="section-title">
                                <i class="fas fa-user-graduate"></i>
                                <?= htmlspecialchars($selectedClass) ?> Roster
                            </h2>
                            <span class="badge"><?= $studentCount ?> student<?= $studentCount !== 1 ? 's' : '' ?></span>

                            <?php if (isset($error)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h3>Error Loading Data</h3>
                                    <p><?= htmlspecialchars($error) ?></p>
                                </div>
                            <?php elseif ($studentCount > 0): ?>
                                <form action="upload_image.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                                    <input type="hidden" name="class" value="<?= htmlspecialchars($selectedClass) ?>">

                                    <div class="student-grid" id="studentGrid">
                                        <?php foreach ($students as $student): ?>
                                            <div class="student-card">
                                                <?php
                                                // Handle base64 image display: prepend data URI prefix if photo exists
                                                $imageSrc = '';
                                                if (!empty($student['photo'])) {
                                                    $imageSrc = 'data:image/jpeg;base64,' . htmlspecialchars($student['photo']);
                                                } else {
                                                    $imageSrc = 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23e5e7eb\'%3E%3Cpath d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z\'/%3E%3C/svg%3E';
                                                }
                                                ?>
                                                <img src="<?= $imageSrc ?>"
                                                     alt="<?= htmlspecialchars($student['student']) ?> Photo"
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
                    <form method="GET" action="">
                        <div class="form-group">
                            <label for="year" class="form-label">
                                <i class="fas fa-calendar-alt mr-1"></i> Select Year
                            </label>
                            <select name="year" id="year" class="form-select" required>
                                <option value="">-- Select a year --</option>
                                <?php
                                try {
                                    $yearQuery = "SELECT DISTINCT year FROM student_entries WHERE year IS NOT NULL AND year != '' ORDER BY year";
                                    $yearResult = $conn->query($yearQuery);

                                    if ($yearResult) {
                                        while ($row = $yearResult->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = (isset($_GET['year']) && $_GET['year'] == $row['year']) ? 'selected' : '';
                                            echo '<option value="' . htmlspecialchars($row['year']) . '" ' . $selected . '>' . htmlspecialchars($row['year']) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="">No years available</option>';
                                    }
                                } catch (PDOException $e) {
                                    echo '<option value="">Error loading years</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="class_list" class="form-label">
                                <i class="fas fa-chalkboard mr-1"></i> Select Class
                            </label>
                            <select name="class" id="class_list" class="form-select" required>
                                <option value="">-- Select a class --</option>
                                <?php
                                try {
                                    $classQuery = "SELECT DISTINCT class FROM student_entries WHERE class IS NOT NULL AND class != '' ORDER BY class";
                                    $classResult = $conn->query($classQuery);

                                    if ($classResult) {
                                        while ($row = $classResult->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = (isset($_GET['class']) && $_GET['class'] == $row['class']) ? 'selected' : '';
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
                            <i class="fas fa-eye mr-1"></i> View Student List
                        </button>
                    </form>

                    <?php if (isset($_GET['year']) && isset($_GET['class'])): ?>
                        <?php
                        $selectedYear = $_GET['year'];
                        $selectedClass = $_GET['class'];

                        try {
                            $studentListQuery = "SELECT id, name FROM student_entries WHERE year = :year AND class = :class ORDER BY name";
                            $stmt = $conn->prepare($studentListQuery);
                            $stmt->bindParam(':year', $selectedYear);
                            $stmt->bindParam(':class', $selectedClass);
                            $stmt->execute();
                            $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $studentsCount = count($studentsList);
                        } catch (PDOException $e) {
                            $error = "Error loading student list: " . $e->getMessage();
                            $studentsCount = 0;
                            $studentsList = [];
                        }
                        ?>

                        <div class="mt-6">
                            <h2 class="section-title">
                                <i class="fas fa-users mr-1"></i>
                                <?= htmlspecialchars($selectedClass) ?> - <?= htmlspecialchars($selectedYear) ?> Students
                            </h2>
                            <span class="badge"><?= $studentsCount ?> student<?= $studentsCount !== 1 ? 's' : '' ?></span>

                            <?php if (isset($error)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h3>Error Loading Data</h3>
                                    <p><?= htmlspecialchars($error) ?></p>
                                </div>
                            <?php elseif ($studentsCount > 0): ?>
                                <ul class="student-list">
                                    <?php foreach ($studentsList as $student): ?>
                                        <li class="student-list-item">
                                            <span><?= htmlspecialchars($student['name']) ?></span>
                                            <a href="?delete_id=<?= htmlspecialchars($student['id']) ?>&year=<?= urlencode($selectedYear) ?>&class=<?= urlencode($selectedClass) ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirmDelete(event)">
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
                const previewElement = document.getElementById('preview-' + studentId);
                if (previewElement) {
                    previewElement.src = e.target.result;
                    showToast('Image selected for ' + studentId, 'success');
                }
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
            return true;
        }

        // Show success toast if redirected from delete action
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('delete_success')) {
            showToast('Student deleted successfully', 'success');
        }

        // Show error message if delete failed
        <?php if (!empty($deleteError)): ?>
            showToast('<?= addslashes($deleteError) ?>', 'error');
        <?php endif; ?>

        // Handle form submission to maintain tab state
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    // Store which tab was active before form submission
                    const activeTab = document.querySelector('.tab.active').getAttribute('data-tab');
                    localStorage.setItem('activeTab', activeTab);
                });
            });

            // Restore active tab on page load
            const savedTab = localStorage.getItem('activeTab');
            if (savedTab && savedTab === 'list') {
                document.querySelector('.tab[data-tab="roster"]').classList.remove('active');
                document.querySelector('.tab[data-tab="list"]').classList.add('active');
                rosterCard.style.display = 'none';
                listCard.style.display = 'block';
            }
            
            // Clear the saved tab state
            localStorage.removeItem('activeTab');
        });
    </script>
</body>
</html>
<?php
// End output buffering and flush
ob_end_flush();
?>
