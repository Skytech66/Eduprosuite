<?php
session_start();
require_once 'config.php'; // Ensure this file correctly connects to your database

// Check if the teacher is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: logginn.php");
    exit();
}

// Get the assigned class from the session
$assigned_class = $_SESSION['assigned_class'] ?? '';

// Check if a class is specified in the URL
$class_to_access = $_GET['class'] ?? '';

// If the class in the URL does not match the assigned class, redirect
if (!empty($class_to_access) && $class_to_access !== $assigned_class) {
    $_SESSION['general_error_message'] = "You do not have permission to access this class.";
    header("Location: lesson_notes.php");
    exit();
}

// Define variables and initialize with empty values for form
$id = $class = $periods = $week_ending = $class_size = $strand = $sub_strand = $indicator = "";
$content_standard = $performance_indicator = $core_competencies = $keywords = "";
$tlm = $reference = $starter = $main = $plenary = $learning_objectives = $assessment_methods = "";

// Define variables for error messages
$class_err = $periods_err = $week_ending_err = $class_size_err = $strand_err = $sub_strand_err = $indicator_err = "";
$general_error_message = "";
$success_message = "";

// --- Handle Messages from Session ---
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['general_error_message'])) {
    $general_error_message = $_SESSION['general_error_message'];
    unset($_SESSION['general_error_message']);
}

// --- Handle Sticky Form Data and Errors from Session ---
if (isset($_SESSION['form_data'])) {
    $class = $_SESSION['form_data']['class'] ?? '';
    $periods = $_SESSION['form_data']['periods'] ?? '';
    $week_ending = $_SESSION['form_data']['week_ending'] ?? '';
    $class_size = $_SESSION['form_data']['class_size'] ?? '';
    $strand = $_SESSION['form_data']['strand'] ?? '';
    $sub_strand = $_SESSION['form_data']['sub_strand'] ?? '';
    $indicator = $_SESSION['form_data']['indicator'] ?? '';
    $content_standard = $_SESSION['form_data']['content_standard'] ?? '';
    $performance_indicator = $_SESSION['form_data']['performance_indicator'] ?? '';
    $core_competencies = $_SESSION['form_data']['core_competencies'] ?? '';
    $keywords = $_SESSION['form_data']['keywords'] ?? '';
    $tlm = $_SESSION['form_data']['tlm'] ?? '';
    $reference = $_SESSION['form_data']['reference'] ?? '';
    $starter = $_SESSION['form_data']['starter'] ?? '';
    $main = $_SESSION['form_data']['main'] ?? '';
    $plenary = $_SESSION['form_data']['plenary'] ?? '';
    $learning_objectives = $_SESSION['form_data']['learning_objectives'] ?? '';
    $assessment_methods = $_SESSION['form_data']['assessment_methods'] ?? '';
    $id = $_SESSION['form_data']['id'] ?? '';

    $class_err = $_SESSION['form_errors']['class_err'] ?? '';
    $periods_err = $_SESSION['form_errors']['periods_err'] ?? '';
    $week_ending_err = $_SESSION['form_errors']['week_ending_err'] ?? '';
    $class_size_err = $_SESSION['form_errors']['class_size_err'] ?? '';
    $strand_err = $_SESSION['form_errors']['strand_err'] ?? '';
    $sub_strand_err = $_SESSION['form_errors']['sub_strand_err'] ?? '';
    $indicator_err = $_SESSION['form_errors']['indicator_err'] ?? '';

    unset($_SESSION['form_data']);
    unset($_SESSION['form_errors']);
}

