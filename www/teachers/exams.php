<?php 
require_once "header.php"; 
require_once "config.php"; // Include the database configuration

// Database connection parameters
$host = "dpg-d20bls6mcj7s73avna10-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "school_523q";
$user = "school_523q_user";
$password = "05A4cQnogC1qETghafnFsKNYUxYIRwrv";

// Establishing the connection
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

$year = isset($_POST['year']) ? $_POST['year'] : '';
$exam = isset($_POST['exam']) ? $_POST['exam'] : '';
$class = isset($_POST['class']) ? $_POST['class'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
$totalStudents = 0;

// Updated SQL query to fetch students with casting
$sql = "SELECT * FROM student_entries WHERE year::text ILIKE $1 AND class ILIKE $2";
$result = pg_prepare($conn, "fetch_students", $sql);

if (!$result) {
    die("Error preparing statement: " . pg_last_error($conn));
}

$res = pg_execute($conn, "fetch_students", array($year, $class));

if (!$res) {
    echo "Error executing query: " . pg_last_error($conn);
    exit; // Exit if the query fails
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

    <div class="action-bar">
        <button type="button" id="fetchStudentDataButton" class="btn btn-primary">
            <i class="fas fa-user-graduate"></i> Fetch Student Data
        </button>
    </div>

    <form id="marksForm" action="submit_scores.php" method="POST" enctype="multipart/form-data">
        <div class="action-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search students by name or ID..." onkeyup="searchStudent()" />
                <div class="search-border"></div>
            </div>
            
            <div class="action-buttons">
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

<!-- Modal for selecting year, class, and subject -->
<div id="studentDataModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Select Criteria</h2>
        
        <div class="form-group">
            <label for="yearSelect">Select Year:</label>
            <select id="yearSelect">
                <option value="">Select Year</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <!-- Add more years as needed -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="classSelect">Select Class:</label>
            <select id="classSelect">
                <option value="">Select Class</option>
                <option value="Class 1">Class 1</option>
                <option value="Class 2">Class 2</option>
                <option value="Class 3">Class 3</option>
                <!-- Add more classes as needed -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="subjectSelect">Select Subject:</label>
            <select id="subjectSelect">
                <option value="">Select Subject</option>
                <option value="Math">Math</option>
                <option value="Science">Science</option>
                <option value="English">English</option>
                <!-- Add more subjects as needed -->
            </select>
        </div>
        
        <button id="searchMarksButton" class="btn btn-primary">Search Marksheet</button>
        
        <div id="studentDetails" style="margin-top: 20px;"></div>
    </div>
</div>

<?php 
// Helper function to generate consistent color from name
if (!function_exists('generateColor')) {
    function generateColor($name) {
        $colors = ['#4361ee', '#3f37c9', '#4895ef', '#4cc9f0', '#560bad', '#b5179e', '#f72585', '#7209b7'];
        $hash = crc32($name) % count($colors);
        return $colors[$hash];
    }
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

    // Fetch Student Data Button
    document.getElementById('fetchStudentDataButton').addEventListener('click', function() {
        // Open the modal
        document.getElementById('studentDataModal').style.display = 'block';
    });

    // Search Marks Button
    document.getElementById('searchMarksButton').addEventListener('click', function() {
        const year = document.getElementById('yearSelect').value;
        const className = document.getElementById('classSelect').value;
        const subject = document.getElementById('subjectSelect').value;

        if (year && className && subject) {
            // Perform the AJAX request to retrieve student data
            fetch('retrieve_student_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `year=${year}&class=${className}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Clear previous results
                const studentDetailsDiv = document.getElementById('studentDetails');
                studentDetailsDiv.innerHTML = '';

                // Check if any students were returned
                if (data.length > 0) {
                    // Create a list of students
                    const studentList = document.createElement('ul');
                    data.forEach(student => {
                        const listItem = document.createElement('li');
                        listItem.textContent = `${student.name} - ${subject}`;
                        studentList.appendChild(listItem);
                    });
                    studentDetailsDiv.appendChild(studentList);
                } else {
                    studentDetailsDiv.innerHTML = '<p>No students found for the selected criteria.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching student data:', error);
                document.getElementById('studentDetails').innerHTML = '<p>Error retrieving student data.</p>';
            });
        } else {
            alert('Please select all criteria.');
        }
    });

    // Close modal functionality
    document.querySelector('.close-button').addEventListener('click', function() {
        document.getElementById('studentDataModal').style.display = 'none';
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('studentDataModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Display selected student details
    document.getElementById('studentDropdown').addEventListener('change', function() {
        const selectedValue = this.value;
        const studentDetailsDiv = document.getElementById('studentDetails');

        if (selectedValue) {
            // Fetch and display student details based on selected value
            studentDetailsDiv.innerHTML = `Selected Student ID: ${selectedValue}`; // Example display
        } else {
            studentDetailsDiv.innerHTML = '';
        }
    });

    // Helper function for ordinal suffixes
    function getOrdinalSuffix(num) {
        const j = num % 10, k = num % 100;
        if (j == 1 && k != 11) return 'st';
        if (j == 2 && k != 12) return 'nd';
        if (j == 3 && k != 13) return 'rd';
        return 'th';
    }
});
</script>

<style>
:root {
    --primary: #4361ee;
    --primary-dark: #3a56d4;
    --primary-light: #e0e7ff;
    --secondary: #3f37c9;
    --success: #4cc9f0;
    --success-dark: #3ab7dc;
    --warning: #f8961e;
    --danger: #f94144;
    --light: #f8f9fa;
    --light-gray: #f1f3f9;
    --medium-gray: #e9ecef;
    --dark-gray: #6c757d;
    --dark: #212529;
    --white: #ffffff;
    --border-radius: 12px;
    --border-radius-sm: 8px;
    --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    --transition: all 0.25s cubic-bezier(0.645, 0.045, 0.355, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background-color: #f8fafc;
    color: var(--dark);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
}

.marks-dashboard {
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 24px;
}

.dashboard-header {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 24px;
    margin: 24px 0;
    box-shadow: var(--box-shadow);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 24px;
}

.header-left {
    flex: 1;
    min-width: 300px;
}

.dashboard-title {
    font-size: 28px;
    font-weight: 600;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.dashboard-title i {
    font-size: 32px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--dark-gray);
    font-size: 14px;
}

.breadcrumb i {
    font-size: 10px;
    opacity: 0.6;
}

.header-right {
    min-width: 280px;
}

.stats-card {
    background: var(--light-gray);
    border-radius: var(--border-radius-sm);
    padding: 16px;
    display: flex;
    gap: 24px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-label {
    font-size: 13px;
    color: var(--dark-gray);
    font-weight: 500;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: var(--primary);
}

.progress-container {
    width: 100%;
}

.progress-track {
    width: 100%;
    height: 6px;
    background: var(--medium-gray);
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 4px;
}

.progress-thumb {
    height: 100%;
    border-radius: 3px;
    transition: var(--transition);
}

.progress-thumb.low { background: var(--danger); }
.progress-thumb.medium { background: var(--warning); }
.progress-thumb.high { background: var(--success); }

.progress-count {
    font-size: 13px;
    font-weight: 600;
    color: var(--dark);
}

.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 300px;
    max-width: 500px;
}

.search-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--dark-gray);
    font-size: 14px;
}

#searchInput {
    width: 100%;
    padding: 12px 16px 12px 44px;
    border: none;
    border-radius: var(--border-radius-sm);
    font-size: 14px;
    transition: var(--transition);
    background: var(--white);
    box-shadow: 0 0 0 1px var(--medium-gray);
}

#searchInput:focus {
    outline: none;
    box-shadow: 0 0 0 2px var(--primary);
}

