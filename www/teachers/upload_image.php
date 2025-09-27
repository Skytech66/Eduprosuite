<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase connection (must use pooler)
$host = "aws-1-eu-north-1.pooler.supabase.com"; 
$port = "6543";                                
$dbname = "postgres";                          
$user = "postgres.mqtuzltstbshtjigzujz";       
$password = "Ernestbizz..123";                 

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",  // Added sslmode=require
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
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


