<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTrack | Class Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #3B82F6;
            --surface: #F8FAFC;
            --border: #E2E8F0;
        }
        body {
            font-family: 'Inter', system-ui;
            background-color: var(--surface);
        }
        .sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--border);
        }
        .attendance-card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .chart-container {
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar vh-100 p-3">
            <div class="d-flex align-items-center gap-3 mb-5">
                <i class="bi bi-journal-check fs-4 text-primary"></i>
                <h5 class="mb-0 fw-semibold">EduTrack</h5>
            </div>
            <nav class="nav flex-column gap-2">
                <a href="#" class="nav-link active bg-light rounded">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="#" class="nav-link text-dark">
                    <i class="bi bi-people me-2"></i> Classes
                </a>
                <a href="#" class="nav-link text-dark">
                    <i class="bi bi-bar-chart me-2"></i> Analytics
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <nav class="nav-header d-flex justify-content-between align-items-center p-3 bg-white">
                <div class="d-flex gap-3 align-items-center">
                    <button class="btn btn-sm btn-light">
                        <i class="bi bi-calendar-week"></i> Week 5
                    </button>
                    <!-- Mark Attendance Button -->
                    <button class="btn btn-sm btn-primary" id="markAttendance">
                        <i class="bi bi-check-circle"></i> Mark Today's Attendance
                    </button>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light">
                            <i class="bi bi-bell"></i>
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light d-flex align-items-center">
                            <div class="me-2">John Doe</div>
                            <i class="bi bi-person-circle"></i>
                        </button>
                    </div>
                </div>
            </nav>

            <main class="container-fluid p-4">
                <!-- Summary Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Class Overview</h4>
                        <h6 class="mb-0 text-muted">Grade 10A - Mathematics</h6>
                    </div>
                    <div class="col-md-3">
                        <div class="attendance-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Total Students</div>
                                    <div class="h4 mb-0" id="totalStudents">32</div>
                                </div>
                                <i class="bi bi-people fs-4 text-primary"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="attendance-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Attendance Rate</div>
                                    <div class="h4 mb-0 text-success" id="attendanceRate">94%</div>
                                </div>
                                <i class="bi bi-check2-circle fs-4 text-success"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="attendance-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Absent Today</div>
                                    <div class="h4 mb-0 text-danger" id="absentToday">2</div>
                                </div>
                                <i class="bi bi-x-circle fs-4 text-danger"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="attendance-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small">Late Arrivals</div>
                                    <div class="h4 mb-0 text-warning" id="lateArrivals">3</div>
                                </div>
                                <i class="bi bi-clock-history fs-4 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div class="row g-4 mb-4">
                    <div class="col-md-8">
                        <div class="attendance-card p-3">
                            <h6 class="mb-3">Weekly Attendance Trend</h6>
                            <div class="chart-container">
                                <canvas id="attendanceTrend"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="attendance-card p-3">
                            <h6 class="mb-3">Status Distribution</h6>
                            <div class="chart-container">
                                <canvas id="statusDistribution"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Single Class Data
        const currentClass = {
            students: 32,
            attendance: 94,
            absent: 2,
            late: 3,
            trend: [85, 88, 92, 90, 94],
            distribution: [75, 5, 12, 8],
            teacher: "Mr. Smith"
        };

        // Chart Instances
        let trendChart, distChart;

        // Initialize Charts
        function initCharts() {
            // Attendance Trend Chart
            const trendCtx = document.getElementById('attendanceTrend').getContext('2d');
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                    datasets: [{
                        label: 'Attendance Rate',
                        data: currentClass.trend,
                        borderColor: '#3B82F6',
                        tension: 0.4,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });

            // Status Distribution Chart
            const distCtx = document.getElementById('statusDistribution').getContext('2d');
            distChart = new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Present', 'Absent', 'Late', 'Excused'],
                    datasets: [{
                        data: currentClass.distribution,
                        backgroundColor: ['#16A34A', '#DC2626', '#D97706', '#7C3AED']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Update Dashboard Data
        function updateDashboard() {
            // Update Summary Cards
            document.getElementById('totalStudents').textContent = currentClass.students;
            document.getElementById('attendanceRate').textContent = `${currentClass.attendance}%`;
            document.getElementById('absentToday').textContent = currentClass.absent;
            document.getElementById('lateArrivals').textContent = currentClass.late;
        }

        // Event Listeners
        document.getElementById('markAttendance').addEventListener('click', () => {
            // Redirect to attendancemark.php
            window.location.href = '';
        });

        // Initialization
        document.addEventListener('DOMContentLoaded', () => {
            initCharts();
            updateDashboard();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>