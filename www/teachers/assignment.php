<?php
session_start();
require_once 'config.php'; // Include the database connection file

// Display the assigned class name
$assigned_class = $_SESSION['assigned_class'] ?? '';

// Initialize variables for messages
$message = $_GET['msg'] ?? '';
$message_type = $_GET['type'] ?? '';

// --- Handle Delete Request ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = (int)$_GET['id'];

    // Prepare statement to select the file path
    $stmt_select_file = $conn->prepare("SELECT file_path FROM assignments WHERE id = ?");
    if ($stmt_select_file) {
        $stmt_select_file->bind_param("i", $id_to_delete);
        $stmt_select_file->execute();
        $stmt_select_file->bind_result($file_to_delete_path);
        $stmt_select_file->fetch();
        $stmt_select_file->close();
    }

    // Prepare statement to delete the assignment
    $stmt_delete = $conn->prepare("DELETE FROM assignments WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id_to_delete);
        if ($stmt_delete->execute()) {
            $message = "Assignment deleted successfully!";
            $message_type = "success";

            // Delete the associated file if it exists
            if ($file_to_delete_path && file_exists($file_to_delete_path)) {
                unlink($file_to_delete_path);
                $message .= " Associated file also deleted.";
            }
            // Redirect to avoid resubmission and pass message
            header("Location: " . strtok($_SERVER['PHP_SELF'], '?') . "?msg=" . urlencode($message) . "&type=" . urlencode($message_type));
            exit();
        } else {
            $message = "Error deleting assignment: " . $stmt_delete->error;
            $message_type = "error";
        }
        $stmt_delete->close();
    } else {
        $message = "Error preparing delete statement: " . $conn->error;
        $message_type = "error";
    }
}

// --- Handle Form Submission (Insert Data) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $category = trim($_POST['category'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $questions = trim($_POST['questions'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $assigned_class_post = trim($_POST['assigned_class'] ?? '');

    $file_path = null;
    $file_name = null;
    $upload_error = false;

    // Check if all required fields are filled
    if (empty($category) || empty($title) || empty($subject) || empty($grade) || empty($questions) || empty($deadline)) {
        $message = "All fields are required.";
        $message_type = "error";
    } else {
        // Handle file upload
        if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $original_file_name = basename($_FILES['file_upload']['name']);
            $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);
            $allowed_extensions = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
            $max_file_size = 5 * 1024 * 1024; // 5MB

            // Validate file type and size
            if (in_array(strtolower($file_extension), $allowed_extensions)) {
                if ($_FILES['file_upload']['size'] <= $max_file_size) {
                    $unique_file_name = uniqid() . '.' . $file_extension;
                    $destination_path = $upload_dir . $unique_file_name;

                    // Move the uploaded file
                    if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $destination_path)) {
                        $file_path = $destination_path;
                        $file_name = $original_file_name;
                    } else {
                        $message = "Error uploading file. Please try again.";
                        $message_type = "error";
                        $upload_error = true;
                    }
                } else {
                    $message = "File size exceeds the 5MB limit.";
                    $message_type = "error";
                    $upload_error = true;
                }
            } else {
                $message = "Invalid file type. Only PDF, DOCX, JPG, PNG are allowed.";
                $message_type = "error";
                $upload_error = true;
            }
        }

        // Insert data into the database if no upload errors
        if (!$upload_error) {
            $stmt = $conn->prepare("INSERT INTO assignments (category, title, subject, grade, file_path, file_name, questions, deadline, assigned_class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssssssss", $category, $title, $subject, $grade, $file_path, $file_name, $questions, $deadline, $assigned_class_post);
                if ($stmt->execute()) {
                    $message = "Assignment submitted successfully!";
                    $message_type = "success";
                    // Redirect to avoid resubmission and pass message
                    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message) . "&type=" . urlencode($message_type));
                    exit();
                } else {
                    $message = "Error: " . $stmt->error;
                    $message_type = "error";
                }
                $stmt->close();
            } else {
                $message = "Error preparing statement: " . $conn->error;
                $message_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Assignment Submission &amp; List</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #e9ecef;
      margin: 0;
      padding: 20px;
      color: #343a40;
    }
    .flex-container {
      display: flex;
      justify-content: space-between;
      max-width: 1200px;
      margin: 30px auto;
      gap: 24px;
      flex-wrap: wrap;
    }
    .submission-form, .assignments-list {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
      flex: 1 1 450px;
      min-width: 350px;
    }
    .submission-form:hover, .assignments-list:hover {
      transform: translateY(-2px);
    }
    h2 {
      text-align: center;
      color: #495057;
      margin-bottom: 20px;
      font-size: 1.5em;
      border-bottom: 2px solid #007bff;
      padding-bottom: 10px;
    }
    .back-dashboard {
      display: inline-block;
      margin-bottom: 20px;
      background-color: #007bff;
      color: white;
      text-decoration: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      transition: background-color 0.3s ease;
      user-select: none;
    }
    .back-dashboard:hover,
    .back-dashboard:focus {
      background-color: #0056b3;
      outline: none;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: #495057;
      font-weight: 600;
    }
    .form-group input[type="text"],
    .form-group input[type="date"],
    .form-group input[type="file"],
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ced4da;
      border-radius: 5px;
      box-sizing: border-box;
      font-size: 1em;
      font-family: inherit;
      transition: border-color 0.3s;
    }
    .form-group input[type="text"]:focus,
    .form-group input[type="date"]:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: #007bff;
      outline: none;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }
    .form-group input[type="submit"] {
      background-color: #007bff;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
      transition: background-color 0.3s ease;
    }
    .form-group input[type="submit"]:hover {
      background-color: #0056b3;
    }
    /* --- Cards Display Styles --- */
    .assignment-card {
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: relative;
      font-size: 0.9em;
      margin-bottom: 20px;
      transition: transform 0.2s;
    }
    .assignment-card:hover {
      transform: translateY(-2px);
    }
    .assignment-card h3 {
      margin-top: 0;
      color: #343a40;
      font-size: 1.2em;
      margin-bottom: 10px;
    }
    .assignment-card p {
      margin-bottom: 8px;
      color: #495057;
      line-height: 1.5;
    }
    .assignment-card p strong {
      color: #343a40;
    }
    .assignment-card .deadline {
      font-weight: bold;
      color: #e74c3c;
      font-size: 1em;
      margin-top: 15px;
      padding-top: 10px;
      border-top: 1px dashed #dee2e6;
    }
    .assignment-card .file-link {
      display: inline-block;
      margin-top: 10px;
      color: #007bff;
      text-decoration: none;
      font-weight: bold;
      word-break: break-word;
    }
    .assignment-card .file-link:hover,
    .assignment-card .file-link:focus {
      text-decoration: underline;
    }
    .delete-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.85em;
      position: absolute;
      top: 15px;
      right: 15px;
      transition: background-color 0.2s ease;
    }
    .delete-btn:hover,
    .delete-btn:focus {
      background-color: #c82333;
    }
  </style>
