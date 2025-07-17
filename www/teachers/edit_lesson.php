<?php
require_once "db_connection.php";

// Security Key for Editing
$stored_security_key = 'your-secret-key'; // Example security key

// Get the lesson ID from the URL
$lesson_id = $_GET['id'];

// Fetch the lesson note based on the ID
$query = "SELECT * FROM lesson_notes WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->execute([':id' => $lesson_id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

// If no lesson found, redirect to the lesson notes list
if (!$lesson) {
    echo "Lesson note not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $entered_security_key = $_POST['security_key']; // Security key entered by the user

    // Validate the security key
    if ($entered_security_key !== $stored_security_key) {
        echo "<p>Incorrect security key! You are not authorized to edit.</p>";
        exit;
    }

    // Proceed with the form submission to update the lesson
    // Collect all the form data and update the lesson note
    $class = $_POST['class'];
    $periods = $_POST['periods'];
    $week_ending = $_POST['week_ending'];
    $class_size = $_POST['class_size'];
    $strand = $_POST['strand'];
    $sub_strand = $_POST['sub_strand'];
    $indicator = $_POST['indicator'];
    $content_standard = $_POST['content_standard'];
    $performance_indicator = $_POST['performance_indicator'];
    $core_competencies = $_POST['core_competencies'];
    $keywords = $_POST['keywords'];
    $tlm = $_POST['tlm'];
    $reference = $_POST['reference'];
    $starter = $_POST['starter'];
    $main = $_POST['main'];
    $plenary = $_POST['plenary'];

    // Update the lesson note in the database
    try {
        $query = "UPDATE lesson_notes SET class = :class, periods = :periods, week_ending = :week_ending, 
                  class_size = :class_size, strand = :strand, sub_strand = :sub_strand, indicator = :indicator, 
                  content_standard = :content_standard, performance_indicator = :performance_indicator, 
                  core_competencies = :core_competencies, keywords = :keywords, tlm = :tlm, 
                  reference = :reference, starter = :starter, main = :main, plenary = :plenary
                  WHERE id = :lesson_id";
        
        $stmt = $conn->prepare($query);
        $stmt->execute([
            ':lesson_id' => $lesson_id,
            ':class' => $class,
            ':periods' => $periods,
            ':week_ending' => $week_ending,
            ':class_size' => $class_size,
            ':strand' => $strand,
            ':sub_strand' => $sub_strand,
            ':indicator' => $indicator,
            ':content_standard' => $content_standard,
            ':performance_indicator' => $performance_indicator,
            ':core_competencies' => $core_competencies,
            ':keywords' => $keywords,
            ':tlm' => $tlm,
            ':reference' => $reference,
            ':starter' => $starter,
            ':main' => $main,
            ':plenary' => $plenary
        ]);
        echo "<p>Lesson note updated successfully!</p>";
    } catch (PDOException $e) {
        echo "<p>Error updating lesson note: " . $e->getMessage() . "</p>";
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lesson Note</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        /* Add the necessary CSS here */
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Edit Lesson Note</h2>

        <!-- Edit Lesson Note Form -->
        <form method="POST">
            <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
            <label for="security_key">Enter Security Key</label>
            <input type="password" name="security_key" required>

            <!-- Display lesson note details in editable fields -->
            <label for="class">Class</label>
            <input type="text" name="class" value="<?php echo htmlspecialchars($lesson['class']); ?>" required>
            <!-- Add other form fields for editing lesson note details -->
            <button type="submit">Update Lesson Note</button>
        </form>
    </div>
</body>
</html>
