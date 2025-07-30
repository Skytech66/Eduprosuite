<?php
// upload_image.php
$host = "dpg-d20bls6mcj7s73avna10-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "school_523q";
$user = "school_523q_user";
$password = "05A4cQnogC1qETghafnFsKNYUxYIRwrv";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images']) && isset($_POST['iduser'])) {
    $images = $_FILES['images'];
    $students = $_POST['iduser'];

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($students as $index => $studentId) {
        if (!empty($images['tmp_name'][$index])) {
            $tmpName = $images['tmp_name'][$index];
            $originalName = basename($images['name'][$index]);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFileName = uniqid('img_') . '.' . $ext;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $stmt = $conn->prepare("UPDATE marks SET photo = :photo WHERE student = :student");
                $stmt->execute([
                    'photo' => $destination,
                    'student' => $studentId
                ]);
            }
        }
    }

    echo "<script>alert('Images uploaded successfully.'); window.location.href='view_students.php';</script>";
} else {
    echo "<script>alert('Invalid submission. Please try again.'); window.location.href='view_students.php';</script>";
}
?>
