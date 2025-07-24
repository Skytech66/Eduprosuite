<?php 
require_once "header.php"; 
require_once 'config.php'; // Include the database configuration

$year = isset($_POST['year']) ? $_POST['year'] : '';
$exam = isset($_POST['exam']) ? $_POST['exam'] : '';
$class = isset($_POST['class']) ? $_POST['class'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$totalStudents = 0;

// SQL query to fetch students
$sql = "SELECT * from student_entries where year = $1 and class = $2";
$res = pg_query_params($conn, $sql, array($year, $class));

if (!$res) {
    echo "Error executing query: " . pg_last_error($conn);
}

$students = [];
while ($row = pg_fetch_assoc($res)) {
    $students[] = $row;
}

$totalStudents = count($students);
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="marks-dashboard">
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="dashboard-title">
                    <i class="fas fa-clipboard-check"></i> 
                    <?php echo htmlspecialchars($subject); ?> Mark Sheet
                </h1>
                <div class="breadcrumb">
                    <span><?php echo htmlspecialchars($year); ?></span>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($class); ?></span>
                    <i class="fas fa-chevron-right"></i>
                    <span><?php echo htmlspecialchars($exam); ?></span>
                </div>
            </div>
            <div class="header-right">
                <div class="stats-card">
                    <div class="stat-item">
                        <span class="stat-label">Total Students</span>
                        <span class="stat-value"><?php echo $totalStudents; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completion</span>
                        <div class="progress-container">
                            <div class="progress-track">
                                <div class="progress-thumb" id="progressFill" style="width: 0%"></div>
                            </div>
                            <span id="studentCount" class="progress-count">0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="marksForm" action="submit_scores.php" method="POST" enctype="multipart/form-data">
        <div class="action-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search students by name or ID..." onkeyup="searchStudent()" />
                <div class="search-border"></div>
            </div>
            
            <div class="action-buttons">
                <button type="button" id="openModalButton" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Retrieve Student Data
                </button>
                <button type="button" id="analyzePositionsButton" class="btn btn-analytics">
                    <i class="fas fa-chart-pie"></i> Analyze
                </button>
                <button type="button" id="submitMarksButton" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Marks
                </button>
            </div>
        </div>

        <input type="hidden" name="uuser" value="<?php echo $session_id; ?>" />
        <input type="hidden" name="year" value="<?php echo $year; ?>" />
        <input type="hidden" name="exam" value="<?php echo $exam; ?>" />
        <input type="hidden" name="class" value="<?php echo $class; ?>" />
        <input type="hidden" name="subject" value="<?php echo $subject; ?>" />
        
        <div class="data-table-container">
            <div class="table-responsive">
                <table id="pager" class="data-table">
                    <thead>
                        <tr>
                            <th class="col-serial">#</th>
                            <th class="col-student">Student</th>
                            <th class="col-id">Student ID</th>
                            <th class="col-score">Class Score <span>(50 max)</span></th>
                            <th class="col-score">Exam Score <span>(50 max)</span></th>
                            <th class="col-position">Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalStudents === 0): ?>
                            <tr class="no-data">
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-graduate"></i>
                                        <h3>No Students Found</h3>
                                        <p>No students match the selected criteria</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $index => $row): ?>
                            <tr class="student-row">
                                <td class="col-serial"><?php echo $index + 1; ?></td>
                                <td class="col-student">
                                    <div class="student-info">
                                        <input type="hidden" name="jina[]" value="<?php echo htmlspecialchars($row['name']); ?>" />
                                        <div class="student-avatar" style="background-color: <?php echo generateColor($row['name']); ?>">
                                            <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                        </div>
                                        <div class="student-details">
                                            <span class="student-name"><?php echo htmlspecialchars($row['name']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-id">
                                    <input type="hidden" name="regno[]" value="<?php echo htmlspecialchars($row['admission_number']); ?>" />
                                    <span class="student-id"><?php echo htmlspecialchars($row['admission_number']); ?></span>
                                </td>
                                <td class="col-score">
                                    <div class="score-input-container">
                                        <input type="number" class="score-input" name="midterm[]" max="50" placeholder="0-50" />
                                        <div class="input-state"></div>
                                    </div>
                                </td>
                                <td class="col-score">
                                    <div class="score-input-container">
                                        <input type="number" class="score-input" name="endterm[]" max="50" placeholder="0-50" />
                                        <div class="input-state"></div>
                                    </div>
                                </td>
                                <td class="col-position">
                                    <div class="position-container">
                                        <input type="number" class="position-input" name="position[]" min="1" readonly />
                                        <span class="ordinal-suffix"></span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<!-- Modal for retrieving student data -->
<div id="dataModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Retrieve Student Data</h2>
        <form id="retrieveForm">
            <label for="yearSelect">Year:</label>
            <select id="yearSelect" name="year">
                <option value="">Select Year</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
                <!-- Add more years as needed -->
            </select>

            <label for="classSelect">Class:</label>
            <select id="classSelect" name="class">
                <option value="">Select Class</option>
                <option value="Class 1">Class 1</option>
                <option value="Class 2">Class 2</option>
                <!-- Add more classes as needed -->
            </select>

            <button type="button" id="fetchDataButton">Fetch Data</button>
        </form>
        <div id="studentDataContainer"></div>
    </div>
</div>

<?php 
// Helper function to generate consistent color from name
function generateColor($name) {
    $colors = ['#4361ee', '#3f37c9', '#4895ef', '#4cc9f0', '#560bad', '#b5179e', '#f72585', '#7209b7'];
    $hash = crc32($name) % count($colors);
    return $colors[$hash];
}
?>

<?php require_once "../include/footer.php"; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced mark input validation
    function validateMark(input) {
        const value = parseFloat(input.value) || 0;
        const max = parseFloat(input.max) || 50;
        const container = input.closest('.score-input-container');
        
        // Reset all classes
        container.classList.remove('valid', 'warning', 'error', 'empty');
        
        if (input.value === "") {
            container.classList.add('empty');
        } else if (value > max) {
            container.classList.add('warning');
        } else if (value < 0) {
            container.classList.add('error');
        } else {
            container.classList.add('valid');
        }
        
        updateProgress();
    }

    // Progress tracking
    function updateProgress() {
        const inputs = document.querySelectorAll('.score-input');
        let filled = 0;
        
        inputs.forEach(input => {
            if (input.value !== "") filled++;
        });
        
        const total = inputs.length;
        const percentage = total > 0 ? Math.round((filled / total) * 100) : 0;
        
        document.getElementById('progressFill').style.width = `${percentage}%`;
        document.getElementById('studentCount').textContent = `${percentage}%`;
        
        // Update progress bar color based on completion
        const progressFill = document.getElementById('progressFill');
        progressFill.classList.remove('low', 'medium', 'high');
        
        if (percentage < 30) {
            progressFill.classList.add('low');
        } else if (percentage < 70) {
            progressFill.classList.add('medium');
        } else {
            progressFill.classList.add('high');
        }
    }

    // Attach validation to all mark inputs
    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', function() {
            validateMark(this);
        });
        
        // Initial validation
        validateMark(input);
    });

    // Enhanced search functionality
    window.searchStudent = function() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');
        
        rows.forEach(row => {
            const name = row.querySelector('.student-name').textContent.toLowerCase();
            const id = row.querySelector('.student-id').textContent.toLowerCase();
            
            if (name.includes(filter) || id.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    };

    // Improved keyboard navigation
    document.querySelectorAll('.score-input, .position-input').forEach(input => {
        input.addEventListener('keydown', function(e) {
            const row = this.closest('tr');
            const allInputs = Array.from(document.querySelectorAll('.score-input, .position-input'));
            const currentIndex = allInputs.indexOf(this);
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (currentIndex < allInputs.length - 1) allInputs[currentIndex + 1].focus();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    if (currentIndex > 0) allInputs[currentIndex - 1].focus();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    const nextInRow = row.querySelectorAll('.score-input, .position-input');
                    const rowIndex = Array.from(nextInRow).indexOf(this);
                    if (rowIndex < nextInRow.length - 1) nextInRow[rowIndex + 1].focus();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    const prevInRow = row.querySelectorAll('.score-input, .position-input');
                    const prevRowIndex = Array.from(prevInRow).indexOf(this);
                    if (prevRowIndex > 0) prevInRow[prevRowIndex - 1].focus();
                    break;
            }
        });
    });

    // Submit Marks Button
    document.getElementById('submitMarksButton').addEventListener('click', function() {
        const emptyInputs = Array.from(document.querySelectorAll('.score-input')).filter(i => i.value === "");
        
        if (emptyInputs.length > 0) {
            if (confirm(`${emptyInputs.length} marks are empty. Submit anyway?`)) {
                document.getElementById('marksForm').submit();
            }
        } else {
            document.getElementById('marksForm').submit();
        }
    });

    // Analyze Positions Button
    document.getElementById('analyzePositionsButton').addEventListener('click', function() {
        const rows = document.querySelectorAll('.student-row:not(.no-data)');
        const scores = [];
        
        rows.forEach(row => {
            const admno = row.querySelector('.col-id input').value;
            const midterm = parseFloat(row.querySelector('input[name="midterm[]"]').value) || 0;
            const endterm = parseFloat(row.querySelector('input[name="endterm[]"]').value) || 0;
            
            scores.push({ admno, total: midterm + endterm });
        });
        
        // Sort by total score descending
        scores.sort((a, b) => b.total - a.total);
        
        // Assign positions (handling ties)
        let currentPosition = 1;
        scores.forEach((score, index) => {
            if (index > 0 && score.total < scores[index - 1].total) {
                currentPosition = index + 1;
            }
            
            // Update the position in the UI
            const row = Array.from(rows).find(r => 
                r.querySelector('.col-id input').value === score.admno
            );
            
            if (row) {
                const positionInput = row.querySelector('.position-input');
                const ordinalSpan = row.querySelector('.ordinal-suffix');
                
                positionInput.value = currentPosition;
                ordinalSpan.textContent = getOrdinalSuffix(currentPosition);
            }
        });
        
        // Visual feedback
        const btn = this;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> Analysis Complete';
        btn.classList.add('success');
        
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-chart-pie"></i> Analyze';
            btn.classList.remove('success');
        }, 2000);
    });
    
    // Helper function for ordinal suffixes
    function getOrdinalSuffix(num) {
        const j = num % 10, k = num % 100;
        if (j == 1 && k != 11) return 'st';
        if (j == 2 && k != 12) return 'nd';
        if (j == 3 && k != 13) return 'rd';
        return 'th';
    }

    // Modal functionality
    const modal = document.getElementById('dataModal');
    const openModalButton = document.getElementById('openModalButton');
    const closeButton = document.querySelector('.close-button');

    // Open the modal
    openModalButton.onclick = function() {
        modal.style.display = 'block';
    }

    // Close the modal
    closeButton.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Fetch student data
    document.getElementById('fetchDataButton').onclick = function() {
        const year = document.getElementById('yearSelect').value;
        const className = document.getElementById('classSelect').value;

        if (year && className) {
            fetch('retrieve_student_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `year=${year}&class=${className}`
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('studentDataContainer');
                container.innerHTML = ''; // Clear previous data

                if (data.length > 0) {
                    data.forEach(student => {
                        const studentDiv = document.createElement('div');
                        studentDiv.textContent = `Name: ${student.name}, ID: ${student.admission_number}`;
                        container.appendChild(studentDiv);
                    });
                } else {
                    container.textContent = 'No students found.';
                }
            })
            .catch(error => console.error('Error fetching data:', error));
        } else {
            alert('Please select both year and class.');
        }
    }
});
</script>

<style>
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
}

.close-button {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close-button:hover,
.close-button:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
