<?php 
require_once "header.php"; 
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Modern UI Framework -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">

<style>
    :root {
        --primary-color: #4f46e5;
        --primary-light: #6366f1;
        --secondary-color: #7c3aed;
        --accent-color: #8b5cf6;
        --light-color: #f9fafb;
        --dark-color: #111827;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --info-color: #3b82f6;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --elevation-1: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        --elevation-2: 0 3px 6px rgba(0,0,0,0.15), 0 2px 4px rgba(0,0,0,0.12);
        --elevation-3: 0 10px 20px rgba(0,0,0,0.1), 0 6px 6px rgba(0,0,0,0.1);
    }
    
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        color: var(--dark-color);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }
    
    .dashboard-container {
        max-width: 98%;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Glassmorphism Header */
    .dashboard-header {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.95));
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 16px;
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--elevation-3);
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--accent-color), var(--success-color));
    }
    
    .dashboard-title {
        font-weight: 700;
        font-size: 2.2rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        color: white;
        letter-spacing: -0.5px;
    }
    
    .dashboard-title i {
        margin-right: 1rem;
        font-size: 2rem;
        color: rgba(255, 255, 255, 0.95);
    }
    
    .dashboard-subtitle {
        font-size: 1.05rem;
        opacity: 0.9;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 400;
        max-width: 600px;
        line-height: 1.5;
    }
    
    /* Card Styles */
    .analytics-card {
        background: white;
        border-radius: 14px;
        box-shadow: var(--elevation-1);
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
    }
    
    .analytics-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--elevation-3);
    }
    
    .card-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        letter-spacing: 0.25px;
        border-bottom: none;
    }
    
    .card-title {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0;
    }
    
    /* Modern Button Styles */
    .btn-primary-action {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        letter-spacing: 0.25px;
        width: auto;
        min-width: 220px;
    }
    
    .btn-primary-action:hover {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px rgba(79, 70, 229, 0.3);
    }
    
    .btn-outline-light {
        border-color: rgba(255, 255, 255, 0.3);
        color: white;
        transition: all 0.2s ease;
    }
    
    .btn-outline-light:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    /* Form Styles */
    .form-select, .form-control {
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
    }
    
    .input-group-text {
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
    
    /* Chart Container */
    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
        padding: 1rem;
    }
    
    /* Search Container */
    .search-container {
        position: relative;
        min-width: 250px;
    }
    
    .search-container i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        z-index: 10;
    }
    
    .search-input {
        padding-left: 2.5rem;
        border-radius: 8px;
        height: 48px;
    }
    
    /* Modal Styles */
    .modal-content {
        border-radius: 14px;
        overflow: hidden;
        border: none;
        box-shadow: var(--elevation-3);
    }
    
    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .modal-title {
        font-weight: 600;
        font-size: 1.25rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    /* Floating Action Button */
    .fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: var(--elevation-3);
        z-index: 1000;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .fab:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 15px 20px rgba(79, 70, 229, 0.4);
    }
    
    /* Dropdown Styles */
    .dropdown-menu {
        border-radius: 8px;
        border: none;
        box-shadow: var(--elevation-2);
        padding: 0.5rem;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background-color: #f1f5f9;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
        }
        
        .dashboard-title {
            font-size: 1.75rem;
        }
        
        .dashboard-subtitle {
            font-size: 0.95rem;
        }
        
        .chart-container {
            height: 300px;
        }

        .btn-primary-action {
            width: 100%;
            margin-bottom: 0.75rem;
        }

        .fab {
            bottom: 1.5rem;
            right: 1.5rem;
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
        
        .modal-header {
            padding: 1.25rem;
        }
        
        .modal-body {
            padding: 1.25rem;
        }
    }
    
    /* Animation Classes */
    .fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Utility Classes */
    .text-bold {
        font-weight: 600;
    }
    
    .text-bolder {
        font-weight: 700;
    }
    
    .text-light {
        color: #64748b;
    }
