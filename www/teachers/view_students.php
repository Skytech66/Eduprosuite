<?php
include "../include/functions.php"; // Ensure this file contains necessary functions
include 'config.php'; // Include the database configuration

// PostgreSQL connection
$conn = pg_connect("host={$db_host} dbname={$db_name} user={$db_user} password={$db_pass}");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
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
        /* Add your CSS styles here */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #6366f1;
            --accent: #7c3aed;
            --dark: #111827;
            --light: #f9fafb;
            --gray: #6b7280;
            --light-gray: #f3f4f6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
        }

        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin: 2rem 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 1.5rem 2rem;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--success) 0%, var(--accent) 100%);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 0.5rem;
            font-weight: 400;
        }

        .card-body {
            padding: 2rem;
        }

        @media (max-width: 640px) {
            .card-header {
                padding: 1.25rem 1.5rem;
            }
            
            .card-title {
                font-size: 1.25rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9375rem;
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-sm);
            font-size: 1rem;
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 12px 12px;
            font-weight: 500;
            color: var(--dark);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
            font-weight: 600;
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
            box-shadow: var(--shadow-md);
        }

        .btn-lg {
            padding: 0.875rem 2rem;
            font-size: 1rem;
        }

        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (max-width: 640px) {
            .student-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
        }

        .student-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .student-avatar {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid var(--light-gray);
            background-color: #f8fafc;
        }

        .student-info {
            padding: 1.25rem;
        }

        .student-name {
            font-weight: 700;
            font-size: 1.0625rem;
            margin-bottom: 0.25rem;
            color: var(--dark);
        }

        .student-class {
            color: var(--gray);
            font-size: 0.8125rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .file-input-wrapper {
            position: relative;
            margin-top: 0.75rem;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--light);
            border-radius: var(--border-radius-sm);
            font-size: 0.8125rem;
            cursor: pointer;
            transition: var(--transition);
            border: 1px dashed var(--light-gray);
            font-weight: 500;
            color: var(--gray);
        }

        .file-input-label:hover {
            background-color: #f1f5ff;
            border-color: var(--primary);
            color: var(--primary);
        }

        .file-input {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 50rem;
            background-color: #eef2ff;
            color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1.25rem;
            color: #e5e7eb;
        }

        .empty-state h3 {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .drag-drop-area {
            border: 2px dashed #e5e7eb;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
            transition: var(--transition);
            background-color: #f9fafb;
        }

        .drag-drop-area.highlight {
            border-color: var(--primary);
            background-color: rgba(79, 70, 229, 0.03);
        }

        .drag-drop-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .drag-drop-text {
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.0625rem;
        }

        .drag-drop-subtext {
            color: var(--gray);
            font-size: 0.8125rem;
            margin-bottom: 1.25rem;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--light-gray);
            color: var(--gray);
        }

        .btn-outline:hover {
            background-color: white;
            border-color: var(--primary);
            color: var(--primary);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .hidden-id {
            display: none;
        }

        /* Loading skeleton */
        .skeleton {
            animation: pulse 1.5s ease-in-out infinite;
            background-color: #e5e7eb;
            border-radius: 4px;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Status indicators */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 50rem;
        }
        
        .status-success {
            background-color: #ecfdf5;
            color: var(--success);
        }
        
        .status-warning {
            background-color: #fffbeb;
            color: var(--warning);
        }
        
        .status-error {
            background-color: #fef2f2;
            color: var(--error);
        }

        /* Toast notifications */
        .toast {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            padding: 1rem 1.5rem;
            background-color: var(--dark);
            color: white;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast-success {
            background-color: var(--success);
        }
        
        .toast-error {
            background-color: var(--error);
        }

        /* Progress bar */
        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 3px;
            margin-top: 1rem;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: var(--primary);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Utility classes */
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 0.75rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-5 { margin-top: 1.25rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mt-8 { margin-top: 2rem; }
        
        .text-center { text-align: center; }
        .text-muted { color: var(--gray); }
        
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="card-title">
                            <i class="fas fa-users-class"></i> Class Roster Management
                        </h1>
                        <p class="card-subtitle">View and manage student information and photos</p>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="class" class="form-label">
                            <i class="fas fa-chalkboard"></i> Select Class
                        </label>
                        <select name="class" id="class" class="form-select" required>
                            <option value="">-- Select a class --</option>
                            <?php
                            $classQuery = "SELECT DISTINCT class FROM student WHERE class IS NOT NULL AND class != '' ORDER BY class";
                            $classResult = pg_query($conn, $classQuery);

                            if ($classResult) {
                                while ($row = pg_fetch_assoc($classResult)) {
                                    echo '<option value="' . htmlspecialchars($row['class']) . '">' . htmlspecialchars($row['class']) . '</option>';
                                }
                            } else {
                                echo '<option value="">No classes available</option>';
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
                    $studentQuery = "SELECT id, name, photo FROM student WHERE class = $1 ORDER BY name";
                    $result = pg_query_params($conn, $studentQuery, array($selectedClass));
                    $studentCount = 0;
                    ?>

                    <div class="mt-6">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-user-graduate"></i>
                                <?= htmlspecialchars($selectedClass) ?> Roster
                            </h2>
                            <span class="badge" id="studentCount">0 students</span>
                        </div>

                        <?php if ($result && pg_num_rows($result) > 0): ?>
                            <form action="upload_image.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                                <input type="hidden" name="class" value="<?= htmlspecialchars($selectedClass)                                ?>">

                                <div class="drag-drop-area" id="dragDropArea">
                                    <div class="drag-drop-icon">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </div>
                                    <h3 class="drag-drop-text">Upload Student Photos</h3>
                                    <p class="drag-drop-subtext">Drag & drop images or click to browse</p>
                                    <div class="progress-container" id="progressContainer" style="display: none;">
                                        <div class="progress-bar" id="progressBar"></div>
                                    </div>
                                    <button type="button" class="btn btn-outline mt-4" onclick="document.querySelectorAll('.file-input').forEach(el => el.click())">
                                        <i class="fas fa-folder-open mr-2"></i> Select Files
                                    </button>
                                </div>

                                <div class="student-grid" id="studentGrid">
                                    <?php while ($student = pg_fetch_assoc($result)): ?>
                                        <?php $studentCount++; ?>
                                        <div class="student-card">
                                            <img src="<?= htmlspecialchars($student['photo'] ? $student['photo'] : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'%23e5e7eb\'%3E%3Cpath d=\'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z\'/%3E%3C/svg%3E') ?>"
                                                 alt="<?= htmlspecialchars($student['name']) ?>"
                                                 class="student-avatar"
                                                 id="preview-<?= $student['id'] ?>">
                                            <div class="student-info">
                                                <h3 class="student-name"><?= htmlspecialchars($student['name']) ?></h3>
                                                <p class="student-class">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    <?= htmlspecialchars($selectedClass) ?>
                                                </p>

                                                <div class="file-input-wrapper">
                                                    <label for="file-<?= $student['id'] ?>" class="file-input-label">
                                                        <i class="fas fa-camera mr-2"></i> Update Photo
                                                    </label>
                                                    <input type="file"
                                                           id="file-<?= $student['id'] ?>"
                                                           name="images[]"
                                                           accept="image/*"
                                                           class="file-input"
                                                           data-student-id="<?= $student['id'] ?>"
                                                           onchange="previewImage(this)">
                                                    <input type="hidden" name="iduser[]" value="<?= $student['id'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>

                                <div class="text-center mt-6">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-upload mr-2"></i> Upload Selected Photos
                                    </button>
                                </div>
                            </form>

                            <script>
                                document.getElementById('studentCount').textContent = '<?= $studentCount ?> student' + (<?= $studentCount ?> !== 1 ? 's' : '');
                            </script>
                        <?php else: ?>
                            <div class="empty-state mt-8">
                                <i class="fas fa-user-slash"></i>
                                <h3>No Students Found</h3>
                                <p>There are currently no students registered in this class.</p>
                                <button class="btn btn-outline mt-4" onclick="history.back()">
                                    <i class="fas fa-arrow-left mr-2"></i> Back to Class Selection
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        // JS functions like previewImage, showToast, drag-drop and form submission handlers go here
        function previewImage(input) {
            const studentId = input.getAttribute('data-student-id');
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-' + studentId).src = e.target.result;
            }
            reader.readAsDataURL(file);
        }

        // Additional JavaScript for handling drag-and-drop, toast notifications, etc.
    </script>
</body>
</html>

<?php
pg_close($conn);
?>
