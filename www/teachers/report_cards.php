<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

require_once __DIR__ . '/phpword/src/PhpWord/Settings.php';
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// Database connection
$host = "aws-1-eu-north-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.mqtuzltstbshtjigzujz";
$password = "Ernestbizz..123";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to convert number to ordinal
function ordinal($number) {
    $number = (int)$number;
    if (!in_array(($number % 100), [11, 12, 13])) {
        switch ($number % 10) {
            case 1: return $number . 'st';
            case 2: return $number . 'nd';
            case 3: return $number . 'rd';
        }
    }
    return $number . 'th';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class = $_POST['askclass'];
    $exam = $_POST['exam'];

    // Fetch all student data
    $sql = "SELECT admission_number, student, subject, class_score, exam_score, remarks, position 
            FROM marks 
            WHERE class = :class AND examname = :exam 
            ORDER BY admission_number ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':class', $class, PDO::PARAM_STR);
    $stmt->bindValue(':exam', $exam, PDO::PARAM_STR);
    $stmt->execute();

    $students = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $admno = $row["admission_number"];
        if (!isset($students[$admno])) {
            $students[$admno]['name'] = $row["student"];
            $students[$admno]['marks'] = [];
        }

        $students[$admno]['marks'][] = [
            'subject' => $row["subject"],
            'class_score' => (int)$row["class_score"],
            'exam_score' => (int)$row["exam_score"],
            'total' => (int)$row["class_score"] + (int)$row["exam_score"],
            'remarks' => $row["remarks"],
            'position' => $row["position"]
        ];
    }

    // Fetch school config
    $configSql = "SELECT school_name, po_box, address, term_ends, term_begins FROM config_report LIMIT 1";
    $configStmt = $conn->query($configSql);
    $config = $configStmt->fetch(PDO::FETCH_ASSOC);
    if (!$config) $config = [];

    // Create Word document
    $phpWord = new PhpWord();

    $sectionStyle = ['marginTop' => 600, 'marginBottom' => 600];
    $section = $phpWord->addSection($sectionStyle);

    foreach ($students as $admno => $data) {
        // School Header
        if (file_exists('gat.png')) {
            $section->addImage('gat.png', ['width' => 80, 'height' => 80, 'alignment' => 'left']);
        }
        $section->addText(
            isset($config['school_name']) ? strtoupper($config['school_name']) : 'STARS ON EARTH ACADEMY',
            ['bold' => true, 'size' => 28], ['alignment' => 'center']
        );
        $section->addText(
            'LOCATION: ABOKOBI-AKPORMAN, ACCRA',
            ['size' => 14], ['alignment' => 'center']
        );
        $section->addText(
            'TEL: +233246484366 / +233244457834',
            ['size' => 14], ['alignment' => 'center']
        );
        $section->addText(
            'EMAIL: starsonearth@gmail.com',
            ['size' => 14], ['alignment' => 'center']
        );
        $section->addTextBreak(1);
        $section->addText('Terminal Report (Upper Primary)', ['italic' => true, 'size' => 11], ['alignment' => 'center']);
        $section->addTextBreak(1);

        // Student Info
        $section->addText('STUDENT: ' . strtoupper($data['name']), ['bold' => true, 'size' => 16]);
        $section->addText('CLASS: ' . $class);
        $section->addText('EXAM: ' . $exam);
        $section->addText('NEXT TERM BEGINS: ' . ($config['term_begins'] ?? 'January 8, 2025'));
        $overallPosition = isset($data['marks'][0]['position']) && is_numeric($data['marks'][0]['position']) ?
            ordinal($data['marks'][0]['position']) : 'N/A';
        $section->addText('POSITION: ' . $overallPosition);
        $section->addTextBreak(1);

        // Marks Table
        $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 50];
        $phpWord->addTableStyle('MarksTable', $tableStyle);
        $table = $section->addTable('MarksTable');

        $headers = ['SUBJECT', 'CLASS SCORE 50%', 'EXAM SCORE 50%', 'TOTAL 100%', 'REMARKS', 'INITIALS'];
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(2000)->addText($header, ['bold' => true], ['alignment' => 'center']);
        }

        foreach ($data['marks'] as $mark) {
            $table->addRow();
            $table->addCell(2000)->addText($mark['subject'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText($mark['class_score'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText($mark['exam_score'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText($mark['total'], ['bold' => true], ['alignment' => 'center']);
            $table->addCell(2500)->addText($mark['remarks'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText(is_numeric($mark['position']) ? ordinal($mark['position']) : 'N/A', [], ['alignment' => 'center']);
        }

        // Attendance, conduct, promotion
        $section->addTextBreak(1);
        $section->addText('ATTENDANCE: .................. OUT OF: .................. PROMOTED TO: ..................');
        $section->addText('CONDUCT/TEMPERAMENT: _________________________________________________________________');
        $section->addText('ATTITUDE TOWARDS WORK: ______________________________________________________________');
        $section->addText('INTEREST: ____________________________________________________________________________');
        $section->addText('CLASS TEACHER\'S REMARKS: _____________________________________   HEADMASTER/MISTRESS REMARKS: _____________________________________');

        // Page break for next student
        if ($admno !== array_key_last($students)) {
            $section->addPageBreak();
        }
    }

    // Output Word file
    $filename = 'Student_Academic_Report.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save('php://output');

    ob_end_flush();
} else {
    die("Invalid request method. Please submit the form.");
}
?>
