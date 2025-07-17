<?php
session_start();
require 'config.php'; // Database connection

// Check if teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php");
    exit;
}

$assigned_class = $_SESSION['assigned_class'];

// Fetch students belonging to the assigned class
$stmt = $conn->prepare("SELECT id, name FROM students WHERE class = ?");
$stmt->bind_param("s", $assigned_class);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle submission of ratings
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['critical_thinking', 'logical_reasoning', 'collaboration', 'creativity', 'communication', 'behavior', 'notes'];
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || !is_array($_POST[$field])) {
            $errorMessage = "Invalid form submission: missing $field.";
            break;
        }
    }

    if (!$errorMessage) {
        $conn->begin_transaction();
        try {
            $stmtInsert = $conn->prepare("
                INSERT INTO behaviour (
                    student_name, critical_thinking, logical_reasoning, collaboration, creativity, communication, behavior, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            if (!$stmtInsert) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $count = count($students);

            for ($i = 0; $i < $count; $i++) {
                $studentName = $students[$i]['name'];

                $criticalThinking = isset($_POST['critical_thinking'][$i]) && $_POST['critical_thinking'][$i] !== '' ? max(1, min(5, (int)$_POST['critical_thinking'][$i])) : null;
                $logicalReasoning = isset($_POST['logical_reasoning'][$i]) && $_POST['logical_reasoning'][$i] !== '' ? max(1, min(5, (int)$_POST['logical_reasoning'][$i])) : null;
                $collaboration = isset($_POST['collaboration'][$i]) && $_POST['collaboration'][$i] !== '' ? max(1, min(5, (int)$_POST['collaboration'][$i])) : null;
                $creativity = isset($_POST['creativity'][$i]) && $_POST['creativity'][$i] !== '' ? max(1, min(5, (int)$_POST['creativity'][$i])) : null;
                $communication = isset($_POST['communication'][$i]) && $_POST['communication'][$i] !== '' ? max(1, min(5, (int)$_POST['communication'][$i])) : null;

                $behavior = isset($_POST['behavior'][$i]) ? trim($_POST['behavior'][$i]) : '';
                $notes = isset($_POST['notes'][$i]) ? trim($_POST['notes'][$i]) : '';

                $stmtInsert->bind_param(
                    "siiiiiss",
                    $studentName,
                    $criticalThinking,
                    $logicalReasoning,
                    $collaboration,
                    $creativity,
                    $communication,
                    $behavior,
                    $notes
                );

                $stmtInsert->execute();

                if ($stmtInsert->error) {
                    throw new Exception("Execute failed: " . $stmtInsert->error);
                }
            }

            $stmtInsert->close();
            $conn->commit();
            $successMessage = "Ratings successfully submitted!";
            echo "<script>var successMessage = '$successMessage';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = "Error submitting ratings: " . htmlspecialchars($e->getMessage());
            echo "<script>var errorMessage = '$errorMessage';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ratings for<?=htmlspecialchars($assigned_class)?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --primary-dark: #4338ca;
            --secondary: #7c3aed;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --light-gray: #f1f5f9;
            --border-gray: #e2e8f0;
            --error: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1800px;
            margin: 0 auto;
            padding: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-gray);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h1 i {
            color: var(--primary);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-outline {
            background-color: white;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .card-header {
            padding: 18px 24px;
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header i {
            font-size: 18px;
        }

        .card-body {
            padding: 24px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
        }

        thead th {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            font-weight: 600;
            padding: 14px 16px;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr {
            transition: var(--transition);
        }

        tbody tr:hover {
            background-color: var(--light-gray);
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:nth-child(even):hover {
            background-color: var(--light-gray);
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-gray);
            vertical-align: middle;
        }

        .student-name {
            font-weight: 500;
            color: var(--dark);
            min-width: 180px;
        }

        .rating-input {
            width: 70px;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
            font-size: 14px;
            text-align: center;
            transition: var(--transition);
            background-color: white;
            color: var(--dark);
            font-weight: 500;
        }

        .rating-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .notes-input {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-gray);
            font-size: 14px;
            transition: var(--transition);
            min-width: 200px;
        }

        .notes-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .submit-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 32px;
            font-size: 16px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: block;
            margin: 24px auto 0;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(79, 70, 229, 0.3);
        }

        .message {
            padding: 16px 24px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }

        .message-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .message-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--error);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 48px;
            color: var(--border-gray);
            margin-bottom: 16px;
        }

        .empty-state p {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .action-buttons {
                width: 100%;
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .card-body {
                padding: 16px;
            }
        }

        /* Tooltip for rating inputs */
        .rating-tooltip {
            position: relative;
            display: inline-block;
        }

        .rating-tooltip .tooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: var(--dark);
            color: white;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
            font-weight: normal;
        }

        .rating-tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1><i class="fas fa-chart-line"></i> Skill Ratings for <?=htmlspecialchars($assigned_class)?></h1>
        <div class="action-buttons">
            <a href="view_ratings.php" class="btn btn-outline">
                <i class="fas fa-eye"></i> View Skills
            </a>
             <a href="lo.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Back
            </a>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-user-graduate"></i> Student Ratings</span>
            <i class="fas fa-info-circle" title="Rate each student on a scale of 1-5 for each skill"></i>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Critical Thinking</th>
                            <th>Logical Reasoning</th>
                            <th>Collaboration</th>
                            <th>Creativity</th>
                            <th>Communication</th>
                            <th>Behavior Notes</th>
                            <th>Additional Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students): ?>
                            <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td class="student-name"><?= htmlspecialchars($student['name']) ?></td>
                                <td>
                                    <div class="rating-tooltip">
                                        <input type="number" class="rating-input" name="critical_thinking[]" min="1" max="5" />
                                        <span class="tooltiptext">1 (Needs Work) - 5 (Excellent)</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-tooltip">
                                        <input type="number" class="rating-input" name="logical_reasoning[]" min="1" max="5" />
                                        <span class="tooltiptext">1 (Needs Work) - 5 (Excellent)</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-tooltip">
                                        <input type="number" class="rating-input" name="collaboration[]" min="1" max="5" />
                                        <span class="tooltiptext">1 (Needs Work) - 5 (Excellent)</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-tooltip">
                                        <input type="number" class="rating-input" name="creativity[]" min="1" max="5" />
                                        <span class="tooltiptext">1 (Needs Work) - 5 (Excellent)</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-tooltip">
                                        <input type="number" class="rating-input" name="communication[]" min="1" max="5" />
                                        <span class="tooltiptext">1 (Needs Work) - 5 (Excellent)</span>
                                    </div>
                                </td>
                                <td><input type="text" class="notes-input" name="behavior[]" placeholder="Behavior notes..." /></td>
                                <td><input type="text" class="notes-input" name="notes[]" placeholder="Additional notes..." /></td>
                            </tr>
                            <input type="hidden" name="student_name[]" value="<?= htmlspecialchars($student['name']) ?>" />
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-user-slash"></i>
                                <p>No students found in this class.</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Ratings
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Add input validation for ratings
    document.querySelectorAll('.rating-input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > 5) this.value = 5;
        });
    });

    // Add confirmation before submitting
    document.querySelector('form').addEventListener('submit', function(e) {
                const emptyRatings = Array.from(document.querySelectorAll('.rating-input'))
            .filter(input => input.value === '');
        
        if (emptyRatings.length > 0) {
            if (!confirm('Some ratings are empty. Submit anyway?')) {
                e.preventDefault();
            }
        }
    });

    // SweetAlert logic
    if (typeof successMessage !== 'undefined') {
        swal("Success!", successMessage, "success");
    }

    if (typeof errorMessage !== 'undefined') {
        swal("Error!", errorMessage, "error");
    }

    // Prevent form resubmission on refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
</script>
</body>
</html>

<?php $conn->close(); ?>