// --- Handle GET Requests (for editing or initial load) ---
if (isset($_GET["action"]) && $_GET["action"] == "edit" && isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id_to_edit = trim($_GET["id"]);

    $sql = "SELECT * FROM lesson_notes WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $id_to_edit;

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $id = $row["id"];
                $class = $row["class"];
                $periods = $row["periods"];
                $week_ending = $row["week_ending"];
                $class_size = $row["class_size"];
                $strand = $row["strand"];
                $sub_strand = $row["sub_strand"];
                $indicator = $row["indicator"];
                $content_standard = $row["content_standard"];
                $performance_indicator = $row["performance_indicator"];
                $core_competencies = $row["core_competencies"];
                $keywords = $row["keywords"];
                $tlm = $row["tlm"];
                $reference = $row["reference"];
                $starter = $row["starter"];
                $main = $row["main"];
                $plenary = $row["plenary"];
                $learning_objectives = $row["learning_objectives"];
                $assessment_methods = $row["assessment_methods"];
            } else {
                $_SESSION['general_error_message'] = "Lesson note not found.";
                header("location: lesson_notes.php");
                exit();
            }
        } else {
            $_SESSION['general_error_message'] = "Error fetching data for edit: " . $stmt->error;
            header("location: lesson_notes.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['general_error_message'] = "Error preparing select statement: " . $conn->error;
        header("location: lesson_notes.php");
        exit();
    }
} elseif (isset($_GET["action"]) && $_GET["action"] == "delete" && isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id_to_delete = trim($_GET["id"]);

    $sql = "DELETE FROM lesson_notes WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $id_to_delete;

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Lesson note deleted successfully!";
        } else {
            $_SESSION['general_error_message'] = "Error deleting lesson note: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['general_error_message'] = "Error preparing delete statement: " . $conn->error;
    }
    header("location: lesson_notes.php");
    exit();
}

// --- Handle POST Requests (for Add or Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['form_data'] = $_POST;

    $id = trim($_POST["id"] ?? '');

    // Validate inputs
    $class = trim($_POST["class"] ?? '');
    $periods = trim($_POST["periods"] ?? '');
    $week_ending = trim($_POST["week_ending"] ?? '');
    $class_size = trim($_POST["class_size"] ?? '');
    $strand = trim($_POST["strand"] ?? '');
    $sub_strand = trim($_POST["sub_strand"] ?? '');
    $indicator = trim($_POST["indicator"] ?? '');

    // Error handling
    $class_err = empty($class) ? "Please enter the class." : "";
    $periods_err = empty($periods) ? "Please enter the number of periods." : (!filter_var($periods, FILTER_VALIDATE_INT) ? "Periods must be an integer." : "");
    $week_ending_err = empty($week_ending) ? "Please enter the week ending date." : "";
    $class_size_err = empty($class_size) ? "Please enter the class size." : (!filter_var($class_size, FILTER_VALIDATE_INT) ? "Class size must be an integer." : "");
    $strand_err = empty($strand) ? "Please enter the strand." : "";
    $sub_strand_err = empty($sub_strand) ? "Please enter the sub-strand." : "";
    $indicator_err = empty($indicator) ? "Please enter the indicator." : "";

    $_SESSION['form_errors'] = [
        'class_err' => $class_err,
        'periods_err' => $periods_err,
        'week_ending_err' => $week_ending_err,
        'class_size_err' => $class_size_err,
        'strand_err' => $strand_err,
        'sub_strand_err' => $sub_strand_err,
        'indicator_err' => $indicator_err
    ];

    if (empty($class_err) && empty($periods_err) && empty($week_ending_err) && empty($class_size_err) && empty($strand_err) && empty($sub_strand_err) && empty($indicator_err)) {
        if (empty($id)) {
            $sql = "INSERT INTO lesson_notes (class, periods, week_ending, class_size, strand, sub_strand, indicator, content_standard, performance_indicator, core_competencies, keywords, tlm, reference, starter, main, plenary, learning_objectives, assessment_methods) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        } else {
            $sql = "UPDATE lesson_notes SET class=?, periods=?, week_ending=?, class_size=?, strand=?, sub_strand=?, indicator=?, content_standard=?, performance_indicator=?, core_competencies=?, keywords=?, tlm=?, reference=?, starter=?, main=?, plenary=?, learning_objectives=?, assessment_methods=? WHERE id=?";
        }

        if ($stmt = $conn->prepare($sql)) {
            if (empty($id)) {
                $stmt->bind_param("sissssssssssssssss", $class, $periods, $week_ending, $class_size, $strand, $sub_strand, $indicator, $content_standard, $performance_indicator, $core_competencies, $keywords, $tlm, $reference, $starter, $main, $plenary, $learning_objectives, $assessment_methods);
            } else {
                $stmt->bind_param("sissssssssssssssssi", $class, $periods, $week_ending, $class_size, $strand, $sub_strand, $indicator, $content_standard, $performance_indicator, $core_competencies, $keywords, $tlm, $reference, $starter, $main, $plenary, $learning_objectives, $assessment_methods, $id);
            }

            if ($stmt->execute()) {
                $_SESSION['success_message'] = empty($id) ? "Lesson note added successfully!" : "Lesson note updated successfully!";
                unset($_SESSION['form_data']);
                unset($_SESSION['form_errors']);
                header("location: lesson_notes.php");
                exit();
            } else {
                $_SESSION['general_error_message'] = "Error executing statement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['general_error_message'] = "Error preparing statement: " . $conn->error;
        }
    } else {
        header("location: lesson_notes.php");
        exit();
    }
}

