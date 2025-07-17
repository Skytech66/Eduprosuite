<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../require_developer.php"; // Now correct path
require_once "header.php";
?>
$conn = db_conn();

// Fetch average marks for each class
$query = "SELECT class, AVG(Average) AS average_mark FROM marks GROUP BY class ORDER BY class;";
$result = $conn->query($query);

$classes = [];
$average_marks = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $classes[] = $row['class'];
    $average_marks[] = $row['average_mark'];
}

// Fetch total fees paid from the school_fees table
$query_balance = "SELECT SUM(fees_paid) AS total_balance FROM Student_fees;";
$result_balance = $conn->query($query_balance);
$balance_row = $result_balance->fetchArray(SQLITE3_ASSOC);
$school_account_balance = (float)$balance_row['total_balance'];

// Count the number of students admitted each month
$query_admission = "SELECT strftime('%Y-%m', date_of_admission) AS admission_month, COUNT(*) AS student_count FROM students GROUP BY admission_month ORDER BY admission_month;";
$result_admission = $conn->query($query_admission);

$months = [];
$student_counts = [];

while ($row = $result_admission->fetchArray(SQLITE3_ASSOC)) {
    $months[] = $row['admission_month'];
    $student_counts[] = (int)$row['student_count'];
}

// Conversion rate (example)
$conversion_rate = 0.12; // 1 GHS = 0.12 USD
$converted_amount = $school_account_balance * $conversion_rate;

// Initialize total_students, total_teachers, total_females, total_males, and total_employees variables
$total_students = 0;
$total_teachers = 0;
$total_females = 0;
$total_males = 0;
$total_employees = 0;

