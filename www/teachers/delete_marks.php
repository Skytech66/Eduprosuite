<?php
ob_start();  // Start output buffering
require_once "header.php";

// Direct SQLite connection
$database_name = "../db/school.db"; 
$conn = new SQLite3($database_name);

// Handling record deletion
if (isset($_POST['marksid'])) {
    $marksid = $_POST['marksid'];
    $entered_code = $_POST['security_code'];

    // The unique code (you can change this to something else if needed)
    $correct_code = "q1234";

    // Validate the entered code
    if ($entered_code === $correct_code) {
        // Prepare the DELETE query
        $stmt = $conn->prepare("DELETE FROM marks WHERE marksid = :marksid");
        $stmt->bindValue(':marksid', $marksid, SQLITE3_INTEGER);

        // Execute the query
        if ($stmt->execute()) {
            // Redirect immediately after deletion
            header("Location: exams.php?success=1");
            exit; // Ensure no further code runs
        } else {
            // Redirect if delete fails
            header("Location: exams.php?error=1");
            exit; // Ensure no further code runs
        }
    } else {
        // If the code is incorrect, display an error message
        $error_message = "Invalid security code entered. Please try again.";
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center mb-4">Delete Mark Entry</h2>

    <!-- Display Success/Error messages -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> The record has been deleted successfully.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> There was an issue deleting the record. Please try again later.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php elseif (isset($error_message)): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning!</strong> <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Security code form -->
    <div class="card p-4 shadow-sm">
        <form method="POST" action="">
            <!-- Hidden field to pass marksid -->
            <input type="hidden" name="marksid" value="<?php echo isset($_GET['marksid']) ? htmlspecialchars($_GET['marksid']) : ''; ?>">
            
            <div class="form-group">
                <label for="security_code">Enter Security Code to Delete Record</label>
                <input type="text" class="form-control" id="security_code" name="security_code" placeholder="Enter the code" required>
                <small class="form-text text-muted">Please enter the security code (e.g., q123) to proceed.</small>
            </div>
            <button type="submit" class="btn btn-danger btn-block">Delete Record</button>
        </form>
    </div>

    <!-- Link back to exams page -->
    <div class="mt-3 text-center">
        <a href="exams.php" class="btn btn-secondary">Back to Exam List</a>
    </div>
</div>

<?php
ob_end_flush();  // Flush the output buffer and send output
?>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