// --- Fetch existing lesson notes to display ---
$lesson_notes = [];
$sql_select = "SELECT id, class, periods, week_ending, strand, sub_strand, indicator FROM lesson_notes WHERE class = ? ORDER BY week_ending DESC, class ASC";
if ($stmt = $conn->prepare($sql_select)) {
    $stmt->bind_param("s", $assigned_class);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $lesson_notes[] = $row;
            }
        }
        $result->free();
    } else {
        $general_error_message = "Error fetching lesson notes: " . $stmt->error;
    }
    $stmt->close();
} else {
    $general_error_message = "Error preparing statement: " . $conn->error;
}

// Close database connection after all operations are complete
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lesson Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #3a56d4;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --success-color: #38b000;
            --danger-color: #ef233c;
            --warning-color: #ff9e00;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #2b2d42;
            --light-text: #8d99ae;
            --border-color: #e9ecef;
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 4px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --border-radius: 12px;
            --border-radius-sm: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: var(--shadow-medium);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }

        header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            position: relative;
        }

        .form-section, .cards-section {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
        }

        .form-section:hover, .cards-section:hover {
            box-shadow: var(--shadow-medium);
        }

        h2 {
            color: var(--secondary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-color);
            font-weight: 600;
            font-size: 1.5rem;
            position: relative;
        }

        h2::after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent-color);
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: var(--light-bg);
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
            background-color: var(--card-bg);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .error-message {
            color: var(--danger-color);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }

        .message-box {
            padding: 15px;
            border-radius: var(--border-radius-sm);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .success-message {
            background-color: rgba(56, 176, 0, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-message {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
                        justify-content: center;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius-sm);
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: white;
            gap: 8px;
        }

        .btn i {
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(63, 55, 201, 0.2);
        }

        .btn-secondary {
            background-color: var(--light-text);
        }

        .btn-secondary:hover {
            background-color: #6c757d;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #2d8a00;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #d90429;
            transform: translateY(-2px);
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            gap: 15px;
        }

        .lesson-note-card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            margin-bottom: 20px;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
            position: relative;
            overflow: hidden;
        }

        .lesson-note-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .lesson-note-card h3 {
            margin-bottom: 10px;
            color: var(--secondary-color);
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .lesson-note-card p {
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--light-text);
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .lesson-note-card p strong {
            color: var(--text-color);
            min-width: 100px;
            display: inline-block;
        }

        .lesson-note-card .actions {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--light-text);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 20px;
        }

        .empty-state p {
            font-size: 1.1rem;
        }

        /* Floating action button for mobile */
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            z-index: 100;
            transition: var(--transition);
        }

        .fab:hover {
            transform: scale(1.1);
            background-color: var(--secondary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .cards-section {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8rem;
            }
            
            .form-section, .cards-section {
                padding: 20px;
            }
            
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .fab {
                display: flex;
            }
        }

        @media (max-width: 576px) {
            header {
                padding: 15px 0;
            }
            
            h2 {
                font-size: 1.3rem;
            }
            
            .lesson-note-card .actions {
                flex-direction: column;
            }
            
            .lesson-note-card p {
                flex-direction: column;
                gap: 2px;
            }
            
            .lesson-note-card p strong {
                min-width: auto;
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

</head>
<body>
<header class="d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
    <h1 class="m-0">
        <i class="fas fa-book-open"></i> Lesson Notes Management
    </h1>
    <div class="d-flex align-items-center">
        <span class="me-3">Assigned Class: <strong><?= htmlspecialchars($_SESSION['assigned_class'] ?? 'N/A') ?></strong></span>
        <a href="dashboard.php" class="btn btn-light">
            <i class="fas fa-tachometer-alt"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>
</header>

    <div class="container">
        <div class="form-section">
            <h2><?php echo empty($id) ? "Add New Lesson Note" : "Edit Lesson Note"; ?></h2>
                
            <?php if (!empty($success_message)): ?>
                <div class="message-box success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($general_error_message)): ?>
                <div class="message-box alert-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo $general_error_message; ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                <div class="form-group">
                    <label for="class"><i class="fas fa-users"></i> Class:</label>
                    <input type="text" id="class" name="class" value="<?php echo htmlspecialchars($class); ?>" placeholder="Enter class name">
                    <span class="error-message"><?php echo $class_err; ?></span>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="periods"><i class="fas fa-clock"></i> Periods:</label>
                        <input type="number" id="periods" name="periods" value="<?php echo htmlspecialchars($periods); ?>" placeholder="Number of periods">
                        <span class="error-message"><?php echo $periods_err; ?></span>
                    </div>

                    <div class="form-group">
                        <label for="class_size"><i class="fas fa-user-graduate"></i> Class Size:</label>
                        <input type="number" id="class_size" name="class_size" value="<?php echo htmlspecialchars($class_size); ?>" placeholder="Number of students">
                        <span class="error-message"><?php echo $class_size_err; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="week_ending"><i class="fas fa-calendar-day"></i> Week Ending:</label>
                    <input type="date" id="week_ending" name="week_ending" value="<?php echo htmlspecialchars($week_ending); ?>">
                    <span class="error-message"><?php echo $week_ending_err; ?></span>
                </div>

                <div class="form-group">
                    <label for="strand"><i class="fas fa-layer-group"></i> Strand: <span style="color:var(--danger-color)">*</span></label>
                    <textarea id="strand" name="strand" placeholder="e.g., Number Sense"><?php echo htmlspecialchars($strand); ?></textarea>
                    <span class="error-message"><?php echo $strand_err; ?></span>
                </div>

                <div class="form-group">
                    <label for="sub_strand"><i class="fas fa-stream"></i> Sub-Strand: <span style="color:var(--danger-color)">*</span></label>
                    <textarea id="sub_strand" name="sub_strand" placeholder="e.g., Counting and Cardinality"><?php echo htmlspecialchars($sub_strand); ?></textarea>
                    <span class="error-message"><?php echo $sub_strand_err; ?></span>
                </div>

                <div class="form-group">
                    <label for="indicator"><i class="fas fa-bullseye"></i> Indicator: <span style="color:var(--danger-color)">*</span></label>
                    <textarea id="indicator" name="indicator" placeholder="e.g., Identify and count numbers up to 100"><?php echo htmlspecialchars($indicator); ?></textarea>
                    <span class="error-message"><?php echo $indicator_err; ?></span>
                </div>

                <div class="form-group">
                    <label for="content_standard"><i class="fas fa-graduation-cap"></i> Content Standard (Optional):</label>
                    <textarea id="content_standard" name="content_standard" placeholder="e.g., MA.K.CC.A.1 - Count to 100 by ones and by tens."><?php echo htmlspecialchars($content_standard); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="performance_indicator"><i class="fas fa-chart-line"></i> Performance Indicator (Optional):</label>
                    <textarea id="performance_indicator" name="performance_indicator" placeholder="e.g., Students will be able to orally count from 1 to 50 and write numbers from 1 to 20."><?php echo htmlspecialchars($performance_indicator); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="core_competencies"><i class="fas fa-brain"></i> Core Competencies (Optional):</label>
                    <textarea id="core_competencies" name="core_competencies" placeholder="e.g., Critical thinking, Communication"><?php echo htmlspecialchars($core_competencies); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="keywords"><i class="fas fa-key"></i> Keywords (Optional):</label>
                    <textarea id="keywords" name="keywords" placeholder="e.g., numbers, counting, cardinality, tens, ones"><?php echo htmlspecialchars($keywords); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tlm"><i class="fas fa-tools"></i> TLM (Teaching/Learning Materials) (Optional):</label>
                    <textarea id="tlm" name="tlm" placeholder="e.g., Number charts, counting blocks, flashcards"><?php echo htmlspecialchars($tlm); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="reference"><i class="fas fa-book"></i> Reference (Optional):</label>
                    <textarea id="reference" name="reference" placeholder="e.g., Mathematics Curriculum Guide - Grade K, Unit 2"><?php echo htmlspecialchars($reference); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="starter"><i class="fas fa-play"></i> Starter (Optional):</label>
                    <textarea id="starter" name="starter" placeholder="e.g., Sing a counting song from 1 to 20."><?php echo htmlspecialchars($starter); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="main"><i class="fas fa-tasks"></i> Main (Optional):</label>
                    <textarea id="main" name="main" placeholder="e.g., Introduce number chart. Practice counting by ones and tens. Group activities with counting blocks."><?php echo htmlspecialchars($main); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="plenary"><i class="fas fa-stop"></i> Plenary (Optional):</label>
                    <textarea id="plenary" name="plenary" placeholder="e.g., Quick number identification game. Review daily objectives."><?php echo htmlspecialchars($plenary); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="learning_objectives"><i class="fas fa-bullseye"></i> Learning Objectives (Optional):</label>
                    <textarea id="learning_objectives" name="learning_objectives" placeholder="e.g., Students will be able to count orally to 50. Students will be able to write numbers 1-20."><?php echo htmlspecialchars($learning_objectives); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="assessment_methods"><i class="fas fa-clipboard-check"></i> Assessment Methods (Optional):</label>
                    <textarea id="assessment_methods" name="assessment_methods" placeholder="e.g., Observation during group activity, informal quiz on number writing."><?php echo htmlspecialchars($assessment_methods); ?></textarea>
                </div>

                <div class="button-group">
                    <?php if (!empty($id)): ?>
                        <a href="lesson_notes.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <?php if (empty($id)): ?>
                            <i class="fas fa-plus-circle"></i> Add Lesson Note
                        <?php else: ?>
                            <i class="fas fa-save"></i> Update Lesson Note
                        <?php endif; ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="cards-section">
            <h2><i class="fas fa-clipboard-list"></i> Existing Lesson Notes</h2>
            <?php if (!empty($lesson_notes)): ?>
                <?php foreach ($lesson_notes as $note): ?>
                    <div class="lesson-note-card">
                        <h3><i class="fas fa-book"></i> <?php echo htmlspecialchars($note['class']); ?></h3>
                        <p><strong><i class="fas fa-clock"></i> Periods:</strong> <?php echo htmlspecialchars($note['periods']); ?></p>
                        <p><strong><i class="fas fa-calendar-day"></i> Week Ending:</strong> <?php echo htmlspecialchars($note['week_ending']); ?></p>
                        <p><strong><i class="fas fa-layer-group"></i> Strand:</strong> <?php echo nl2br(htmlspecialchars($note['strand'])); ?></p>
                        <p><strong><i class="fas fa-stream"></i> Sub-Strand:</strong> <?php echo nl2br(htmlspecialchars($note['sub_strand'])); ?></p>
                        <p><strong><i class="fas fa-bullseye"></i> Indicator:</strong> <?php echo nl2br(htmlspecialchars($note['indicator'])); ?></p>
                        <div class="actions">
                            <a href="?action=edit&id=<?php echo $note['id']; ?>" class="btn btn-success"><i class="fas fa-edit"></i> Edit</a>
                            <a href="?action=delete&id=<?php echo $note['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this lesson note?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>No lesson notes found. Add your first lesson note using the form!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="#" class="fab" id="scrollToTop" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script>
        // Scroll to top button
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.style.display = 'flex';
            } else {
                scrollToTopBtn.style.display = 'none';
            }
        });
        
        scrollToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
