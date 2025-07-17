<?php
session_start();
$dbFile = 'students_records.db';

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT * FROM teachers WHERE email = ?");
        $stmt->execute([$email]);
        $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($teacher && password_verify($password, $teacher['password'])) {
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['teacher_name'] = $teacher['name'];
            $_SESSION['assigned_class'] = $teacher['assigned_class'];

            header("Location: dashboard.php");
            exit;
        } else {
            header("Location: login.php?error=1");
            exit;
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
