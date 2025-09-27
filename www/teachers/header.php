<?php
require_once "../include/functions.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_SESSION["id"] ?? '';

if ($session_id == "") {
    header("Location: ../index.php?error=Invalid username or password");
    exit();
}

$conn = db_conn();
?>
<!doctype html>
<html lang="en">
<head>
    <title>EduPro Suite 2.0</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <link rel="stylesheet" href="../include/css/style.css">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        #sidebar {
            transition: transform 0.3s ease;
        }

        .sidebar-hidden #sidebar {
            transform: translateX(-100%);
        }

        #sidebarToggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 10000;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
        }

        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            background-image: url('logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div id="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<!-- Sidebar Toggle Button -->
<button id="sidebarToggle"><i class="fas fa-bars"></i></button>

<div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
        <div class="p-4 pt-5">
            <a href="#" data-toggle="modal" data-target="#aboutModal" class="circle mb-4" style="background-image: url('log.jpg'); width: 100px; height: 100px; display: block; background-size: cover; border-radius: 50%; margin: 0 auto;"></a>
            
            <!-- Updated Title -->
            <h4 class="text-center font-weight-bold text-light mt-2" style="font-size: 1.4rem;">EduPro Suite <span style="color: #ffc107;">2.0</span></h4>
            
            <ul class="list-unstyled components mb-5">
                <li class="active"><a href="index.php?dashboard" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="index.php?exams" class="nav-link"><i class="fas fa-file-alt"></i> Exams</a></li>
                <li><a href="exam_scores.php?exam_scores" class="nav-link"><i class="fas fa-chart-line"></i> Exam Scores</a></li>
                <li><a href="index.php?lesson_notes" class="nav-link"><i class="fas fa-book"></i> Lesson Notes</a></li>
                <li><a href="index.php?subjects" class="nav-link"><i class="fas fa-book-open"></i> Subjects</a></li>
                <li><a href="index.php?form" class="nav-link"><i class="fas fa-user-plus"></i> Add Students</a></li>
                <li><a href="index.php?view_students" class="nav-link"><i class="fas fa-eye"></i> View Students & Add Passport Photo</a></li>
                <li><a href="index.php?login" class="nav-link"><i class="fas fa-user-check"></i> Attendance Register</a></li>
                <li><a href="index.php?lo" class="nav-link"><i class="fas fa-brain"></i> Skill & Behaviour Management</a></li>
                <li><a href="index.php?logg" class="nav-link"><i class="fas fa-tasks"></i> Assignments & lessons</a></li>
                <li><a href="index.php?email_login" class="nav-link"><i class="fas fa-envelope"></i> Emails</a></li>
                <li><a href="index.php?messages" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li>
                    <a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to logout?');">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
                <li>
                    <a href="#pageSubmenu" class="nav-link dropdown-toggle" data-toggle="collapse" aria-expanded="false"><i class="fas fa-file-invoice"></i> Generate Reports</a>
                    <ul class="collapse list-unstyled" id="pageSubmenu">
                        <li>
                            <a href="#Generate_Report_Cards" class="nav-link" data-toggle="modal" data-target="#Generate_Report_Cards"><i class="fas fa-file-alt"></i> Students Report</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <div class="footer">
                <p class="text-light text-center">&copy;<script>document.write(new Date().getFullYear());</script> powered by <a href="https://me.co.ke" target="_blank" class="text-warning">Swipeware tech.</a></p>
            </div>
        </div>
    </nav>

    <div id="content" class="content-container">
        <?php
        if (isset($_GET['dashboard'])) {
            include('dashboard.php');
        } elseif (isset($_GET['exams'])) {
            include('exams.php');
        } elseif (isset($_GET['exam_scores'])) {
            include('exam_scores.php');
        } elseif (isset($_GET['lesson_notes'])) {
            include('lesson_notes.php');
        } elseif (isset($_GET['subjects'])) {
            include('subjects.php');
        } elseif (isset($_GET['form'])) {
            include('add_students.php');
        } elseif (isset($_GET['login'])) {
            include('attendance.php');
        } elseif (isset($_GET['lo'])) {
            include('skills_behavior.php');
        } elseif (isset($_GET['logg'])) {
            include('assignments.php');
        } elseif (isset($_GET['email_login'])) {
            include('email.php');
        } elseif (isset($_GET['messages'])) {
            include('messages.php');
        } else {
            include('dashboard.php');
        }
        ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/v/bs-3.3.7/jq-2.2.4/dt-1.10.15/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="../include/js/functions.js"></script>

<script>
    // Sidebar toggle
    document.getElementById("sidebarToggle").addEventListener("click", function() {
        document.querySelector(".wrapper").classList.toggle("sidebar-hidden");
    });

    // Loading Overlay
    $(document).ready(function() {
        function showLoadingOverlay() {
            const overlay = $('#loading-overlay');
            overlay.addClass('active');
            setTimeout(function() {
                overlay.removeClass('active');
            }, 1000);
        }

        $('a.nav-link').on('click', function(e) {
            const href = $(this).attr('href');
            const targetUrls = [
                'index.php?exams', 'exam_scores.php?exam_scores',
                'index.php?lesson_notes', 'index.php?subjects',
                'index.php?form', 'index.php?view_students',
                'index.php?login', 'index.php?lo', 'index.php?logg'
            ];

            const isTargetUrl = targetUrls.some(url => href.includes(url));
            const isExternalLink = href.startsWith('http') || href.startsWith('#');

            if (!isExternalLink && isTargetUrl) {
                e.preventDefault();
                showLoadingOverlay();
                setTimeout(function() {
                    window.location.href = href;
                }, 1000);
            }
        });

        $(window).on('load', function() {
            $('#loading-overlay').removeClass('active');
        });
    });
</script>
</body>
</html>