.search-border {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--medium-gray);
    border-radius: 0 0 2px 2px;
}

#searchInput:focus ~ .search-border {
    background: var(--primary);
}

.action-buttons {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: var(--border-radius-sm);
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary);
    color: var(--white);
    box-shadow: 0 2px 6px rgba(67, 97, 238, 0.3);
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.4);
}

.btn-analytics {
    background: var(--white);
    color: var(--primary);
    box-shadow: 0 0 0 1px var(--medium-gray);
}

.btn-analytics:hover {
    background: var(--light-gray);
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.btn-analytics.success {
    background: var(--success);
    color: var(--white);
    box-shadow: 0 2px 6px rgba(76, 201, 240, 0.3);
}

.data-table-container {
    background: var(--white);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    margin-bottom: 40px;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.data-table thead {
    background: var(--primary);
    color: var(--white);
}

.data-table th {
    padding: 16px;
    font-weight: 500;
    text-align: left;
    font-size: 14px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table th span {
    font-weight: 400;
    opacity: 0.8;
    font-size: 12px;
}

.data-table td {
    padding: 16px;
    border-bottom: 1px solid var(--light-gray);
    font-size: 14px;
    transition: var(--transition);
}

.student-row:hover {
    background: rgba(67, 97, 238, 0.03);
}

.col-serial {
    width: 50px;
    text-align: center;
    color: var(--dark-gray);
    font-weight: 500;
}

.col-student {
    min-width: 200px;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.student-details {
    display: flex;
    flex-direction: column;
}

.student-name {
    font-weight: 500;
    color: var(--dark);
}

.student-id {
    font-size: 12px;
    color: var(--dark-gray);
    font-family: 'Roboto Mono', monospace;
}

.col-id {
    min-width: 120px;
}

.col-score, .col-position {
    text-align: center;
    min-width: 150px;
}

.score-input-container {
    position: relative;
    max-width: 120px;
    margin: 0 auto;
}

.score-input {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: var(--border-radius-sm);
    font-size: 14px;
    text-align: center;
    background: var(--light-gray);
    transition: var(--transition);
    box-shadow: 0 0 0 1px var(--medium-gray);
}

.score-input:focus {
    outline: none;
    background: var(--white);
    box-shadow: 0 0 0 2px var(--primary);
}

.input-state {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: var(--medium-gray);
    transition: var(--transition);
    border-radius: 0 0 2px 2px;
}

.score-input:focus ~ .input-state {
    height: 2px;
    background: var(--primary);
}

.score-input-container.valid .input-state {
    background: var(--success);
}

.score-input-container.warning .input-state {
    background: var(--warning);
}

.score-input-container.error .input-state {
    background: var(--danger);
}

.score-input-container.empty .input-state {
    background: var(--medium-gray);
}

.position-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.position-input {
    width: 50px;
    padding: 12px;
    border: none;
    border-radius: var(--border-radius-sm);
    font-size: 14px;
    font-weight: 600;
    color: var(--primary);
    background: var(--light-gray);
    text-align: center;
}

.ordinal-suffix {
    font-size: 12px;
    color: var(--dark-gray);
}

.no-data td {
    padding: 60px 20px;
    text-align: center;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    max-width: 300px;
    margin: 0 auto;
    color: var(--dark-gray);
}

.empty-state i {
    font-size: 48px;
    color: var(--medium-gray);
    margin-bottom: 8px;
}

.empty-state h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--dark);
}

.empty-state p {
    font-size: 14px;
}

/* Modal Styles */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1000; /* Sit on top */
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

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group select {
    width: 100%;
    padding: 10px;
    border-radius: var(--border-radius-sm);
    border: 1px solid var(--medium-gray);
}

@media (max-width: 768px) {
    .dashboard-header {
        padding: 20px;
    }
    
    .header-content {
        flex-direction: column;
    }
    
    .stats-card {
        width: 100%;
        justify-content: space-between;
    }
    
    .action-bar {
        flex-direction: column;
    }
    
    .search-box {
        min-width: 100%;
    }
    
    .action-buttons {
        width: 100%;
    }
    
    .btn {
        flex: 1;
        justify-content: center;
    }
    
    .data-table {
        min-width: 700px;
    }
}
</style>

