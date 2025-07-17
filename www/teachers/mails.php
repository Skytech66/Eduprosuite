<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$messageSent = false;
$errorMsg = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $to = $_POST["to"];
    $subject = $_POST["subject"];
    $body = $_POST["message"];

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration using SSL (Port 465)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dadzieernestbizz@gmail.com';      // Replace with your Gmail address
        $mail->Password = 'myizuwngvcmeurwp';                // Replace with your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient
        $mail->setFrom('dadzieernestbizz@gmail.com', 'Teacher');
        $mail->addAddress($to);

        // Email content
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        $messageSent = true;
    } catch (Exception $e) {
        $errorMsg = "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            padding: 40px;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px #ccc;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            margin-top: 15px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
        }
        .success { color: green; text-align: center; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>

<h2 style="text-align: center;">Send Email</h2>

<?php if ($messageSent): ?>
    <p class="success">Email sent successfully!</p>
<?php elseif ($errorMsg): ?>
    <p class="error"><?= htmlspecialchars($errorMsg) ?></p>
<?php endif; ?>

<form method="POST">
    <label>To:</label>
    <input type="email" name="to" required placeholder="recipient@example.com">

    <label>Subject:</label>
    <input type="text" name="subject" required placeholder="Email subject">

    <label>Message:</label>
    <textarea name="message" rows="6" required placeholder="Your message here..."></textarea>

    <button type="submit">Send</button>
</form>

</body>
</html>