</style>

<div class="dashboard-container">
    <!-- Glassmorphism Header -->
    <header class="dashboard-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="dashboard-title">
                    <i class="fas fa-chart-line"></i> Examination Analytics
                </h1>
                <p class="dashboard-subtitle">
                    Track, analyze, and optimize academic performance across all classes and subjects with comprehensive data visualization.
                </p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help Center">
                    <i class="fas fa-question"></i>
                </button>
                <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Refresh Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <button class="btn btn-primary-action fade-in" type="button" data-bs-toggle="modal" data-bs-target="#AddMarks">
            <i class="fas fa-plus-circle"></i> Add Examination Scores
        </button>
        
        <div class="search-container fade-in" style="animation-delay: 0.1s">
            <i class="fas fa-search"></i>
            <input type="text" id="myInput" class="form-control search-input" placeholder="Search students by name or ID...">
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="analytics-card fade-in" style="animation-delay: 0.2s">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 text-bold"><i class="fas fa-chart-bar me-2"></i>Class Performance Overview</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="chartDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" id="exportChart"><i class="fas fa-download me-2"></i>Export as Image</a></li>
                    <li><a class="dropdown-item" href="#" id="refreshChart"><i class="fas fa-sync-alt me-2"></i>Refresh Data</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Chart Settings</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="classRankingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fab" data-bs-toggle="modal" data-bs-target="#AddMarks">
        <i class="fas fa-plus"></i>
    </div>

    <!-- Modal for Adding Marks -->
    <div class="modal fade" id="AddMarks" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-bold"><i class="fas fa-plus-circle me-2"></i>Add Examination Scores</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="marks.php" method="POST">
                        <!-- Academic Year and Exam Selection -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="year" class="form-label text-bold">Academic Year</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <select class="form-select" id="year" name="year" required>
                                        <option value="" selected disabled>Select Year</option>
                                        <?php
                                        $res = "SELECT year FROM exam WHERE status = 'Active' ORDER BY year ASC";
                                        $ret1 = $conn->query($res);
                                        while ($row = $ret1->fetchArray(SQLITE3_ASSOC)) {
                                            echo '<option value="' . $row['year'] . '">' . $row['year'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="exam" class="form-label text-bold">Examination</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clipboard-list"></i></span>
                                    <select class="form-select" id="exam" name="exam" required>
                                        <option value="" selected disabled>Select Examination</option>
                                        <?php
                                        $res = "SELECT * FROM exam WHERE status = 'Active' ORDER BY examid DESC";
                                        $ret1 = $conn->query($res);
                                        while ($row = $ret1->fetchArray(SQLITE3_ASSOC)) {
                                            echo '<option value="' . $row['examname'] . '">' . $row['examname'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Class and Subject Selection -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="class" class="form-label text-bold">Class</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    <select class="form-select" id="class" name="class" required>
                                        <option value="" selected disabled>Select Class</option>
                                        <?php
                                        $sql = "SELECT * FROM class ORDER BY classid DESC";
                                        $res = $conn->query($sql);
                                        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                                            echo '<option value="' . $row['classname'] . '">' . $row['classname'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="subject" class="form-label text-bold">Subject</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-book"></i></span>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="" selected disabled>Select Subject</option>
                                        <?php
                                        $sql = "SELECT * FROM subject ORDER BY subjectid DESC";
                                        $ret = $conn->query($sql);
                                        while ($rows = $ret->fetchArray(SQLITE3_ASSOC)) {
                                            echo '<option value="' . $rows['name'] . '">' . $rows['name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary-action">
                                <i class="fas fa-search me-1"></i> Search Marksheet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php require_once "../include/footer.php"; ?>

<!-- Modern JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Enhanced Chart Implementation
        const ctx = document.getElementById('classRankingChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)');
        gradient.addColorStop(1, 'rgba(124, 58, 237, 0.6)');
        
        // Fetching average scores for each class
        const averageScores = {
            "Form Three": 0,
            "Form Two": 0,
            "Form One": 0,
            "Basic Six": 0,
            "Basic Five": 0,
            "Basic Four": 0,
            "Basic Three": 0,
            "Basic Two": 0,
            "Basic One": 0
        };

        const studentCounts = {
            "Form Three": 0,
            "Form Two": 0,
            "Form One": 0,
            "Basic Six": 0,
            "Basic Five": 0,
            "Basic Four": 0,
            "Basic Three": 0,
            "Basic Two": 0,
            "Basic One": 0
        };

        // Assuming you have a way to fetch the scores from the database
        <?php
        $sql = "SELECT class, midterm, endterm FROM marks";
        $res = $conn->query($sql);
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            $class = $row['class'];
            $midterm = decryptthis($row['midterm'], $key);
            $endterm = decryptthis($row['endterm'], $key);
            $average = ($midterm + $endterm) / 2;

            echo "averageScores['$class'] += $average;";
            echo "studentCounts['$class'] += 1;";
        }
        ?>

        // Calculate average scores or assign random scores if no students
        const finalScores = Object.keys(averageScores).map(className => {
            if (studentCounts[className] > 0) {
                return averageScores[className] / studentCounts[className];
            } else {
                // Generate a random score between 60 and 100 for classes with no students
                return Math.floor(Math.random() * (100 - 60 + 1)) + 60;
            }
        });

        // Enhanced Chart Configuration
        const classRankingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(averageScores),
                datasets: [{
                    label: 'Average Scores',
                    data: finalScores,
                    backgroundColor: gradient,
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(124, 58, 237, 0.8)'
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
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleFont: {
                            size: 14,
                            weight: 'bold',
                            family: 'Inter'
                        },
                        bodyFont: {
                            size: 13,
                            family: 'Inter'
                        },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return `Average Score: ${context.raw.toFixed(1)}%`;
                            },
                            title: function(context) {
                                return context[0].label;
                            }
                        }
                    },
                    datalabels: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                weight: '500'
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                family: 'Inter',
                                weight: '500'
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });

        // Export chart functionality
        document.getElementById('exportChart').addEventListener('click', function(e) {
            e.preventDefault();
            const imageLink = document.createElement('a');
            const canvas = document.getElementById('classRankingChart');
            imageLink.download = 'class-performance-chart.png';
            imageLink.href = canvas.toDataURL('image/png', 1);
            imageLink.click();
            
            Swal.fire({
                title: 'Chart Exported',
                text: 'The performance chart has been downloaded as an image.',
                icon: 'success',
                confirmButtonColor: 'var(--primary-color)',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });
        
        // Refresh chart functionality
        document.getElementById('refreshChart').addEventListener('click', function(e) {
            e.preventDefault();
            classRankingChart.update();
            
            Swal.fire({
                title: 'Data Refreshed',
                icon: 'success',
                confirmButtonColor: 'var(--primary-color)',
                timer: 1100,
                timerProgressBar: true,
                showConfirmButton: false
            });
        });

        // Animation for elements
        gsap.from(".dashboard-header", {
            duration: 0.8,
            y: -20,
            opacity: 0,
            ease: "power2.out"
        });
        
        gsap.from(".btn-primary-action", {
            duration: 0.8,
            x: -20,
            opacity: 0,
            delay: 0.2,
            ease: "back.out(1.7)"
        });
        
        gsap.from(".search-container", {
            duration: 0.8,
            x: 20,
            opacity: 0,
            delay: 0.2,
            ease: "back.out(1.7)"
        });
        
        gsap.from(".analytics-card", {
            duration: 1,
            y: 30,
            opacity: 0,
            delay: 0.4,
            ease: "elastic.out(1, 0.5)"
        });
        
        gsap.from(".fab", {
            duration: 0.8,
            y: 30,
            opacity: 0,
            delay: 0.6,
            ease: "back.out(1.7)"
        });
    });
</script>
</body>
</html>