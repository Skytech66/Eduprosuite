<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

$teacher_name = $_SESSION['teacher_name'];
$assigned_class = $_SESSION['assigned_class'];

// Fetch total students
$query = $db->prepare("SELECT COUNT(*) AS total FROM studentsandclass WHERE class = ?");
$query->execute([$assigned_class]);
$total_students = $query->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Fetch attendance stats
$query = $db->prepare("SELECT COUNT(*) AS present FROM mark_attendance WHERE class = ? AND date = DATE('now') AND status = 'Present'");
$query->execute([$assigned_class]);
$present_students = $query->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;

$attendance_rate = $total_students > 0 ? round(($present_students / $total_students) * 100, 2) : 0;

// Fetch weekly attendance data for the chart
$weekly_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = $db->prepare("SELECT COUNT(*) AS present FROM mark_attendance WHERE class = ? AND date = ? AND status = 'Present'");
    $query->execute([$assigned_class, $date]);
    $present = $query->fetch(PDO::FETCH_ASSOC)['present'] ?? 0;
    $weekly_data[] = [
        'date' => date('D', strtotime($date)),
        'present' => $present,
        'rate' => $total_students > 0 ? round(($present / $total_students) * 100, 2) : 0
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPro | Class Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #3A57E8; /* Deep professional blue */
            --primary-dark: #2A46C8;
            --secondary: #00C9A7; /* Modern teal */
            --accent: #6C5CE7; /* Purple accent */
            --danger: #FF4757; /* Vibrant red */
            --success: #1DD1A1; /* Vibrant green */
            --light: #F8F9FA;
            --dark: #212529;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F5F7FB;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 0.8rem 1rem;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-left: 8px;
            font-size: 1.2rem;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            color: var(--gray);
            transition: var(--transition);
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            overflow: hidden;
            background: white;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--light-gray);
            font-weight: 600;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-header i {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .stat-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .stat-change {
            display: flex;
            align-items: center;
            font-size: 0.85rem;
            margin-top: auto;
        }
        
        .stat-change.up {
            color: var(--success);
        }
        
        .stat-change.down {
            color: var(--danger);
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .welcome-card h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
        }
        
        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .btn-premium {
            background: white;
            color: var(--primary);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: var(--primary-dark);
        }
        
        .attendance-rate {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: conic-gradient(var(--success) <?php echo $attendance_rate * 3.6; ?>deg, var(--light-gray) 0deg);
            margin: 0 auto 1rem;
            position: relative;
        }
        
        .attendance-rate::before {
            content: '';
            position: absolute;
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 50%;
        }
        
        .attendance-rate span {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark);
            position: relative;
            z-index: 1;
        }
        
        .chart-container {
            position: relative;
            height: 280px;
            width: 100%;
        }
        
        .sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            height: 100%;
        }
        
        .sidebar-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .sidebar-title i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .quick-action {
            display: flex;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--light-gray);
            transition: var(--transition);
        }
        
        .quick-action:last-child {
            border-bottom: none;
        }
        
        .quick-action:hover {
            color: var(--primary);
            cursor: pointer;
        }
        
        .quick-action i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-top: 1px solid var(--light-gray);
            margin-top: auto;
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 0.2rem;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: var(--gray);
        }
        
        .badge-premium {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #000;
            font-weight: 600;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <span>EduPro</span>
                <i class="bi bi-award-fill"></i>
            </a>
            <div class="d-flex align-items-center">
                <span class="badge-premium me-3 d-none d-md-inline">Premium</span>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($teacher_name, 0, 1)); ?>
                        </div>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($teacher_name); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign out</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-3">
                <div class="sidebar mb-4">
                    <h5 class="sidebar-title">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </h5>
                    <div class="quick-action active">
                        <i class="bi bi-house-door"></i>
                        Overview
                    </div>
                    <div class="quick-action">
                        <i class="bi bi-people"></i>
                        My Class
                    </div>
                    <div class="quick-action">
                        <i class="bi bi-calendar-check"></i>
                        Attendance
                    </div>
                    <div class="quick-action">
                        <i class="bi bi-graph-up"></i>
                        Analytics
                    </div>
                    <div class="quick-action">
                        <i class="bi bi-journal-text"></i>
                        Reports
                    </div>
                    <div class="quick-action">
                        <i class="bi bi-megaphone"></i>
                        Announcements
                    </div>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($teacher_name, 0, 1)); ?>
                        </div>
                        <div>
                            <div class="user-name"><?php echo htmlspecialchars($teacher_name); ?></div>
                            <div class="user-role">Class <?php echo htmlspecialchars($assigned_class); ?> Teacher</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="welcome-card">
                    <h2>Welcome back, <?php echo htmlspecialchars($teacher_name); ?>!</h2>
                    <p>Here's what's happening with your class today.</p>
                    <button class="btn-premium" onclick="location.href='mark_attendance.php'">
                        <i class="bi bi-calendar-check me-2"></i>Mark Today's Attendance
                    </button>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-label">Total Students</div>
                                <div class="stat-value"><?php echo $total_students; ?></div>
                                <div class="stat-change up">
                                    <i class="bi bi-arrow-up-short"></i> 2.5% from last week
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-label">Present Today</div>
                                <div class="stat-value"><?php echo $present_students; ?></div>
                                <div class="stat-change <?php echo ($present_students / $total_students) > 0.8 ? 'up' : 'down'; ?>">
                                    <i class="bi bi-<?php echo ($present_students / $total_students) > 0.8 ? 'arrow-up-short' : 'arrow-down-short'; ?>"></i>
                                    <?php echo $attendance_rate; ?>% attendance
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-label">Attendance Rate</div>
                                <div class="attendance-rate">
                                    <span><?php echo $attendance_rate; ?>%</span>
                                </div>
                                <div class="text-center mt-2">
                                    <small class="text-muted">Today's attendance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <span>Weekly Attendance Trend</span>
                                <i class="bi bi-bar-chart-line"></i>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="attendanceTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <span>Attendance Distribution</span>
                                <i class="bi bi-pie-chart"></i>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="attendanceDonutChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <span>Recent Activity</span>
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="user-avatar me-3" style="background: var(--secondary);">
                                        <i class="bi bi-check2-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Attendance Marked</h6>
                                        <p class="text-muted small mb-0">Today's attendance has been recorded</p>
                                        <small class="text-muted"><?php echo date('h:i A'); ?></small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="user-avatar me-3" style="background: var(--accent);">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">New Student Added</h6>
                                        <p class="text-muted small mb-0">John Doe joined your class</p>
                                        <small class="text-muted">Yesterday, 3:45 PM</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <div class="user-avatar me-3" style="background: var(--danger);">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Low Attendance Alert</h6>
                                        <p class="text-muted small mb-0">Wednesday had only 65% attendance</p>
                                        <small class="text-muted">2 days ago</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <span>Upcoming Events</span>
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="me-3 text-center" style="min-width: 40px;">
                                        <div class="fw-bold text-primary">15</div>
                                        <div class="text-muted small">MON</div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Parent-Teacher Meeting</h6>
                                        <p class="text-muted small mb-0">10:00 AM - 12:00 PM</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="me-3 text-center" style="min-width: 40px;">
                                        <div class="fw-bold text-primary">18</div>
                                        <div class="text-muted small">THU</div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">School Sports Day</h6>
                                        <p class="text-muted small mb-0">All day event</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <div class="me-3 text-center" style="min-width: 40px;">
                                        <div class="fw-bold text-primary">22</div>
                                        <div class="text-muted small">MON</div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Monthly Test</h6>
                                        <p class="text-muted small mb-0">Mathematics</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Weekly Attendance Trend Chart
        const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($weekly_data, 'date')); ?>,
                datasets: [{
                    label: 'Attendance Rate',
                    data: <?php echo json_encode(array_column($weekly_data, 'rate')); ?>,
                    backgroundColor: 'rgba(58, 87, 232, 0.1)',
                    borderColor: 'rgba(58, 87, 232, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(58, 87, 232, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.raw + '% attendance';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 50,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Attendance Donut Chart
        const donutCtx = document.getElementById('attendanceDonutChart').getContext('2d');
        const donutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [<?php echo $present_students; ?>, <?php echo $total_students - $present_students; ?>],
                    backgroundColor: [
                        'rgba(29, 209, 161, 0.8)',
                        'rgba(255, 71, 87, 0.8)'
                    ],
                    borderColor: [
                        'rgba(29, 209, 161, 1)',
                        'rgba(255, 71, 87, 1)'
                    ],
                    borderWidth: 1,
                    cutout: '80%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = <?php echo $total_students; ?>;
                                const value = context.raw;
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
