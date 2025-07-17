<?php
include "../include/functions.php"; // Include your functions file
$conn = db_conn(); // Establish the database connection

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images']) && isset($_POST['iduser'])) {
    $studentIds = $_POST['iduser'];
    $uploadDir = 'uploads/'; // Directory where images will be stored

    // Check if the uploads directory exists, if not, create it
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
            $studentId = $studentIds[$index];
            $imageFileType = strtolower(pathinfo($_FILES['images']['name'][$index], PATHINFO_EXTENSION));

            // Validate the uploaded file
            $check = getimagesize($tmpName);
            if ($check === false) {
                $response['message'] = "File is not an image for student ID: $studentId.";
                echo json_encode($response);
                continue;
            }

            // Check file size (limit to 9MB or adjust as needed)
            if ($_FILES['images']['size'][$index] > 9000000) {
                $response['message'] = "Sorry, your file is too large for student ID: $studentId.";
                echo json_encode($response);
                continue;
            }

            // Allow certain file formats
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                $response['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for student ID: $studentId.";
                echo json_encode($response);
                continue;
            }

            // Generate a unique filename
            $newFileName = uniqid($studentId . '_', true) . '.' . $imageFileType;
            $uploadFile = $uploadDir . $newFileName;

            // Attempt to move the uploaded file to the uploads directory
            if (move_uploaded_file($tmpName, $uploadFile)) {
                // Prepare the SQL statement to update the student's photo path
                $updateQuery = "UPDATE student SET photo = :photo WHERE id = :id";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bindValue(':photo', $uploadFile, SQLITE3_TEXT);
                $stmt->bindValue(':id', $studentId, SQLITE3_INTEGER);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "The file has been uploaded and the database has been updated for student ID: $studentId.";
                } else {
                    $response['message'] = "Database update failed for student ID: $studentId.";
                }
            } else {
                $response['message'] = "Sorry, there was an error uploading your file for student ID: $studentId.";
            }
        }
    }
} else {
    $response['message'] = "Invalid request.";
}

echo json_encode($response);
$conn->close();
?>