try {
    // Get total students count for the current month
    $students_result = $conn->query("SELECT COUNT(*) as total_students FROM students");
    $students_row = $students_result->fetchArray(SQLITE3_ASSOC);
    $total_students = (int)$students_row['total_students'];

    // Get total students count for the previous month
    $previous_month_result = $conn->query("SELECT COUNT(*) as total_students FROM students WHERE strftime('%Y-%m', date_of_admission) = strftime('%Y-%m', 'now', '-1 month')");
    $previous_month_row = $previous_month_result->fetchArray(SQLITE3_ASSOC);
    $previous_month_students = (int)$previous_month_row['total_students'];

    // Calculate percentage change
    $percentage_change = 0;
    if ($previous_month_students > 0) {
        $percentage_change = (($total_students - $previous_month_students) / $previous_month_students) * 100;
    } elseif ($total_students > 0) {
        $percentage_change = 100;
    }

    // Determine if the change is positive or negative
    $change_class = $percentage_change >= 0 ? 'change-positive' : 'change-negative';
    $arrow_icon = $percentage_change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
    $percentage_change_display = number_format(abs($percentage_change), 1);

    // Get total teachers count
    $teachers_result = $conn->query("SELECT COUNT(*) as total_teachers FROM employees WHERE position = 'Teacher'");
    $teachers_row = $teachers_result->fetchArray(SQLITE3_ASSOC);
    $total_teachers = (int)$teachers_row['total_teachers'];

    // Get total females count
    $females_result = $conn->query("SELECT COUNT(*) as total_females FROM students WHERE gender = 'Female'");
    $females_row = $females_result->fetchArray(SQLITE3_ASSOC);
    $total_females = (int)$females_row['total_females'];

    // Get total males count
    $males_result = $conn->query("SELECT COUNT(*) as total_males FROM students WHERE gender = 'Male'");
    $males_row = $males_result->fetchArray(SQLITE3_ASSOC);
    $total_males = (int)$males_row['total_males'];
    
    // Get total employees count
    $employees_result = $conn->query("SELECT COUNT(*) as total_employees FROM employees");
    $employees_row = $employees_result->fetchArray(SQLITE3_ASSOC);
    $total_employees = (int)$employees_row['total_employees'];

    // Fetch the most recent attendance date
    $latest_date_query = "SELECT MAX(date) AS latest_date FROM mark_attendance;";
    $latest_date_result = $conn->query($latest_date_query);
    $latest_date_row = $latest_date_result->fetchArray(SQLITE3_ASSOC);
    $latest_date = $latest_date_row['latest_date'];

    if ($latest_date) {
        $latest_date_formatted = date('Y-m-d', strtotime($latest_date));

        $count_present_query = "SELECT COUNT(*) AS present_count FROM mark_attendance WHERE date = :latest_date AND status = 'Present';";
        $stmt = $conn->prepare($count_present_query);
        $stmt->bindValue(':latest_date', $latest_date_formatted, SQLITE3_TEXT);
        $count_present_result = $stmt->execute();
        $count_present_row = $count_present_result->fetchArray(SQLITE3_ASSOC);
        $present_count = (int)$count_present_row['present_count'];
    }
} catch (Exception $e) {
    error_log("Error fetching counts: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPro Suite 2.0 - Admin Dashboard</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- SweetAlert -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #e0e7ff;
            --secondary-color: #3a0ca3;
            --accent-color: #f72585;
            --success-color: #4cc9f0;
            --warning-color: #f8961e;
            --danger-color: #ef233c;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --card-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
            --sidebar-shadow: 4px 0 20px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-radius: 12px;
            --sidebar-width: 280px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-color);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: white;
            color: var(--dark-color);
            transition: var(--transition);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--sidebar-shadow);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-header {
            padding: 25px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            text-align: center;
            color: white;
        }
        
        .sidebar-header img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-header h4 {
            color: white;
            font-weight: 600;
            margin-bottom: 0;
            font-size: 1.2rem;
            font-family: 'Playfair Display', serif;
            letter-spacing: 0.5px;
        }
        
        #sidebar ul.components {
            padding: 15px 0;
        }
        
        #sidebar ul li a {
            padding: 12px 25px;
            font-size: 0.95rem;
            display: block;
            color: var(--gray-color);
            border-left: 4px solid transparent;
            transition: var(--transition);
            text-decoration: none;
            font-weight: 500;
            margin: 5px 10px;
            border-radius: 8px;
        }
        
        #sidebar ul li a:hover {
            color: var(--primary-color);
            background: var(--primary-light);
            border-left: 4px solid var(--primary-color);
            transform: translateX(5px);
        }
        
        #sidebar ul li a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }
        
        #sidebar ul li.active > a {
            background: var(--primary-light);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
            font-weight: 600;
        }
        
        #sidebar .footer {
            padding: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--gray-color);
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        #content {
            width: calc(100% - var(--sidebar-width));
            padding: 30px;
            min-height: 100vh;
            transition: var(--transition);
            position: absolute;
            top: 0;
            right: 0;
            background-color: #f8f9fa;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 15px 0;
        }
        
        .dashboard-title {
            display: flex;
            align-items: center;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--dark-color);
            letter-spacing: -0.5px;
        }
        
        .dashboard-title img {
            height: 50px;
            margin-right: 20px;
        }
        
        .ai-badge {
            background: linear-gradient(135deg, #f72585, #b5179e);
            color: white;
            font-size: 0.75rem;
            padding: 5px 15px;
            border-radius: 20px;
            margin-left: 15px;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(247, 37, 133, 0.3);
        }
        
        .ai-badge i {
            margin-right: 8px;
        }
        
        .admin-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        
        .admin-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 8px;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .admin-role {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--dark-color);
            letter-spacing: 0.5px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .metric-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            border: none;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }
        
        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
            flex-shrink: 0;
        }
        
        .metric-icon.student {
            background: linear-gradient(135deg, #4cc9f0, #4895ef);
            box-shadow: 0 4px 15px rgba(76, 201, 240, 0.3);
        }
        
        .metric-icon.teacher {
            background: linear-gradient(135deg, #f8961e, #f3722c);
            box-shadow: 0 4px 15px rgba(248, 150, 30, 0.3);
        }
        
        .metric-icon.staff {
            background: linear-gradient(135deg, #7209b7, #b5179e);
            box-shadow: 0 4px 15px rgba(114, 9, 183, 0.3);
        }
        
        .metric-icon.attendance {
            background: linear-gradient(135deg, #2ec4b6, #06d6a0);
            box-shadow: 0 4px 15px rgba(46, 196, 182, 0.3);
        }
        
        .metric-icon.gender {
            background: linear-gradient(135deg, #3a86ff, #8338ec);
            box-shadow: 0 4px 15px rgba(58, 134, 255, 0.3);
        }
        
        .metric-icon.balance {
            background: linear-gradient(135deg, #ffbe0b, #fb5607);
            box-shadow: 0 4px 15px rgba(255, 190, 11, 0.3);
        }
        
        .metric-info {
            flex: 1;
        }
        
        .metric-title {
            font-size: 0.9rem;
            color: var(--gray-color);
            margin-bottom: 8px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        
        .metric-value {
            font-family: 'Inter', sans-serif;
            font-size: 1.8rem;
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        
        .metric-change {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .change-positive {
            color: #06d6a0;
        }
        
        .change-negative {
            color: var(--danger-color);
        }
        
        .metric-date {
            font-size: 0.75rem;
            color: var(--gray-color);
            margin-top: 5px;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .chart-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--card-shadow);
            position: relative;
            transition: var(--transition);
        }
        
        .chart-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark-color);
            letter-spacing: 0.3px;
        }
        
        .chart-options {
            font-size: 0.85rem;
            color: var(--gray-color);
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .chart-options:hover {
            color: var(--primary-color);
        }
        
        .chart-wrapper {
            position: relative;
            height: 280px;
            width: 100%;
        }
        
        .double-width {
            grid-column: span 2;
        }
        
        .ai-suggestion {
            font-size: 0.85rem;
            color: var(--gray-color);
            margin-top: 15px;
            padding: 12px 15px;
            background: rgba(67, 97, 238, 0.05);
            border-radius: 8px;
            display: flex;
            align-items: center;
            border-left: 3px solid var(--primary-color);
        }
        
        .ai-suggestion i {
            margin-right: 10px;
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1440px) {
            .double-width {
                grid-column: span 1;
            }
        }
        
        @media (max-width: 1200px) {
            :root {
                --sidebar-width: 240px;
            }
            
            #sidebar ul li a {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
            
            .dashboard-title {
                font-size: 1.5rem;
            }
            
            .metric-card {
                padding: 20px;
            }
            
            .metric-icon {
                width: 50px;
                height: 50px;
                font-size: 1.3rem;
                margin-right: 15px;
            }
            
            .metric-value {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .metrics-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 20px;
            }
            
            .chart-container {
                padding: 20px;
            }
            
            .chart-wrapper {
                height: 240px;
            }
        }
        
        @media (max-width: 768px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
                z-index: 1001;
            }
            
            #sidebar.active {
                margin-left: 0;
            }
            
            #content {
                width: 100%;
                padding: 20px;
            }
            
            #content.active {
                width: 100%;
                transform: translateX(var(--sidebar-width));
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .admin-profile {
                flex-direction: row;
                gap: 15px;
                padding: 8px 15px;
            }
            
            .admin-avatar {
                margin-bottom: 0;
            }
            
            .dashboard-title {
                font-size: 1.4rem;
            }
            
            .ai-badge {
                margin-left: 10px;
                padding: 4px 12px;
            }
        }
        
        @media (max-width: 576px) {
            :root {
                --sidebar-width: 260px;
            }
            
            #content {
                padding: 15px;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                padding: 15px;
            }
            
            .chart-wrapper {
                height: 220px;
            }
            
            .metric-card {
                padding: 18px;
            }
            
            .metric-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                margin-right: 12px;
            }
            
            .metric-value {
                font-size: 1.4rem;
            }
        }
        
        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1002;
            background: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
        
        /* Overlay for mobile menu */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" id="sidebarCollapse">
        <i class="fas fa-bars"></i>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="wrapper">
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="sidebar-header">
                <img src="../images/logo.png" alt="EduPro Logo">
                <h4>EduPro Suite 2.0</h4>
            </div>
            
            <ul class="list-unstyled components">
                <li class="active">
                    <a href="index.php?dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li>
                    <a href="index.php?student_fees"><i class="fas fa-user-graduate"></i> Student Fees</a>
                </li>
                <li>
                    <a href="students.php"><i class="fas fa-users"></i> Student Management</a>
                </li>
                <li>
                    <a onclick="window.location.href='ex.php';" style="cursor: pointer;"><i class="fas fa-file-invoice-dollar"></i> Budget and expenses</a>
                </li>
                <li>
                    <a onclick="window.location.href='qq/admin.php';"><i class="fas fa-clipboard-list"></i> Attendance Records</a>
                </li>
                <li>
                    <a href="index.php?unnamed"><i class="fas fa-check-double"></i> Fees Validator</a>
                </li>
                <li>
                    <a onclick="window.location.href='rec.php';" style="cursor: pointer;"><i class="fas fa-file-invoice-dollar"></i> Expenses</a>
                </li>
                <li>
                    <a href="index.php?sent-messages"><i class="fas fa-comments"></i> Parent Communication</a>
                </li>
                <li>
                    <a href="index.php?admin_pickup"><i class="fas fa-lock"></i> Secure Pickup</a>
                </li>
                <li>
                    <a href="index.php?visitors"><i class="fas fa-user-friends"></i> Visitors Tracking</a>
                </li>
                <li>
                    <a href="index.php?bus_tracking"><i class="fas fa-bus"></i> Bus Tracking</a>
                </li>
                <li>
                    <a href="index.php?emp"><i class="fas fa-chalkboard-teacher"></i> Employees</a>
                </li>
                <li>
                    <a href="index.php?class"><i class="fas fa-chalkboard"></i> Classes</a>
                </li>
                <li>
                    <a href="index.php?subject"><i class="fas fa-book"></i> Subjects</a>
                </li>
                <li>
                    <a href="index.php?exam"><i class="fas fa-pencil-alt"></i> Exams</a>
                </li>
                <li>
                    <a href="#Change_Password" data-toggle="modal" data-target="#Change_Password"><i class="fas fa-user-cog"></i> Profile</a>
                </li>
                <li>
                    <a href="../include/functions.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
                <li>
                    <a href="#"><i class="fas fa-question-circle"></i> Help</a>
                </li>
            </ul>
            
            <div class="footer">
                <p>&copy; <?php echo date("Y"); ?> Swipeware Technologies</p>
            </div>
        </div>

        <!-- Page Content -->
        <div id="content">
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">
                        <img src="../images/adinkra.png" alt="School Logo">
                        ADINKRA INTERNATIONAL SCHOOL
                        <span class="ai-badge">
                            <i class="fas fa-robot"></i> AI Enhanced
                        </span>
                    </h1>
                    
                    <div class="admin-profile">
                        <div class="admin-avatar">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="admin-role">Administrator</div>
                    </div>
                </div>

                <!-- Metrics Cards Section -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon student">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-info">
                            <div class="metric-title">Total Students</div>
                            <div class="metric-value"><?php echo number_format($total_students); ?></div>
                            <div class="metric-change <?php echo $change_class; ?>">
                                <i class="<?php echo $arrow_icon; ?>"></i> <?php echo $percentage_change_display; ?>% from last month
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon teacher">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="metric-info">
                            <div class="metric-title">Total Teachers</div>
                            <div class="metric-value"><?php echo number_format($total_teachers); ?></div>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon staff">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="metric-info">
                            <div class="metric-title">Total Staff</div>
                            <div class="metric-value"><?php echo $total_employees; ?></div>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon attendance">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="metric-info">
                            <div class="metric-title">Today's Attendance</div>
                            <div class="metric-value">
                                <?php echo isset($present_count) ? number_format($present_count) : "N/A"; ?>
                            </div>
                            <div class="metric-date">
                                <?php echo date('l, F j, Y'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon gender">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="metric-info">
                            <div class="metric-title">Gender Distribution</div>
                            <div class="metric-value">
                                <?php echo $total_males; ?>♂ / <?php echo $total_females; ?>♀
                            </div>
                        </div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-icon balance">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="metric-info">
                            <div class="metric-title">School Balance</div>
                            <div class="metric-value">
                                GHS <?php echo number_format($school_account_balance, 2); ?>
                            </div>
                            <div class="metric-change" style="cursor: pointer;" onclick="showConvertedAmount()">
                                <i class="fas fa-exchange-alt"></i> Convert to USD
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Student Admission Trend</div>
                            <div class="chart-options">Monthly <i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="enrollmentChart"></canvas>
                        </div>
                        <div class="ai-suggestion">
                            <i class="fas fa-robot"></i> AI forecasts 210 enrollments for next month
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Attendance Rate</div>
                            <div class="chart-options">Weekly <i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                        <div class="ai-suggestion">
                            <i class="fas fa-robot"></i> AI detected Wednesday as low attendance day
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Gender Distribution</div>
                            <div class="chart-options">Current Term <i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="genderChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Performance Trend</div>
                            <div class="chart-options">Current Term <i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-container double-width">
                        <div class="chart-header">
                            <div class="chart-title">Class Performance Analysis</div>
                            <div class="chart-options">By Examination <i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="classPerformanceChart"></canvas>
                        </div>
                        <div class="ai-suggestion">
                            <i class="fas fa-robot"></i> AI identifies Science as needing curriculum adjustments
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Admission Growth</div>
                            <div class="chart-options">Current Term <i class="fas fa-chevron-down"></i></div>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="admissionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    
    <script>
        // Sidebar toggle functionality
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $('#sidebarOverlay').toggleClass('active');
                $('body').toggleClass('sidebar-open');
            });
            
            $('#sidebarOverlay').on('click', function() {
                $('#sidebar').removeClass('active');
                $('#sidebarOverlay').removeClass('active');
                $('body').removeClass('sidebar-open');
            });
            
            // Adjust content margin when sidebar is toggled
            if ($(window).width() < 768) {
                $('#sidebar').addClass('active');
                $('#content').addClass('active');
            }
        });
        
        // Enrollment Trend Chart
        const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
        const enrollmentChart = new Chart(enrollmentCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Actual Enrollments',
                    data: <?php echo json_encode($student_counts); ?>,
                    borderColor: '#4361ee',
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#4361ee',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    data: [
                                        {
                    label: 'AI Projection',
                    data: [null, null, null, null, null, null, 231, 210],
                    borderColor: '#f72585',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#f72585',
                    pointBorderColor: '#fff',
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
                        backgroundColor: '#2b2d42',
                        titleFont: {
                            family: 'Inter',
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: 'Inter',
                            size: 12
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
        
        // Attendance Rate Chart
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{
                    label: 'Attendance Rate',
                    data: [96.2, 95.7, 91.8, 95.1, 96.4],
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(239, 71, 111, 0.8)',
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(67, 97, 238, 0.8)'
                    ],
                    borderColor: [
                        'rgba(67, 97, 238, 1)',
                        'rgba(67, 97, 238, 1)',
                        'rgba(239, 71, 111, 1)',
                        'rgba(67, 97, 238, 1)',
                        'rgba(67, 97, 238, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6
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
                                return context.parsed.y + '% attendance';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 90,
                        max: 100,
                        grid: {
                            display: true,
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
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
        
        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [<?php echo $total_males; ?>, <?php echo $total_females; ?>],
                    backgroundColor: ['#3a86ff', '#ff006e'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.raw;
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '75%',
                elements: {
                    arc: {
                        borderWidth: 0
                    }
                }
            }
        });

        // Class Performance Chart
        const classPerformanceCtx = document.getElementById('classPerformanceChart').getContext('2d');
        const classPerformanceChart = new Chart(classPerformanceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($classes); ?>,
                datasets: [{
                    label: 'Average Marks',
                    data: <?php echo json_encode($average_marks); ?>,
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(76, 201, 240, 0.8)',
                        'rgba(114, 9, 183, 0.8)',
                        'rgba(248, 150, 30, 0.8)',
                        'rgba(255, 190, 11, 0.8)',
                        'rgba(239, 71, 111, 0.8)',
                        'rgba(46, 196, 182, 0.8)'
                    ],
                    borderColor: [
                        'rgba(67, 97, 238, 1)',
                        'rgba(76, 201, 240, 1)',
                        'rgba(114, 9, 183, 1)',
                        'rgba(248, 150, 30, 1)',
                        'rgba(255, 190, 11, 1)',
                        'rgba(239, 71, 111, 1)',
                        'rgba(46, 196, 182, 1)'
                    ],
                    borderWidth: 1,
                    borderRadius: 6
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
                                return context.parsed.y.toFixed(1) + ' average score';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            display: true,
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        title: {
                            display: true,
                            text: 'Average Marks',
                            font: {
                                family: 'Inter'
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Classes',
                            font: {
                                family: 'Inter'
                            }
                        }
                    }
                }
            }
        });

        // Admission Growth Chart
        const admissionCtx = document.getElementById('admissionChart').getContext('2d');
        const admissionChart = new Chart(admissionCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Students Admitted',
                    data: <?php echo json_encode($student_counts); ?>,
                    backgroundColor: 'rgba(76, 201, 240, 0.8)',
                    borderColor: 'rgba(76, 201, 240, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false,
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

        // Performance Trend Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],
                datasets: [{
                    label: 'Performance Trend',
                    data: [75, 82, 78, 85, 88],
                    backgroundColor: 'rgba(46, 196, 182, 0.2)',
                    borderColor: '#06d6a0',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#06d6a0',
                    pointBorderColor: '#fff',
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
                                return context.parsed.y + ' average score';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 70,
                        max: 100,
                        grid: {
                            display: true,
                            drawBorder: false,
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

        function showConvertedAmount() {
            var convertedAmount = <?php echo json_encode(number_format($converted_amount, 2)); ?>;
            var conversionRate = <?php echo json_encode($conversion_rate); ?>;
            swal({
                title: "Converted Amount",
                text: "USD " + convertedAmount + "\nConversion Rate: 1 GHS = " + conversionRate + " USD",
                icon: "info",
                button: "Close",
                className: "swal-modal",
                closeModal: true
            });
        }

        // Add animation to metric cards on scroll
        const animateOnScroll = function() {
            const metricCards = document.querySelectorAll('.metric-card');
            
            metricCards.forEach((card, index) => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (cardPosition < screenPosition) {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        };

        // Set initial state for animation
        window.addEventListener('DOMContentLoaded', () => {
            const metricCards = document.querySelectorAll('.metric-card');
            metricCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });
            
            // Trigger first check
            animateOnScroll();
        });

        // Add scroll event listener
        window.addEventListener('scroll', animateOnScroll);

        // Responsive adjustments
        function handleResize() {
            // Adjust chart sizes on resize
            enrollmentChart.resize();
            attendanceChart.resize();
            genderChart.resize();
            classPerformanceChart.resize();
            admissionChart.resize();
            performanceChart.resize();
        }

        window.addEventListener('resize', handleResize);
    </script>
</body>
</html>