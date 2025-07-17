<?php
session_start();
require 'config.php'; // Include your database connection

// Fetch classes for the dropdown
$class_sql = "SELECT DISTINCT class FROM students";
$class_result = $conn->query($class_sql);

// Fetch weeks for the dropdown
$week_sql = "SELECT DISTINCT week FROM mark_attendance";
$week_result = $conn->query($week_sql);

// Initialize variables for filtering
$selected_class = isset($_POST['class']) ? $_POST['class'] : '';
$selected_week = isset($_POST['week']) ? $_POST['week'] : '';
$search_name = isset($_POST['search_name']) ? $_POST['search_name'] : '';

// Build the query to fetch attendance records based on filters
$attendance_sql = "
    SELECT s.name, s.gender, a.status, a.date, a.week, a.day
    FROM mark_attendance a
    JOIN students s ON a.student_id = s.id
    WHERE 1=1
";

if ($selected_class) {
    $attendance_sql .= " AND s.class = ?";
}
if ($selected_week) {
    $attendance_sql .= " AND a.week = ?";
}
if ($search_name) {
    $attendance_sql .= " AND s.name LIKE ?";
}

// Prepare the statement
$stmt = $conn->prepare($attendance_sql);

// Bind parameters
$params = [];
$types = '';

if ($selected_class) {
    $params[] = $selected_class;
    $types .= 's';
}
if ($selected_week) {
    $params[] = $selected_week;
    $types .= 's';
}
if ($search_name) {
    $params[] = '%' . $search_name . '%';
    $types .= 's';
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$attendance_result = $stmt->get_result();

// Initialize counters and organize data by day
$total_absent = 0;
$total_present = 0;
$total_males = 0;
$total_females = 0;
$total_males_present = 0;
$total_females_present = 0;
$attendance_by_day = [];

// Process attendance records
while ($row = $attendance_result->fetch_assoc()) {
    $day = $row['day'];
    
    // Initialize day array if not exists
    if (!isset($attendance_by_day[$day])) {
        $attendance_by_day[$day] = [
            'records' => [],
            'absent' => 0,
            'present' => 0,
            'males' => 0,
            'females' => 0,
            'males_present' => 0,
            'females_present' => 0
        ];
    }
    
    // Add record to day
    $attendance_by_day[$day]['records'][] = $row;
    
    // Update counters for day
    if ($row['gender'] == 'Male') {
        $attendance_by_day[$day]['males']++;
        $total_males++;
        if ($row['status'] == 'Present') {
            $attendance_by_day[$day]['males_present']++;
            $total_males_present++;
        }
    } elseif ($row['gender'] == 'Female') {
        $attendance_by_day[$day]['females']++;
        $total_females++;
        if ($row['status'] == 'Present') {
            $attendance_by_day[$day]['females_present']++;
            $total_females_present++;
        }
    }

    if ($row['status'] == 'Absent') {
        $attendance_by_day[$day]['absent']++;
        $total_absent++;
    } elseif ($row['status'] == 'Present') {
        $attendance_by_day[$day]['present']++;
        $total_present++;
    }
}

$total_students = $total_males + $total_females;

// Get current day for pagination
$current_day = isset($_GET['day']) ? $_GET['day'] : (count($attendance_by_day) > 0 ? array_key_first($attendance_by_day) : null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance | EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --text-color: #5a5c69;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
            color: var(--text-color);
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.35rem;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .stat-card {
            border-left: 0.25rem solid var(--primary-color);
            border-radius: 0.35rem;
        }
        
        .stat-card.success {
            border-left-color: var(--success-color);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger-color);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning-color);
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            font-weight: 600;
            color: #b7b9cc;
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            font-size: 0.85rem;
        }
        
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #b7b9cc;
            border-top: none;
        }
        
        .present-badge {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }
        
        .absent-badge {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        .filter-section {
            background-color: white;
            border-radius: 0.35rem;
            padding: 1.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .export-btn-group {
            margin-left: auto;
        }
        
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .export-btn-group {
                margin-left: 0;
                margin-top: 1rem;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h2 class="page-title mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Dashboard</h2>
            <div class="export-btn-group btn-group">
                <button class="btn btn-outline-primary" onclick="exportToExcel()">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export
                </button>
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <div class="filter-section">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="class" class="form-label">Class</label>
                        <select name="class" id="class" class="form-select">
                            <option value="">All Classes</option>
                            <?php 
                            // Reset pointer for class results
                            $class_result->data_seek(0);
                            while ($row = $class_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['class']) ?>" <?= $selected_class == $row['class'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['class']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="week" class="form-label">Week</label>
                        <select name="week" id="week" class="form-select">
                            <option value="">All Weeks</option>
                            <?php 
                            // Reset pointer for week results
                            $week_result->data_seek(0);
                            while ($row = $week_result->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['week']) ?>" <?= $selected_week == $row['week'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['week']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search_name" class="form-label">Search Student</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search_name" id="search_name" class="form-control" placeholder="Enter student name" value="<?= htmlspecialchars($search_name) ?>">
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-3">
                <a href="mark_attendance.php" class="btn btn-secondary px-4">
                 <i class="bi bi-arrow-left"></i> Back
                 </a>
    
                <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-funnel me-1"></i> Apply Filters
                  </button>
                </div>

                </div>
            </form>
        </div>

        <?php if ($attendance_result->num_rows > 0): ?>
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label">Total Students</div>
                                <div class="stat-value"><?= $total_students ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card success h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label">Present</div>
                                <div class="stat-value"><?= $total_present ?></div>
                                <div class="text-xs font-weight-bold text-success mt-1">
                                    <?= $total_students > 0 ? round(($total_present/$total_students)*100, 2) : 0 ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card danger h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label">Absent</div>
                                <div class="stat-value"><?= $total_absent ?></div>
                                <div class="text-xs font-weight-bold text-danger mt-1">
                                    <?= $total_students > 0 ? round(($total_absent/$total_students)*100, 2) : 0 ?>%
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-x-circle-fill fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card warning h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="stat-label">Current Week</div>
                                <div class="stat-value"><?= htmlspecialchars($selected_week ?: 'N/A') ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-week-fill fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detailed Attendance</h5>
                <?php if (count($attendance_by_day) > 0): ?>
                <div class="nav nav-pills">
                    <?php foreach ($attendance_by_day as $day => $data): ?>
                        <a class="nav-link <?= $current_day == $day ? 'active' : '' ?>" 
                           href="?<?= http_build_query(array_merge($_GET, ['day' => $day])) ?>">
                            <?= htmlspecialchars($day) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($current_day && isset($attendance_by_day[$current_day])): ?>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Day Summary: <?= htmlspecialchars($current_day) ?></h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tbody>
                                        <tr>
                                            <td><strong>Total Present:</strong></td>
                                            <td><?= $attendance_by_day[$current_day]['present'] ?></td>
                                            <td><strong>Total Absent:</strong></td>
                                            <td><?= $attendance_by_day[$current_day]['absent'] ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Males Present:</strong></td>
                                            <td><?= $attendance_by_day[$current_day]['males_present'] ?></td>
                                            <td><strong>Females Present:</strong></td>
                                            <td><?= $attendance_by_day[$current_day]['females_present'] ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="attendanceTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Week</th>
                                    <th>Day</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_by_day[$current_day]['records'] as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['gender']) ?></td>
                                        <td>
                                            <span class="badge <?= $row['status'] == 'Present' ? 'present-badge' : 'absent-badge' ?>">
                                                <?= htmlspecialchars($row['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['date']) ?></td>
                                        <td><?= htmlspecialchars($row['week']) ?></td>
                                        <td><?= htmlspecialchars($row['day']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3">No attendance data available for the selected filters.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-database-exclamation text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3">No Attendance Records Found</h5>
                <p class="text-muted">Try adjusting your filters to see results.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.19.3/package/dist/xlsx.full.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#attendanceTable').DataTable({
                responsive: true,
                dom: '<"top"f>rt<"bottom"lip><"clear">',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                }
            });
        });
        
        function exportToExcel() {
            // Create a new workbook
            const wb = XLSX.utils.book_new();
            
            // Get the table data
            const table = document.getElementById('attendanceTable');
            const ws = XLSX.utils.table_to_sheet(table);
            
            // Add the worksheet to the workbook
            XLSX.utils.book_append_sheet(wb, ws, "Attendance");
            
            // Generate the Excel file
            XLSX.writeFile(wb, `Attendance_${new Date().toISOString().slice(0,10)}.xlsx`);
        }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>