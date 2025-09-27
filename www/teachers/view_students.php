<?php
session_start();
include 'config.php';

// Fetch classes from student_entries table for dropdown
$query = "SELECT DISTINCT class FROM student_entries ORDER BY class ASC";
$result = $conn->query($query);
$classes = [];
if ($result) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $classes[] = $row['class'];
}

// Handle image upload and student selection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['student_photo']) && isset($_POST['student_id'])) {
        $student_id = $_POST['student_id'];
        $photo = $_FILES['student_photo'];
        
        // Validate and upload image
        $upload_dir = 'uploads/photos/';
        $photo_name = basename($photo['name']);
        $target_file = $upload_dir . $photo_name;
        
        // Validate file type (optional)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($photo['type'], $allowed_types)) {
            $_SESSION['error'] = 'Invalid file type. Please upload a JPEG, PNG, or GIF image.';
            header('Location: upload_student_image.php');
            exit;
        }
        
        if (move_uploaded_file($photo['tmp_name'], $target_file)) {
            // Update marks table with image path
            $update_query = "UPDATE marks SET photo = :photo WHERE student_id = :student_id";
            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':photo', $target_file);
            $stmt->bindParam(':student_id', $student_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Image uploaded successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update the photo in marks table.';
            }
        } else {
            $_SESSION['error'] = 'Failed to upload the image.';
        }
    }
}

// Fetch students when class is selected via AJAX
if (isset($_GET['class'])) {
    $class = $_GET['class'];
    
    $query = "SELECT student_id, student_name FROM student_entries WHERE class = :class ORDER BY student_name ASC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':class', $class);
    $stmt->execute();
    
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($students);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Student Image</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Upload Student Image</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="upload_student_image.php" method="POST" enctype="multipart/form-data">
        <label for="class">Select Class:</label>
        <select name="class" id="class" required>
            <option value="">-- Select Class --</option>
            <?php foreach ($classes as $class): ?>
                <option value="<?php echo htmlspecialchars($class); ?>"><?php echo htmlspecialchars($class); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="student_id">Select Student:</label>
        <select name="student_id" id="student_id" required>
            <option value="">-- Select Student --</option>
        </select><br><br>

        <label for="student_photo">Upload Photo:</label>
        <input type="file" name="student_photo" id="student_photo" accept="image/*" required><br><br>

        <button type="submit">Upload Image</button>
    </form>

    <script>
        // Fetch students based on selected class
        document.getElementById('class').addEventListener('change', function () {
            let className = this.value;
            let studentSelect = document.getElementById('student_id');
            studentSelect.innerHTML = '<option value="">-- Select Student --</option>'; // Clear previous students

            if (className) {
                fetch('upload_student_image.php?class=' + className)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(student => {
                            let option = document.createElement('option');
                            option.value = student.student_id;
                            option.textContent = student.student_name;
                            studentSelect.appendChild(option);
                        });
                    });
            }
        });
    </script>

</body>
</html>
