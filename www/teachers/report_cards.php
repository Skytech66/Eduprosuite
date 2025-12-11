<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

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

// Include PHPWord manually
require_once __DIR__ . '/phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;

// Check form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method. Please submit the form.");
}

// Fetch config data
$configSql = "SELECT school_name, po_box, address, term_ends, term_begins FROM config_report LIMIT 1";
$configStmt = $conn->query($configSql);
$config = $configStmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Fetch all student data
$class = $_POST['askclass'];
$exam = $_POST['exam'];

$sql = "SELECT admission_number, photo, student, subject, class_score, exam_score, average, remarks, position 
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

    $classScoreInt = (int)$row["class_score"];
    $examScoreInt = (int)$row["exam_score"];
    $totalScore = $classScoreInt + $examScoreInt;

    $students[$admno]['marks'][] = [
        'subject' => $row["subject"],
        'class_score' => $classScoreInt,
        'exam_score' => $examScoreInt,
        'total' => $totalScore,
        'remarks' => $row["remarks"],
        'position' => $row["position"]
    ];
}

$termBegins = $config['term_begins'] ?? 'January 8, 2025';
$termEnds = $config['term_ends'] ?? 'December 15, 2024';

// Create PHPWord instance
$phpWord = new PhpWord();

// Styles
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

// Loop through each student
foreach ($students as $admno => $data) {
    $section = $phpWord->addSection();

    // Header
    $headerTable = $section->addTable(['alignment' => 'center', 'cellMargin' => 80]);
    $headerTable->addRow();
    $cell1 = $headerTable->addCell(Converter::cmToTwip(3.5));
    if (file_exists('gat.png')) {
        $cell1->addImage('gat.png', ['width' => 100, 'height' => 100]);
    } else {
        $cell1->addText('SCHOOL LOGO', ['bold' => true, 'italic' => true, 'size' => 12], ['align' => 'center']);
    }

    $cell2 = $headerTable->addCell(Converter::cmToTwip(12));
    $cell2->addText('STARS ON EARTH ACADEMY', ['bold' => true, 'size' => 28], ['align' => 'center']);
    $cell2->addText('LOCATION: ABOKOBI-AKPORMAN, ACCRA', ['bold' => true, 'size' => 14], ['align' => 'center']);
    $cell2->addText('TEL: +233246484366 / +233244457834', ['bold' => true, 'size' => 14], ['align' => 'center']);
    $cell2->addText('EMAIL: starsonearth@gmail.com', ['bold' => true, 'size' => 14], ['align' => 'center']);
    $cell2->addText('Terminal Report (Upper Primary)', ['italic' => true, 'size' => 11], ['align' => 'center']);

    // Student Info Table
    $section->addTextBreak(1);
    $infoTable = $section->addTable(['cellMargin' => 80, 'borderSize' => 1]);
    $infoTable->addRow();
    $infoTable->addCell(Converter::cmToTwip(3))->addText("STUDENT: " . strtoupper($data['name']), ['bold' => true, 'size' => 16]);
    $infoTable->addCell(Converter::cmToTwip(3))->addText("EXAM: $exam", ['bold' => true, 'size' => 13]);

    $infoTable->addRow();
    $infoTable->addCell(Converter::cmToTwip(3))->addText("CLASS: $class");
    $infoTable->addCell(Converter::cmToTwip(3))->addText("NO. ON ROLL: " . count($students));

    $infoTable->addRow();
    $infoTable->addCell(Converter::cmToTwip(3))->addText("NEXT TERM BEGINS: $termBegins");
    $overallPosition = isset($data['marks'][0]['position']) ? ordinal($data['marks'][0]['position']) : 'N/A';
    $infoTable->addCell(Converter::cmToTwip(3))->addText("POSITION: $overallPosition");

    // Academic Table
    $section->addTextBreak(1);
    $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
    $phpWord->addTableStyle('AcademicTable', $tableStyle);
    $acadTable = $section->addTable('AcademicTable');

    // Table header
    $acadTable->addRow();
    $headers = ['SUBJECT', 'CLASS SCORE 50%', 'EXAM SCORE 50%', 'TOTAL 100%', 'REMARKS', 'INITIALS'];
    foreach ($headers as $header) {
        $acadTable->addCell(Converter::cmToTwip(3))->addText($header, ['bold' => true, 'size' => 11], ['align' => 'center']);
    }

    // Table data
    foreach ($data['marks'] as $row) {
        $acadTable->addRow();
        $acadTable->addCell(Converter::cmToTwip(3))->addText($row['subject'], [], ['align' => 'center']);
        $acadTable->addCell(Converter::cmToTwip(3))->addText($row['class_score'], [], ['align' => 'center']);
        $acadTable->addCell(Converter::cmToTwip(3))->addText($row['exam_score'], [], ['align' => 'center']);
        $acadTable->addCell(Converter::cmToTwip(3))->addText($row['total'], ['bold' => true], ['align' => 'center']);
        $acadTable->addCell(Converter::cmToTwip(3))->addText($row['remarks'], [], ['align' => 'center']);
        $initials = is_numeric($row['position']) ? ordinal($row['position']) : 'N/A';
        $acadTable->addCell(Converter::cmToTwip(3))->addText($initials, ['bold' => true], ['align' => 'center']);
    }

    // Attendance & Remarks
    $section->addTextBreak(1);
    $section->addText("ATTENDANCE: ..................  OUT OF: ..................  PROMOTED TO: ..................", ['bold' => true]);
    $section->addText("CONDUCT/TEMPERAMENT: ____________________________________________", ['bold' => true]);
    $section->addText("ATTITUDE TOWARDS WORK: _________________________________________", ['bold' => true]);
    $section->addText("INTEREST: _______________________________________________________", ['bold' => true]);
    $section->addText("CLASS TEACHER'S REMARKS: ______________________________________  HEADMASTER/MISTRESS REMARKS: ____________________________", ['bold' => true]);

    // Add page break for next student
    if ($admno !== array_key_last($students)) {
        $section->addPageBreak();
    }
}

// Save Word file
$filename = "Student_Academic_Report.docx";
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save('php://output');

ob_end_flush();
?>
