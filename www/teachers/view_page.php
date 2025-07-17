<?php
include "../include/functions.php"; // Include your functions file
$conn = db_conn(); // Establish the database connection
include "header.php"; // Include the header file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students by Class</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e9ecef; /* Light gray background */
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .ai-header {
            background: linear-gradient(135deg, #4F46E5 0%, #10B981 100%);
            color: black; /* Set text color to black */
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .subject-title {
            font-size: 2.5em; /* Increase font size */
            font-weight: bold; /* Make the text bold */
            color: black; /* Set text color to black */
            margin: 0; /* Remove default margin */
        }
        .ai-header i {
            margin-right: 8px;
            color: white;
            font-size: 1.5em; /* Increase icon size */
        }
        .ai-header p {
            margin: 0; /* Remove default margin */
            color: white; /* Set paragraph text color to white */
        }
        /* Other existing styles */
        h1, h2 {
            color: #343a40; /* Dark gray */
            text-align: center;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #495057; /* Medium gray */
        }
        select, input[type="submit"], .upload-btn {
            padding: 15px; /* Increased padding for better touch targets */
            margin-top: 10px;
            border: 1px solid #ced4da; /* Light gray */
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }
        select:focus, input[type="submit"]:focus, .upload-btn:focus {
            outline: none;
            border-color: #007bff; /* Blue */
        }
        .student-list {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .student-card {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #dee2e6; /* Light gray */
            border-radius: 5px;
            background-color: #f8f9fa; /* Light background */
            width: calc(50% - 15px); /* Two cards per row */
            position: relative;
            transition: transform 0.2s;
        }
        .student-card:hover {
            transform: scale(1.02);
        }
        .student-name {
            flex: 1;
            font-size: 18px;
            margin-right: 10px;
            color: #212529; /* Darker gray */
        }
        .student-image {
            width: 60px;
            height: 60px;
            margin-right: 15px;
            border-radius: 50%; /* Circular images */
            border: 2px solid #007bff; /* Blue border */
        }
        .upload-btn {
            background-color: #007bff; /* Blue */
            color: white;
            border: none;
            transition: background-color 0.3s;
        }
        .upload-btn:hover {
            background-color: #0056b3; /* Darker blue */
        }
        .drop-zone {
            border:            2px dashed #007bff; /* Blue */
            padding: 20px;
            text-align: center;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .drop-zone.hover {
            background-color: #e9f7ff; /* Light blue */
        }
        .total-count {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
        }
        @media (max-width: 600px) {
            .container {
                padding: 15px; /* Reduce padding on small screens */
            }
            .ai-header {
                padding: 15px; /* Adjust header padding */
            }
            .subject-title {
                font-size: 2em; /* Decrease font size for mobile */
            }
            label {
                font-size: 14px; /* Smaller font size for labels */
            }
            select, input[type="submit"], .upload-btn {
                font-size: 14px; /* Smaller font size for buttons */
            }
            .student-card {
                width: 100%; /* Full width for student cards */
                flex-direction: column; /* Stack elements vertically */
                align-items: flex-start; /* Align items to the start */
            }
            .student-image {
                width: 50px; /* Smaller image size */
                height: 50px;
            }
            .student-name {
                font-size: 16px; /* Smaller font size for names */
            }
            .student-list {
                flex-direction: column; /* Stack student cards vertically */
                align-items: stretch; /* Stretch to full width */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="ai-header">
            <h2 class="subject-title"><i class="fas fa-users"></i> View Students by Class</h2>
            <p class="mb-0"><i class="fas fa-chalkboard-teacher"></i> Select a class to view students</p>
        </div>

        <form method="POST" action="">
            <label for="class"><i class="fas fa-chalkboard-teacher"></i> Select Class:</label>
            <select name="class" id="class" required>
                <option value="">--Select Class--</option>
                <?php
                // Fetch distinct classes for the dropdown
                $classQuery = "SELECT DISTINCT class FROM student WHERE class IS NOT NULL AND class != ''";
                $classResult = $conn->query($classQuery);

                if ($classResult) {
                    while ($row = $classResult->fetchArray(SQLITE3_ASSOC)) {
                        echo '<option value="' . htmlspecialchars($row['class']) . '">' . htmlspecialchars($row['class']) . '</option>';
                    }
                } else {
                    echo '<option value="">No classes available</option>';
                }
                ?>
            </select>
            <input type="submit" value="View Students" class="btn">
        </form>

        <?php
        // If a class is selected, fetch and display students
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class'])) {
            $selectedClass = $_POST['class'];
            
            // Prepare the statement
            $studentQuery = "SELECT id, name, photo FROM student WHERE class = :class";
            $stmt = $conn->prepare($studentQuery);
            $stmt->bindValue(':class', $selectedClass, SQLITE3_TEXT);
            $result = $stmt->execute();

            echo "<h2>Students in Class: " . htmlspecialchars($selectedClass) . "</h2>";
            if ($result) {
                $foundStudents = false;
                $studentCount = 0; // Initialize student count
                echo "<form action='upload_image.php' method='POST' enctype='multipart/form-data' id='uploadForm'>";
                echo "<input type='hidden' name='class' value='" . htmlspecialchars($selectedClass) . "'>";
                echo "<ul class='student-list'>";
                while ($student = $result->fetchArray(SQLITE3_ASSOC)) {
                    $photoPath = htmlspecialchars($student['photo']);
                    echo "<li class='student-card'>";
                    echo "<img src='" . ($photoPath ? $photoPath : 'placeholder.png') . "' alt='Student Image' class='student-image'>";
                    echo "<span class='student-name'>" . htmlspecialchars($student['name']) . "</span>";
                    echo "<input type='file' name='images[]' accept='image/*' style='margin-left: 10px;' onchange='previewImage(this)'>";
                    echo "<input type='hidden' name='iduser[]' value='" . htmlspecialchars($student['id']) . "'>";                    echo "</li>";
                    $foundStudents = true;
                    $studentCount++; // Increment count for each student found
                }
                echo "</ul>";
                echo "<div class='drop-zone' id='dropZone'>Drag and drop images here or click to upload</div>";
                echo "<input type='submit' value='Upload Selected Images' class='upload-btn'>";
                echo "</form>";

                // Display total number of students found
                if ($foundStudents) {
                    echo "<div class='total-count'>Total Students: " . $studentCount . "</div>";
                } else {
                    echo "<p>No students found in this class.</p>";
                }
            } else {
                echo "<p>Error executing query.</p>";
            }
        }

        $conn->close();
        ?>
    </div>

    <script>
        // Image preview function
        function previewImage(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '60px'; // Set a fixed width for preview
                    img.style.height = '60px';
                    img.style.borderRadius = '50%'; // Circular preview
                    input.parentNode.insertBefore(img, input);
                }
                reader.readAsDataURL(file);
            }
        }

        // Drag and drop functionality
        const dropZone = document.getElementById('dropZone');
        dropZone.addEventListener('click', () => {
            document.querySelectorAll('input[type="file"]').forEach(input => input.click());
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('hover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('hover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('hover');
            const files = e.dataTransfer.files;
            const inputs = document.querySelectorAll('input[type="file"]');
            inputs.forEach((input, index) => {
                if (files[index]) {
                    input.files = files;
                    previewImage(input);
                }
            });
        });
    </script>
</body>
</html>
					