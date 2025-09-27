<?php
require_once "config.php"; // Database configuration (assuming PDO connection)

// Fetch all subjects from database
function getSubjects($conn) {
    $sql = "SELECT * FROM subject";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt;
}

// Handle AJAX request for subject details
if (isset($_GET['action']) && $_GET['action'] == 'get_subject' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM subject WHERE subjectid = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $subject = $stmt->fetch(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($subject);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4cc9f0;
            --secondary: #3f37c9;
            --success: #4ad66d;
            --warning: #f8961e;
            --danger: #f94144;
            --light: #f8f9fa;
            --dark: #212529;
            --text: #2b2d42;
            --muted: #8d99ae;
            --border: #e9ecef;
            --white: #ffffff;
            --hover: #f1f3f5;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7fa;
            color: var(--text);
            line-height: 1.6;
            font-size: 16px;
        }
        
        .container {
            max-width: 1800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        
        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .header-content {
            flex: 1;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-md);
            margin-right: 1rem;
            color: white;
        }
        
        .page-description {
            color: var(--muted);
            font-size: 1rem;
        }
        
        /* Button Styles */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }
        
        /* Control Panel */
        .control-panel {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }
        
        .search-container {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
        }
        
        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            outline: none;
        }
        
        .filter-options {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .filter-group label {
            font-size: 0.875rem;
            color: var(--muted);
            white-space: nowrap;
        }
        
        .form-select {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            background-color: var(--white);
            min-width: 150px;
        }
        
        /* Table Styles */
        .data-table {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table thead th {
            background-color: var(--light);
            color: var(--muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        
        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .table tbody tr:hover {
            background-color: var(--hover);
        }
        
        .sortable {
            cursor: pointer;
            transition: color 0.2s;
            user-select: none;
        }
        
        .sortable:hover {
            color: var(--primary);
        }
        
        /* Subject Info Styles */
        .subject-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .subject-icon {
            width: 2.5rem;
            height: 2.5rem;
            background-color: rgba(67, 97, 238, 0.1);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }
        
        .subject-name {
            font-weight: 500;
        }
        
        .subject-code {
            font-size: 0.75rem;
            color: var(--muted);
        }
        
        /* Badge Styles */
        .class-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--primary);
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Teacher Info Styles */
        .teacher-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .teacher-avatar {
            width: 2rem;
            height: 2rem;
            background-color: rgba(248, 150, 30, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--warning);
            flex-shrink: 0;
        }
        
        .teacher-name {
            font-weight: 500;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: var(--radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-edit {
            color: var(--warning);
        }
        
        .btn-edit:hover {
            background-color: rgba(248, 150, 30, 0.1);
        }
        
        .btn-view {
            color: var(--primary);
        }
        
        .btn-view:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }
        
        .btn-delete {
            color: var(--danger);
        }
        
        .btn-delete:hover {
            background-color: rgba(249, 65, 68, 0.1);
        }
        
        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
        }
        
        .pagination-info {
            color: var(--muted);
            font-size: 0.875rem;
        }
        
        .pagination-controls {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-pagination {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: var(--radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-pagination:hover {
            background-color: var(--hover);
        }
        
        .btn-pagination.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(2px);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            width: 50%;
            max-width: 600px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            color: var(--muted);
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .close:hover {
            color: var(--danger);
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        
        .modal-body {
            margin-bottom: 1.5rem;
        }
        
        .modal-row {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .modal-label {
            font-weight: 500;
            width: 120px;
            color: var(--muted);
        }
        
        .modal-value {
            flex: 1;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .modal-content {
                width: 70%;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .filter-options {
                flex-direction: column;
                gap: 1rem;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .form-select {
                width: 100%;
            }
            
            .pagination {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
            }
            
            .modal-content {
                width: 90%;
                margin: 20% auto;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 0 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-icon {
                width: 100%;
                justify-content: flex-start;
                padding: 0.5rem;
            }
            
            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
    <!-- Header Section -->
    <header class="header">
        <div class="header-content">
            <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: 10px;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="page-title">
                <span class="icon-wrapper"><i class="fas fa-book-open"></i></span>
                Subject Management
            </h1>
            <p class="page-description">Efficiently manage and organize all academic subjects</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Subject
            </button>
        </div>
    </header>


        <!-- Control Panel -->
        <section class="control-panel">
            <!-- Search Bar -->
            <div class="search-container">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input type="text" id="subjectSearch" class="search-input" placeholder="Search subjects..." onkeyup="filterTable()" aria-label="Search subjects">
            </div>

            <!-- Filter Options -->
            <div class="filter-options">
                <div class="filter-group">
                    <label for="classFilter">Filter by Class:</label>
                    <select id="classFilter" class="form-select" onchange="filterTable()">
                        <option value="" selected>All Classes</option>
                        <option value="10">Class 10</option>
                        <option value="11">Class 11</option>
                        <option value="12">Class 12</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="teacherFilter">Filter by Teacher:</label>
                    <select id="teacherFilter" class="form-select" onchange="filterTable()">
                        <option value="" selected>All Teachers</option>
                        <?php
                        // Fetch unique teachers from the database
                        $teacherQuery = "SELECT DISTINCT teacherid FROM subject WHERE teacherid IS NOT NULL";
                        $teacherStmt = $conn->prepare($teacherQuery);
                        $teacherStmt->execute();
                        $teacherResult = $teacherStmt;
                        if ($teacherResult && $teacherResult->rowCount() > 0) {
                            while ($teacher = $teacherResult->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($teacher['teacherid']) . '">' . htmlspecialchars($teacher['teacherid']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </section>

        <!-- Data Table -->
        <section class="data-table">
            <div class="table-responsive">
                <table id="subjectTable" class="table">
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortTable(0)">
                                Subject <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" onclick="sortTable(1)">
                                Class <i class="fas fa-sort"></i>
                            </th>
                            <th class="sortable" onclick="sortTable(2)">
                                Teacher <i class="fas fa-sort"></i>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subjects = getSubjects($conn);
                        if ($subjects && $subjects->rowCount() > 0) {
                            while ($row = $subjects->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                            <tr>
                                <td>
                                    <div class="subject-info">
                                        <div class="subject-icon">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div>
                                            <div class="subject-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                            <div class="subject-code">SUB-<?php echo strtoupper(substr($row['name'], 0, 3)); ?>-<?php
                                                                                        echo htmlspecialchars($row['subjectid']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="class-badge">Class <?php echo htmlspecialchars($row['classid']); ?></span>
                                </td>
                                <td>
                                                                      <div class="teacher-info">
                                        <div class="teacher-avatar">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="teacher-name"><?php echo htmlspecialchars($row['teacherid']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-edit" title="Edit" onclick="showAdminMessage()" aria-label="Edit">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn-icon btn-view" title="View Details" onclick="showSubjectDetails(<?php echo htmlspecialchars($row['subjectid']); ?>)" 
                                                aria-label="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon btn-delete" title="Delete" 
                                                onclick="confirmDelete(<?php echo htmlspecialchars($row['subjectid']); ?>)" 
                                                aria-label="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center py-4">No subjects found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Pagination -->
        <nav class="pagination">
            <div class="pagination-info">
                Showing 1 to 10 of 50 entries
            </div>
            <div class="pagination-controls">
                <button class="btn-pagination" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="btn-pagination active">1</button>
                <button class="btn-pagination">2</button>
                <button class="btn-pagination">3</button>
                <button class="btn-pagination">4</button>
                <button class="btn-pagination">5</button>
                <button class="btn-pagination">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </nav>
    </div>

    <!-- Subject Details Modal -->
    <div id="subjectDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="modal-title">
                <i class="fas fa-book"></i> Subject Details
            </h2>
            <div class="modal-body">
                <div class="modal-row">
                    <div class="modal-label">Subject ID:</div>
                    <div class="modal-value" id="modalSubjectId">-</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label">Name:</div>
                    <div class="modal-value" id="modalSubjectName">-</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label">Class:</div>
                    <div class="modal-value" id="modalSubjectClass">-</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label">Teacher:</div>
                    <div class="modal-value" id="modalSubjectTeacher">-</div>
                </div>
                <div class="modal-row">
                    <div class="modal-label">Category:</div>
                    <div class="modal-value" id="modalSubjectCategory">-</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Table filter functionality
        function filterTable() {
            const searchValue = document.getElementById('subjectSearch').value.toLowerCase();
            const classFilter = document.getElementById('classFilter').value.toLowerCase();
            const teacherFilter = document.getElementById('teacherFilter').value.toLowerCase();
            const rows = document.querySelectorAll('#subjectTable tbody tr');

            rows.forEach(row => {
                const subjectName = row.cells[0].textContent.toLowerCase();
                const className = row.cells[1].textContent.toLowerCase();
                const teacherName = row.cells[2].textContent.toLowerCase();

                const matchesSearch = searchValue === '' || subjectName.includes(searchValue);
                const matchesClass = classFilter === '' || className.includes(classFilter);
                const matchesTeacher = teacherFilter === '' || teacherName.includes(teacherFilter);

                row.style.display = matchesSearch && matchesClass && matchesTeacher ? '' : 'none';
            });
        }

        // Sort table functionality
        function sortTable(columnIndex) {
            const table = document.getElementById('subjectTable');
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const header = table.querySelectorAll('thead th')[columnIndex];
            const isAscending = header.classList.contains('asc');

            // Clear all sort classes
            table.querySelectorAll('thead th').forEach(th => {
                th.classList.remove('asc', 'desc');
            });

            // Set new sort direction
            header.classList.add(isAscending ? 'desc' : 'asc');
            const direction = isAscending ? -1 : 1;

            // Sort rows
            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim().toLowerCase();
                const bText = b.cells[columnIndex].textContent.trim().toLowerCase();
                return aText.localeCompare(bText) * direction;
            });

            // Re-insert sorted rows
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }

        // Confirm delete dialog
        function confirmDelete(subjectId) {
            if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
                alert('Sorry, only admin can perform this action.');
                console.log('Delete subject with ID:', subjectId);
            }
        }

        // Show admin message for edit action
        function showAdminMessage() {
            alert('Sorry, only admin can perform this action.');
        }

        // Show subject details in a modal
        function showSubjectDetails(subjectId) {
            fetch('?action=get_subject&id=' + subjectId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.subjectid) {
                        document.getElementById('modalSubjectId').textContent = data.subjectid || '-';
                        document.getElementById('modalSubjectName').textContent = data.name || '-';
                        document.getElementById('modalSubjectClass').textContent = 'Class ' + (data.classid || '-');
                        document.getElementById('modalSubjectTeacher').textContent = data.teacherid || '-';
                        document.getElementById('modalSubjectCategory').textContent = data.category || '-';
                        
                        const modal = document.getElementById('subjectDetailsModal');
                        modal.style.display = 'block';
                    } else {
                        throw new Error('Subject not found');
                    }
                })
                .catch(error => {
                    console.error('Error fetching subject details:', error);
                    alert('Error loading subject details. Please try again.');
                });
        }

        // Close modal function
        function closeModal() {
            document.getElementById('subjectDetailsModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('subjectDetailsModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            const modal = document.getElementById('subjectDetailsModal');
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php
$conn = null;
?>
                                    
