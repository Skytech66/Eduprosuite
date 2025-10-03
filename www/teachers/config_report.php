<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase connection
$host = "aws-1-eu-north-1.pooler.supabase.com"; 
$port = "6543";                                
$dbname = "postgres";                          
$user = "postgres.mqtuzltstbshtjigzujz";       
$password = "Ernestbizz..123";                 

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_STRINGIFY_FETCHES => false // Important for BLOB data
        ]
    );
} catch (PDOException $e) {
    die("<script>showToast('Please connect to the internet');</script>");
}

// Fetch existing report config
$config = [];
$stmt = $conn->query("SELECT * FROM report_config WHERE id = 1");
if ($stmt) {
    $config = $stmt->fetch();
    // Set PDO to return BLOB as string
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
}

// Fetch classes for dropdown with teacher signatures
$classes = [];
$stmt = $conn->query("SELECT c.id, c.class_name, cts.teacher_name, cts.signature 
                      FROM classes c 
                      LEFT JOIN class_teacher_signatures cts ON c.id = cts.class_id 
                      ORDER BY c.class_name");
if ($stmt) $classes = $stmt->fetchAll();

// Helper function to parse dates safely
function parseDate($input) {
    $ts = strtotime($input);
    return $ts ? date('Y-m-d', $ts) : null;
}

// Helper function to display BLOB image - FIXED VERSION
function displayBlobImage($blobData, $alt = "Image", $class = "preview-image signature-preview") {
    if ($blobData) {
        // Check if it's already a base64 string or needs conversion
        if (is_string($blobData)) {
            // If it's a resource, convert to string
            if (is_resource($blobData)) {
                $blobData = stream_get_contents($blobData);
            }
            
            // Detect if it's already base64 encoded
            if (base64_decode($blobData, true) !== false) {
                $imageData = $blobData;
            } else {
                $imageData = base64_encode($blobData);
            }
            
            // Detect MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer(base64_decode($imageData));
            
            // Fallback to png if detection fails
            if (!$mimeType || $mimeType == 'application/octet-stream') {
                $mimeType = 'image/png';
            }
            
            $src = 'data:' . $mimeType . ';base64,' . $imageData;
            return '<img src="' . $src . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '">';
        }
    }
    return '';
}

// Alternative simple BLOB display function
function displayBlobImageSimple($blobData, $alt = "Image", $class = "preview-image signature-preview") {
    if ($blobData) {
        try {
            // Handle different BLOB formats
            if (is_resource($blobData)) {
                $blobData = stream_get_contents($blobData);
            }
            
            // Convert to base64
            $base64 = base64_encode($blobData);
            
            // Try to detect image type or default to PNG
            $imageInfo = getimagesizefromstring($blobData);
            $mimeType = $imageInfo ? $imageInfo['mime'] : 'image/png';
            
            $src = 'data:' . $mimeType . ';base64,' . $base64;
            return '<img src="' . $src . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . '" onerror="this.style.display=\'none\'">';
        } catch (Exception $e) {
            return '<div class="image-error">Error loading image</div>';
        }
    }
    return '';
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_name     = !empty($_POST['school_name']) ? $_POST['school_name'] : null;
    $po_box          = !empty($_POST['po_box']) ? $_POST['po_box'] : null;
    $address         = !empty($_POST['address']) ? $_POST['address'] : null;
    $term_begins     = !empty($_POST['term_begins']) ? parseDate($_POST['term_begins']) : null;
    $term_ends       = !empty($_POST['term_ends']) ? parseDate($_POST['term_ends']) : null;
    $management_info = !empty($_POST['management_info']) ? $_POST['management_info'] : null;

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Handle logo upload (keep as file path)
    $school_logo = $config['school_logo'] ?? null;
    if (!empty($_FILES['school_logo']['name'])) {
        $school_logo = $uploadDir . "logo_" . time() . "_" . basename($_FILES['school_logo']['name']);
        move_uploaded_file($_FILES['school_logo']['tmp_name'], $school_logo);
    }

    // Handle head teacher signature as BLOB
    $head_teacher_sig_blob = $config['head_teacher_sig'] ?? null;
    if (!empty($_FILES['head_teacher_sig']['tmp_name'])) {
        $head_teacher_sig_blob = file_get_contents($_FILES['head_teacher_sig']['tmp_name']);
    }

    // Insert or update report config
    $sql = "INSERT INTO report_config 
            (id, school_name, po_box, address, term_begins, term_ends, school_logo, head_teacher_sig, management_info)
            VALUES (1, :school_name, :po_box, :address, :term_begins, :term_ends, :school_logo, :head_teacher_sig, :management_info)
            ON CONFLICT (id) 
            DO UPDATE SET 
                school_name = EXCLUDED.school_name,
                po_box = EXCLUDED.po_box,
                address = EXCLUDED.address,
                term_begins = EXCLUDED.term_begins,
                term_ends = EXCLUDED.term_ends,
                school_logo = EXCLUDED.school_logo,
                head_teacher_sig = EXCLUDED.head_teacher_sig,
                management_info = EXCLUDED.management_info";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':school_name'      => $school_name,
        ':po_box'           => $po_box,
        ':address'          => $address,
        ':term_begins'      => $term_begins,
        ':term_ends'        => $term_ends,
        ':school_logo'      => $school_logo,
        ':head_teacher_sig' => $head_teacher_sig_blob,
        ':management_info'  => $management_info
    ]);

    // Handle class teacher signature if file uploaded
    if (!empty($_POST['class_id']) && !empty($_POST['teacher_name']) && !empty($_FILES['class_teacher_sig']['tmp_name'])) {
        $class_id     = $_POST['class_id'];
        $teacher_name = $_POST['teacher_name'];
        $signature    = file_get_contents($_FILES['class_teacher_sig']['tmp_name']); // BLOB

        $sql = "INSERT INTO class_teacher_signatures (class_id, teacher_name, signature)
                VALUES (:class_id, :teacher_name, :signature)
                ON CONFLICT (class_id) DO UPDATE SET
                    teacher_name = EXCLUDED.teacher_name,
                    signature = EXCLUDED.signature";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':teacher_name', $teacher_name);
        $stmt->bindParam(':signature', $signature, PDO::PARAM_LOB);
        $stmt->execute();
    }

    // Redirect to avoid form resubmission
    header("Location: config_report.php?success=1");
    exit();
}