</head>
<body>

<div class="flex-container">
  <div class="submission-form" role="region" aria-labelledby="form-title">
    <a href="dashboard.php" class="back-dashboard" tabindex="0">Back to Dashboard</a>
    <h2 id="form-title">Assignment/Test/Project Submission</h2>
    <h3>Class: <?= htmlspecialchars($assigned_class) ?></h3>
    <form id="submissionForm" action="" method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="assigned_class" value="<?= htmlspecialchars($assigned_class) ?>" />
      <div class="form-group">
        <label for="category">Category:</label>
        <select id="category" name="category" required aria-describedby="category-note">
          <option value="">Select Category</option>
          <option value="assignment" <?= (isset($_POST['category']) && $_POST['category'] === 'assignment') ? 'selected' : ''; ?>>Assignment</option>
          <option value="test" <?= (isset($_POST['category']) && $_POST['category'] === 'test') ? 'selected' : ''; ?>>Test</option>
          <option value="project_work" <?= (isset($_POST['category']) && $_POST['category'] === 'project_work') ? 'selected' : ''; ?>>Project Work</option>
          <option value="homework" <?= (isset($_POST['category']) && $_POST['category'] === 'homework') ? 'selected' : ''; ?>>Homework</option>
          <option value="study_video" <?= (isset($_POST['category']) && $_POST['category'] === 'study_video') ? 'selected' : ''; ?>>Study Video</option>
        </select>
        <small id="category-note" class="sr-only">Select the category of your submission</small>
      </div>

      <div class="form-group">
        <label for="subject">Subject:</label>
        <select id="subject" name="subject" required aria-describedby="subject-note">
          <option value="">Select Subject</option>
          <option value="mathematics" <?= (isset($_POST['subject']) && $_POST['subject'] === 'mathematics') ? 'selected' : ''; ?>>Mathematics</option>
          <option value="science" <?= (isset($_POST['subject']) && $_POST['subject'] === 'science') ? 'selected' : ''; ?>>Science</option>
          <option value="english" <?= (isset($_POST['subject']) && $_POST['subject'] === 'english') ? 'selected' : ''; ?>>English</option>
          <option value="history" <?= (isset($_POST['subject']) && $_POST['subject'] === 'history') ? 'selected' : ''; ?>>History</option>
          <option value="computer_science" <?= (isset($_POST['subject']) && $_POST['subject'] === 'computer_science') ? 'selected' : ''; ?>>Computer Science</option>
          <option value="art" <?= (isset($_POST['subject']) && $_POST['subject'] === 'art') ? 'selected' : ''; ?>>Art</option>
          <option value="music" <?= (isset($_POST['subject']) && $_POST['subject'] === 'music') ? 'selected' : ''; ?>>Music</option>
          <option value="physical_education" <?= (isset($_POST['subject']) && $_POST['subject'] === 'physical_education') ? 'selected' : ''; ?>>Physical Education</option>
          <option value="geography" <?= (isset($_POST['subject']) && $_POST['subject'] === 'geography') ? 'selected' : ''; ?>>Geography</option>
          <option value="biology" <?= (isset($_POST['subject']) && $_POST['subject'] === 'biology') ? 'selected' : ''; ?>>Biology</option>
        </select>
        <small id="subject-note" class="sr-only">Select the subject related to your submission</small>
      </div>

      <div class="form-group">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" placeholder="e.g., Algebra Homework Set 3" value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>" required />
      </div>

      <div class="form-group">
        <label for="grade">Grade/Level:</label>
        <input type="text" id="grade" name="grade" placeholder="e.g., 10th Grade, University Level" value="<?= htmlspecialchars($_POST['grade'] ?? ''); ?>" required />
      </div>

      <div class="form-group">
        <label for="file_upload">Upload File (Optional):</label>
        <input type="file" id="file_upload" name="file_upload" aria-describedby="file-help" />
        <small id="file-help">Max file size: 5MB. Allowed formats: PDF, DOCX, JPG, PNG.</small>
      </div>

      <div class="form-group">
        <label for="questions">Questions/Instructions:</label>
        <textarea id="questions" name="questions" placeholder="Enter detailed questions or instructions for the assignment/test..." rows="5" required><?= htmlspecialchars($_POST['questions'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label for="deadline">Deadline:</label>
        <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars($_POST['deadline'] ?? ''); ?>" required />
      </div>

      <div class="form-group">
        <input type="submit" value="Submit" />
      </div>
    </form>
  </div>

  <div class="assignments-list" role="region" aria-labelledby="submitted-title">
    <h2 id="submitted-title">Submitted Assignments</h2>
    <?php
    $sql = "SELECT id, category, title, subject, grade, file_path, file_name, questions, deadline, assigned_class, created_at 
            FROM assignments 
            WHERE assigned_class = ? 
            ORDER BY deadline ASC, created_at DESC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
      $stmt->bind_param("s", $assigned_class);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          ?>
          <div class="assignment-card">
            <form action="" method="get" class="delete-form" data-id="<?= (int)$row['id']; ?>">
              <button type="button" class="delete-btn" aria-label="Delete assignment <?= htmlspecialchars($row['title']); ?>">Delete</button>
            </form>
            <h3><?= htmlspecialchars($row['title']); ?></h3>
            <p><strong>Category:</strong> <?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['category']))); ?></p>
            <p><strong>Subject:</strong> <?= htmlspecialchars($row['subject']); ?></p>
            <p><strong>Grade:</strong> <?= htmlspecialchars($row['grade']); ?></p>
            <p><strong>Class:</strong> <?= htmlspecialchars($row['assigned_class']); ?></p>
            <?php if (!empty($row['questions'])): ?>
              <p><strong>Questions/Instructions:</strong> <?= nl2br(htmlspecialchars($row['questions'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($row['file_path']) && !empty($row['file_name'])): ?>
              <p><strong>Attachment:</strong> 
                <a href="<?= htmlspecialchars($row['file_path']); ?>" target="_blank" rel="noopener noreferrer" class="file-link">
                  <?= htmlspecialchars($row['file_name']); ?>
                </a>
              </p>
            <?php endif; ?>
            <p class="deadline"><strong>Deadline:</strong> <?= date('F j, Y', strtotime($row['deadline'])); ?></p>
            <small>Submitted on: <?= date('M j, Y, H:i', strtotime($row['created_at'])); ?></small>
          </div>
          <?php
        }
      } else {
        echo '<p style="text-align: center; color:#6c757d;">No assignments submitted yet for your class.</p>';
      }
      $stmt->close();
    } else {
      echo '<p style="text-align: center; color:#6c757d;">Error fetching assignments: ' . htmlspecialchars($conn->error) . '</p>';
    }
    $conn->close();
    ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Show SweetAlert message if message exists
    const message = <?= json_encode($message); ?>;
    const messageType = <?= json_encode($message_type); ?>;

    if (message) {
      Swal.fire({
        icon: messageType === 'success' ? 'success' : 'error',
        title: messageType === 'success' ? 'Success' : 'Error',
        text: message,
        confirmButtonText: 'OK',
      });
    }

    // Attach delete confirmation to all delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
      button.addEventListener('click', () => {
        const form = button.closest('.delete-form');
        const id = form.getAttribute('data-id');

        Swal.fire({
          title: 'Are you sure you want to delete this assignment?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            // Redirect with GET parameters to trigger deletion
            window.location.href = `?action=delete&id=${id}`;
          }
        });
      });
    });
  });
</script>

</body>
</html>
