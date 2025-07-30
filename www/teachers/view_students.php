<?php
// Database configuration
$host = "dpg-d20bls6mcj7s73avna10-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "school_523q";
$user = "school_523q_user";
$password = "05A4cQnogC1qETghafnFsKNYUxYIRwrv";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

include "../include/functions.php"; // Ensure this file contains necessary functions
include 'config.php'; // Include the database configuration
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | Class Roster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS styles */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #6366f1;
            --accent: #7c3aed;
            --dark: rgb(12, 18, 29);
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

        .dashboard {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            justify-content: space-between;
            gap: 2rem;
        }

        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin: 2rem 0;
            border: 1px solid rgba(0, 0, 0, 0.05);
            flex: 1 1 300px; /* Allow cards to grow and shrink */
            min-width: 300px; /* Minimum width for cards */
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 1.5rem 2rem;
            position: relative;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 2rem;
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

        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .student-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
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

        @media (max-width: 768px) {
            .card-header {
                padding: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            .btn {
                width: 100%; /* Full width buttons on mobile */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard">
            <div class="dashboard-card">
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
                                <i class="fas fa-chalkboard"></i> Select Class
                            </label>
                            <select name="class" id="class" class="form-select" required>
                                <option value="">-- Select a class --</option>
                                <?php
                                // Fetch distinct classes from the marks table
                                $classQuery = "SELECT DISTINCT class FROM marks WHERE class IS NOT NULL AND class != '' ORDER BY class";
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
                            <i class="fas fa-search mr-1"></i> View Class Roster
                        </button>
                    </form>
                    
                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class'])): ?>
                        <?php
                        $selectedClass = $_POST['class'];
                        // Fetch students from the marks table based on the selected class
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
                            <span class="badge" id="studentCount"><?= $studentCount ?> student<?= $studentCount !== 1 ? 's' : '' ?></span>

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
                                                            <i class="fas fa-camera mr-2"></i> Update Photo
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
                                            <i class="fas fa-upload mr-2"></i> Upload Selected Photos
                                        </button>
                                    </div>
                                </form>
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

            <!-- New View Student List Section -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-list"></i> View Student List
                    </h1>
                    <p class="card-subtitle">Select year and class to view students</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="year" class="form-label">Select Year</label>
                            <select name="year" id="year" class="form-select" required>
                                <option value="">-- Select a year --</option>
                                <?php
                                // Fetch distinct years from the student_entries table
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
                            <label for="class" class="form-label">Select Class</label>
                            <select name="class" id="class" class="form-select" required>
                                <option value="">-- Select a class --</option>
                                <?php
                                // Fetch distinct classes from the student_entries table
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

                        // Fetch students from the student_entries table based on the selected year and class
                        $studentListQuery = "SELECT name FROM student_entries WHERE year = :year AND class = :class ORDER BY name";
                        $stmt = $conn->prepare($studentListQuery);
                        $stmt->bindParam(':year', $selectedYear);
                        $stmt->bindParam(':class', $selectedClass);
                        $stmt->execute();
                        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $studentsCount = count($studentsList);
                        ?>

                        <div class="mt-6">
                            <h2 class="section-title">
                                <i class="fas fa-user-graduate"></i>
                                <?= htmlspecialchars($selectedClass) ?> - <?= htmlspecialchars($selectedYear) ?> Student List
                            </h2>
                            <span class="badge" id="studentCount"><?= $studentsCount ?> student<?= $studentsCount !== 1 ? 's' : '' ?></span>

                            <?php if ($studentsCount > 0): ?>
                                <ul>
                                    <?php foreach ($studentsList as $student): ?>
                                        <li><?= htmlspecialchars($student['name']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <div class="empty-state mt-8">
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
    </div>

    <div id="toast" class="toast"></div>

    <script>
        function previewImage(input) {
            const studentId = input.getAttribute('data-student-id');
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('preview-' + studentId).src = e.target.result;
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