// Debug function to check BLOB data
function debugBlobData($blobData, $name = "Unknown") {
    if ($blobData) {
        error_log("BLOB Data for $name: " . gettype($blobData) . ", length: " . strlen($blobData));
        if (is_resource($blobData)) {
            error_log("It's a resource");
        }
    } else {
        error_log("BLOB Data for $name: NULL or empty");
    }
}

// Debug the BLOB data
if (!empty($config['head_teacher_sig'])) {
    debugBlobData($config['head_teacher_sig'], "Head Teacher Signature");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card Configuration | School Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            color: var(--dark);
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 50%;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: white;
            background: var(--primary);
            padding: 12px 20px;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .back-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 25px;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .card-body {
            padding: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 15px;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .file-upload-wrapper {
            position: relative;
            margin-bottom: 15px;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 16px;
            background: var(--light);
            border: 2px dashed #ced4da;
            border-radius: var(--border-radius);
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            background: rgba(67, 97, 238, 0.05);
        }

        .file-upload-label i {
            color: var(--primary);
            font-size: 18px;
        }

        .signature-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 10px;
        }

        .signature-box {
            background: var(--light);
            padding: 20px;
            border-radius: var(--border-radius);
            border: 1px dashed #ced4da;
        }

        .preview-container {
            margin-top: 15px;
            text-align: center;
        }

        .preview-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--gray);
            font-size: 14px;
        }

        .preview-image {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--light-gray);
        }

        .logo-preview {
            max-height: 120px;
        }

        .signature-preview {
            max-height: 100px;
        }

        .image-error {
            color: #e74c3c;
            font-style: italic;
            padding: 10px;
            background: #ffeaea;
            border-radius: 5px;
            text-align: center;
        }

        .class-teacher-list {
            margin-top: 20px;
        }

        .class-teacher-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: white;
            border-radius: var(--border-radius);
            margin-bottom: 10px;
            border: 1px solid var(--light-gray);
        }

        .class-teacher-info {
            flex: 1;
        }

        .class-teacher-name {
            font-weight: 600;
            color: var(--dark);
        }

        .class-teacher-class {
            font-size: 14px;
            color: var(--gray);
        }

        .class-teacher-signature {
            max-height: 50px;
            margin-left: 15px;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            justify-content: center;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary {
            background: var(--secondary);
            color: white;
            box-shadow: 0 4px 12px rgba(114, 9, 183, 0.3);
        }

        .btn-secondary:hover {
            background: #6511a0;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(114, 9, 183, 0.4);
        }

        .alert {
            padding: 16px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(76, 201, 240, 0.15);
            color: #0c5460;
            border-left: 4px solid var(--success);
        }

        #toast {
            visibility: hidden;
            min-width: 280px;
            margin-left: -140px;
            background-color: #e74c3c;
            color: #fff;
            text-align: center;
            border-radius: var(--border-radius);
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        #toast.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .class-teacher-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .class-teacher-signature {
                margin-left: 0;
                margin-top: 10px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .card-header {
                padding: 15px 20px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .form-control {
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1 class="page-title">
                <i class="fas fa-cogs"></i>
                Report Card Configuration
            </h1>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> 
                Back to Dashboard
            </a>
        </header>

        <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Configuration saved successfully!
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- School Information Section -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-school"></i>
                    <h3>School Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="school_name">School Name</label>
                            <input type="text" id="school_name" name="school_name" class="form-control" 
                                   value="<?= htmlspecialchars($config['school_name'] ?? '') ?>" 
                                   placeholder="Enter school name">
                        </div>
                        <div class="form-group">
                            <label for="po_box">P.O. Box</label>
                            <input type="text" id="po_box" name="po_box" class="form-control" 
                                   value="<?= htmlspecialchars($config['po_box'] ?? '') ?>" 
                                   placeholder="Enter P.O. Box">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" class="form-control" 
                                   value="<?= htmlspecialchars($config['address'] ?? '') ?>" 
                                   placeholder="Enter school address">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Term Information Section -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Term Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="term_begins">Term Begins</label>
                            <input type="date" id="term_begins" name="term_begins" class="form-control" 
                                   value="<?= htmlspecialchars($config['term_begins'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="term_ends">Term Ends</label>
                            <input type="date" id="term_ends" name="term_ends" class="form-control" 
                                   value="<?= htmlspecialchars($config['term_ends'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding Section -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-palette"></i>
                    <h3>School Branding</h3>
                </div>
                <div class="card-body">
                    <div class="signature-section">
                        <div class="signature-box">
                            <label>School Logo</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Choose Logo File</span>
                                </div>
                                <input type="file" name="school_logo" accept="image/*">
                            </div>
                            <small style="color: var(--gray); display: block; margin-top: 5px;">
                                Recommended: Square logo, max 300x300px
                            </small>
                            <?php if (!empty($config['school_logo'])): ?>
                                <div class="preview-container">
                                    <div class="preview-title">Current Logo:</div>
                                    <img src="<?= $config['school_logo'] ?>" alt="School Logo" class="preview-image logo-preview">
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="signature-box">
                            <label>Head Teacher Signature</label>
                            <div class="file-upload-wrapper">
                                <div class="file-upload-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Choose Signature File</span>
                                </div>
                                <input type="file" name="head_teacher_sig" accept="image/*">
                            </div>
                            <small style="color: var(--gray); display: block; margin-top: 5px;">
                                Recommended: Transparent PNG, max 200x80px
                            </small>
                            <?php if (!empty($config['head_teacher_sig'])): ?>
                                <div class="preview-container">
                                    <div class="preview-title">Current Signature:</div>
                                    <?= displayBlobImageSimple($config['head_teacher_sig'], "Head Teacher Signature") ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Class Teacher Signatures -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-signature"></i>
                    <h3>Class Teacher Signatures</h3>
                </div>
                <div class="card-body">
                    <div class="signature-section">
                        <div class="signature-box">
                            <div class="form-group">
                                <label for="class_id">Select Class</label>
                                <select id="class_id" name="class_id" class="form-control">
                                    <option value="">-- Choose Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="teacher_name">Teacher Name</label>
                                <input type="text" id="teacher_name" name="teacher_name" class="form-control" 
                                       placeholder="Enter teacher name">
                            </div>
                            <div class="form-group">
                                <label>Upload Signature</label>
                                <div class="file-upload-wrapper">
                                    <div class="file-upload-label">
                                        <i class="fas fa-upload"></i>
                                        <span>Choose Signature File</span>
                                    </div>
                                    <input type="file" name="class_teacher_sig" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display existing class teacher signatures -->
                    <?php if (!empty($classes) && count(array_filter($classes, function($c) { return !empty($c['signature']); })) > 0): ?>
                    <div class="class-teacher-list">
                        <h4 style="margin-bottom: 15px; color: var(--dark);">Existing Class Teacher Signatures</h4>
                        <?php foreach ($classes as $class): ?>
                            <?php if (!empty($class['signature']) && !empty($class['teacher_name'])): ?>
                                <div class="class-teacher-item">
                                    <div class="class-teacher-info">
                                        <div class="class-teacher-name"><?= htmlspecialchars($class['teacher_name']) ?></div>
                                        <div class="class-teacher-class"><?= htmlspecialchars($class['class_name']) ?></div>
                                    </div>
                                    <div class="class-teacher-signature">
                                        <?= displayBlobImageSimple($class['signature'], "Class Teacher Signature", "preview-image signature-preview") ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Management Information Section -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Additional Management Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <textarea name="management_info" class="form-control" 
                                  placeholder="Enter any additional management information..."><?= htmlspecialchars($config['management_info'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location='configure_class_report.php'">
                    <i class="fas fa-edit"></i> Configure Class Report
                </button>
            </div>
        </form>
    </div>

    <div id="toast"></div>

    <script>
        function showToast(message) {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = "show";
            setTimeout(() => toast.className = toast.className.replace("show", ""), 3000);
        }

        // File input change handlers
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.previousElementSibling;
                if (this.files.length > 0) {
                    label.innerHTML = `<i class="fas fa-check"></i> ${this.files[0].name}`;
                    label.style.borderColor = 'var(--primary)';
                    label.style.background = 'rgba(67, 97, 238, 0.1)';
                } else {
                    label.innerHTML = `<i class="fas fa-upload"></i> Choose File`;
                    label.style.borderColor = '#ced4da';
                    label.style.background = 'var(--light)';
                }
            });
        });

        // Auto-fill teacher name when class is selected
        document.getElementById('class_id').addEventListener('change', function() {
            const classId = this.value;
            const teacherNameInput = document.getElementById('teacher_name');
            
            if (classId) {
                // In a real application, you might fetch this data via AJAX
                // For now, we'll just clear the field
                teacherNameInput.value = '';
                
                <?php foreach ($classes as $class): ?>
                    if (classId == '<?= $class['id'] ?>' && '<?= $class['teacher_name'] ?? '' ?>') {
                        teacherNameInput.value = '<?= $class['teacher_name'] ?>';
                    }
                <?php endforeach; ?>
            }
        });

        document.querySelector("form").addEventListener("submit", function(e){
            const fields = [
                {name: "school_name", label: "School Name"},
                {name: "po_box", label: "P.O. Box"},
                {name: "address", label: "Address"},
                {name: "term_begins", label: "Term Begins"},
                {name: "term_ends", label: "Term Ends"},
                {name: "management_info", label: "Management Info"}
            ];
            
            let hasEmptyFields = false;
            
            fields.forEach(f => {
                const input = document.querySelector(`[name="${f.name}"]`);
                if(input && input.value.trim() === "") {
                    hasEmptyFields = true;
                }
            });
            
            if(hasEmptyFields) {
                showToast("Some fields are empty. You can ignore or fill them.");
            }
        });

        window.addEventListener('offline', () => showToast('You are offline! Please connect to the internet.'));
        window.addEventListener('online', () => showToast('Back online!'));
    </script>
</body>
</html>
