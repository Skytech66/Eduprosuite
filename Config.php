<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===============================
// Supabase (PostgreSQL) Connection
// ===============================
$host = "aws-1-eu-north-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.mqtuzltstbshtjigzujz";
$password = "Ernestbizz..123";

$toastMessage = "";

try {
    // Establish PDO connection
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // ===============================
    // Handle Login Request
    // ===============================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $passwordInput = trim($_POST['password'] ?? '');

        if (!empty($username) && !empty($passwordInput)) {
            // Fetch student record
            $stmt = $pdo->prepare("SELECT * FROM student_account WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student && password_verify($passwordInput, $student['password'])) {
                // Store session variables
                $_SESSION['student_username'] = $student['username'];
                $_SESSION['student_class'] = $student['class'];

                echo "<script>
                    localStorage.setItem('toastMsg', 'Login successful! Redirecting...');
                    window.location.href = 'class.php';
                </script>";
                exit;
            } else {
                $toastMessage = "Invalid username or password.";
            }
        } else {
            $toastMessage = "Please fill in all fields.";
        }
    }
} catch (PDOException $e) {
    $toastMessage = "Database connection failed.";
}
?>
