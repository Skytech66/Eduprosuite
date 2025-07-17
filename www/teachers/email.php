<?php
// Enable error reporting
error_reporting(E_ALL); // Report all types of errors
ini_set('display_errors', 1); // Display errors on the screen

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Start session to get assigned class
session_start();

// Check if the user is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

$messageSent = false;
$errorMsg = '';

// Database connection
require 'config.php'; // Include your database connection file

// Fetch unique classes from the students table for the dropdown
$classes = [];
$stmt = $conn->prepare("SELECT DISTINCT class FROM students");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $classes[] = $row['class'];
}
$stmt->close();

$emails = [];
$assignedClass = $_POST['class'] ?? ''; // Get selected class from the form

// Fetch emails of students in the selected class
if ($assignedClass) {
    $stmt = $conn->prepare("SELECT email FROM students WHERE class = ?");
    $stmt->bind_param("s", $assignedClass);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row['email'];
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['send_email'])) {
    $subject = $_POST["subject"];
    $body = $_POST["message"];
    $pdfPath = $_FILES['attachment']['tmp_name'];
    $pdfName = $_FILES['attachment']['name'];
    $recipients = $_POST['recipients'] ?? []; // Get selected recipients

    // Include the selected class in the email body
    $body .= "\n\nClass: " . $assignedClass;

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration using SSL (Port 465)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dadzieernestbizz@gmail.com';
        $mail->Password = 'myizuwngvcmeurwp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender
        $mail->setFrom('your_email@gmail.com', 'Adinkra International School');

        // Add recipients
        foreach ($recipients as $to) {
            $mail->addAddress($to);
        }

        // Email content
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Attach the PDF if uploaded
        if (is_uploaded_file($pdfPath)) { // Corrected missing parenthesis
            $mail->addAttachment($pdfPath, $pdfName);
        }

        $mail->send();
        $messageSent = true;
    } catch (Exception $e) {
        $errorMsg = "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Bulk Email | Adinkra International School</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --dark: #1e1e1e;
            --light: #f8f9fa;
            --success: #4bb543;
            --error: #ff3333;
            --border-radius: 8px;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            padding: 0;
            margin: 0;
        }

        .container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.5rem 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-weight: 600;
            font-size: 1.8rem;
        }

        .header i {
            font-size: 2rem;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        select, input, textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border: 2px dashed #ddd;
            border-radius: var(--border-radius);
            background-color: #f9f9f9;
            text-align: center;
            transition: var(--transition);
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        .file-upload-label i {
            margin-right: 0.5rem;
            color: var(--primary);
        }

        .file-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        .recipients-container {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            padding: 1rem;
            margin-top: 0.5rem;
        }

        .recipient-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .recipient-item:last-child {
            border-bottom: none;
        }

        .recipient-item input {
            width: auto;
            margin-right: 1rem;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .select-all {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .select-all input {
            width: auto;
            margin-right: 0.5rem;
        }

        .select-all label {
            margin-bottom: 0;
            font-weight: 400;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
            }
            
            .header h1 {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope-open-text"></i> Send Bulk Email</h1>
            <p>Adinkra International School</p>
        </div>

        <div class="card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="class"><i class="fas fa-users"></i> Select Class</label>
                    <select name="class" id="class" required onchange="this.form.submit()">
                        <option value="">-- Select a Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= htmlspecialchars($class) ?>" <?= ($class === $assignedClass) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($assignedClass): ?>
                    <div class="form-group">
                        <label for="subject"><i class="fas fa-tag"></i> Subject</label>
                        <input type="text" name="subject" id="subject" required placeholder="Enter email subject">
                    </div>

                    <div class="form-group">
                        <label for="message"><i class="fas fa-comment-alt"></i> Message</label>
                        <textarea name="message" id="message" required placeholder="Type your message here..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-paperclip"></i> Attachment (PDF)</label>
                        <div class="file-upload">
                            <input type="file" name="attachment" id="attachment" class="file-upload-input" accept="application/pdf" onchange="updateFileName(this)">
                            <label for="attachment" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose a PDF file or drag it here</span>
                            </label>
                            <div id="file-name" class="file-name"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-user-graduate"></i> Recipients</label>
                        <?php if (!empty($emails)): ?>
                            <div class="select-all">
                                <input type="checkbox" id="select-all">
                                <label for="select-all">Select All Students</label>
                            </div>
                            <div class="recipients-container">
                                <?php foreach ($emails as $email): ?>
                                    <div class="recipient-item">
                                        <input type="checkbox" name="recipients[]" value="<?= htmlspecialchars($email) ?>" id="<?= htmlspecialchars($email) ?>">
                                        <label for="<?= htmlspecialchars($email) ?>" style="display: inline;"><?= htmlspecialchars($email) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No students found for the selected class.</p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="send_email" class="btn btn-block">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        // Update file name display
        function updateFileName(input) {
            const fileNameDisplay = document.getElementById('file-name');
            if (input.files.length > 0) {
                fileNameDisplay.textContent = 'Selected file: ' + input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }

        // Select all recipients checkbox
        document.getElementById('select-all')?.addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.recipient-item input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        });

        // Check if the PHP variables are set and display SweetAlert2 modals
        <?php if ($messageSent): ?>
            Swal.fire({
                title: 'Success!',
                text: 'Email sent successfully!',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: 'var(--primary)'
            });
        <?php elseif ($errorMsg): ?>
            Swal.fire({
                title: 'Error!',
                text: '<?= htmlspecialchars($errorMsg) ?>',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: 'var(--error)'
            });
        <?php endif; ?>
    </script>
</body>
</html>
