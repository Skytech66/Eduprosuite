<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dadzieernestbizz@gmail.com';
        $mail->Password = 'myizuwngvcmeurwp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('dadzieernestbizz@gmail.com', 'EduPro Assistant');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(false);
        $mail->Subject = 'Message from EduPro Assistant';
        $mail->Body    = $message;

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
    }
    exit;
}
?>
