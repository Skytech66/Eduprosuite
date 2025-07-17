<?php
session_start();
require 'config.php'; // Database connection

// Check if teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

$assigned_class = $_SESSION['assigned_class'];

// Fetch all students and their ratings data for the assigned class
$stmt = $conn->prepare("
    SELECT s.name AS student_name, 
           COALESCE(SUM(b.critical_thinking), 0) AS total_critical_thinking,
           COALESCE(SUM(b.logical_reasoning), 0) AS total_logical_reasoning,
           COALESCE(SUM(b.collaboration), 0) AS total_collaboration,
           COALESCE(SUM(b.creativity), 0) AS total_creativity,
           COALESCE(SUM(b.communication), 0) AS total_communication
    FROM students s
    LEFT JOIN behaviour b ON s.name = b.student_name
    WHERE s.class = ?
    GROUP BY s.name
");
$stmt->bind_param("s", $assigned_class);
$stmt->execute();
$result = $stmt->get_result();
$ratings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Ratings Dashboard | Class <?=htmlspecialchars($assigned_class)?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --primary-dark: #3a0ca3;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --info: #4895ef;
            --warning: #f72585;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --border-radius: 0.375rem;
            --border-radius-lg: 0.5rem;
            --border-radius-xl: 1rem;
            --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --box-shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --box-shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: var(--gray-800);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            background: white;
            box-shadow: var(--box-shadow-sm);
            padding: 1.5rem 0;
            position: fixed;
            height: 100vh;
            width: 240px;
            transition: var(--transition);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-600);
            text-decoration: none;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            grid-column: 2;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
        }

        .header h1 i {
            margin-right: 0.75rem;
            color: var(--primary);
        }

        .student-card {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-sm);
        }

        .skill-meter {
            margin-bottom: 1rem;
        }

        .skill-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .skill-name {
            font-weight: 500;
        }

        .skill-value {
            font-weight: 600;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: var(--gray-200);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            background-color: var(--primary);
            transition: width 1s ease-in-out;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-sm);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--gray-600);
            font-size: 1.125rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                grid-column: 1;
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .user-menu {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h3>SkillMetrics</h3>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="ratings.php" class="nav-link active">
                    <i class="fas fa-chart-bar"></i>
                    <span>Student Ratings</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="view_ratings.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Refresh</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="reports.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Reports</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="settings.php" class="nav-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Student Skill Analysis</h1>
            <div class="user-menu">
                <span>Welcome, Teacher</span>
                <div class="user-avatar">T</div>
            </div>
        </div>

        <div class="class-info mb-4">
            <h2 class="mb-2">Class <?=htmlspecialchars($assigned_class)?></h2>
            <p class="text-muted"><?= count($ratings) ?> students</p>
        </div>

        <?php if (!empty($ratings)): ?>
            <div class="dashboard-grid">
                <?php foreach ($ratings as $rating): ?>
                    <div class="student-card">
                        <h3 class="student-name"><?= htmlspecialchars($rating['student_name']) ?></h3>
                        
                        <?php
                        // Calculate percentages and averages
                        $skills = [
                            'Critical Thinking' => $rating['total_critical_thinking'],
                            'Logical Reasoning' => $rating['total_logical_reasoning'],
                            'Collaboration' => $rating['total_collaboration'],
                            'Creativity' => $rating['total_creativity'],
                            'Communication' => $rating['total_communication']
                        ];
                        ?>

                        <?php foreach ($skills as $skill_name => $total_score): ?>
                            <?php
                            $percentage = ($total_score / 35) * 100; // Calculate percentage
                            $average_rating = round($total_score / 7, 1); // Calculate average rating
                            ?>
                            <div class="skill-meter">
                                <div class="skill-info">
                                    <span class="skill-name"><?= $skill_name ?></span>
                                    <span class="skill-value"><?= $average_rating ?>/5</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" data-percent="<?= $percentage ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <p>No student ratings found for this class.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    // Animation for progress bars
    $(document).ready(function() {
        $('.progress-bar').each(function() {
            // Get the target width from the data-percent attribute
            var currentWidth = $(this).data('percent');
            // Set the initial width to 0
            $(this).css('width', '0');
            // Animate to the target width
            $(this).animate({
                width: currentWidth
            }, 1000);
        });
    });
</script>
</body>
</html>
<?php $conn->close(); ?>
