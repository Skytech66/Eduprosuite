<?php
require_once 'vendor/autoload.php'; // Make sure you installed phpoffice/phpword via Composer
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $class = $_POST['askclass'];
    $exam = $_POST['exam'];

    // Fetch config data
    $configSql = "SELECT school_name, po_box, address, term_ends, term_begins FROM config_report LIMIT 1";
    $configStmt = $conn->query($configSql);
    $config = $configStmt->fetch(PDO::FETCH_ASSOC);
    if (!$config) $config = [];

    $termBegins = $config['term_begins'] ?? 'January 8, 2025';

    // Fetch students and their marks
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

        $classScore = (int)$row["class_score"];
        $examScore = (int)$row["exam_score"];
        $totalScore = $classScore + $examScore;

        $students[$admno]['marks'][] = [
            'subject' => $row["subject"],
            'class_score' => $classScore,
            'exam_score' => $examScore,
            'total' => $totalScore,
            'remarks' => $row["remarks"],
            'position' => $row["position"]
        ];
    }

    $total_students = count($students);

    $phpWord = new PhpWord();
    $sectionStyle = ['marginTop' => 600, 'marginBottom' => 600];
    $section = $phpWord->addSection($sectionStyle);

    // Loop through each student
    foreach ($students as $admno => $data) {

        // Header - School info
        $phpWord->addFontStyle('headerTitle', ['bold' => true, 'size' => 28]);
        $phpWord->addFontStyle('headerSub', ['bold' => true, 'size' => 14]);
        $phpWord->addFontStyle('normal', ['size' => 12]);
        $phpWord->addFontStyle('bold', ['bold' => true, 'size' => 12]);

        $section->addText('STARS ON EARTH ACADEMY', 'headerTitle', ['alignment' => 'center']);
        $section->addText('LOCATION: ABOKOBI-AKPORMAN, ACCRA', 'headerSub', ['alignment' => 'center']);
        $section->addText('TEL: +233246484366 / +233244457834', 'headerSub', ['alignment' => 'center']);
        $section->addText('EMAIL: starsonearth@gmail.com', 'headerSub', ['alignment' => 'center']);
        $section->addTextBreak(1);
        $section->addText('Terminal Report (Upper Primary)', ['italic' => true, 'size' => 11], ['alignment' => 'center']);
        $section->addTextBreak(1);

        // Student info
        $tableStyle = ['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 50];
        $phpWord->addTableStyle('studentInfoTable', $tableStyle);
        $table = $section->addTable('studentInfoTable');

        $table->addRow();
        $table->addCell(3000)->addText('STUDENT:', 'bold');
        $table->addCell(6000)->addText(strtoupper($data['name']));
        $table->addCell(2000)->addText('EXAM:', 'bold');
        $table->addCell(2000)->addText($exam);

        $table->addRow();
        $table->addCell(2000)->addText('CLASS:', 'bold');
        $table->addCell(2000)->addText($class);
        $table->addCell(2000)->addText('NO. ON ROLL:', 'bold');
        $table->addCell(2000)->addText($total_students);

        $table->addRow();
        $table->addCell(3000)->addText('NEXT TERM BEGINS:', 'bold');
        $table->addCell(3000)->addText($termBegins);
        $table->addCell(2000)->addText('POSITION:', 'bold');
        $overallPosition = is_numeric($data['marks'][0]['position']) ? ordinal($data['marks'][0]['position']) : 'N/A';
        $table->addCell(2000)->addText($overallPosition);

        $section->addTextBreak(1);

        // Academic performance table
        $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 50];
        $phpWord->addTableStyle('marksTable', $tableStyle);
        $table = $section->addTable('marksTable');

        $table->addRow();
        $headers = ['SUBJECT', 'CLASS SCORE 50%', 'EXAM SCORE 50%', 'TOTAL 100%', 'REMARKS', 'INITIALS'];
        foreach ($headers as $header) {
            $table->addCell(3000)->addText($header, ['bold' => true], ['alignment' => 'center']);
        }

        foreach ($data['marks'] as $mark) {
            $table->addRow();
            $table->addCell(3000)->addText($mark['subject'], [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($mark['class_score'], [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($mark['exam_score'], [], ['alignment' => 'center']);
            $table->addCell(3000)->addText($mark['total'], ['bold' => true], ['alignment' => 'center']);
            $table->addCell(3000)->addText($mark['remarks'], [], ['alignment' => 'center']);
            $initials = is_numeric($mark['position']) ? ordinal($mark['position']) : 'N/A';
            $table->addCell(3000)->addText($initials, ['bold' => true], ['alignment' => 'center']);
        }

        $section->addTextBreak(1);

        // Attendance and promotion
        $section->addText('ATTENDANCE: ..................    OUT OF: ..................    PROMOTED TO: ..................', 'bold');
        $section->addTextBreak(1);

        // Conduct and Attitude
        $section->addText('CONDUCT/TEMPERAMENT: ____________________________________________');
        $section->addText('ATTITUDE TOWARDS WORK: _________________________________________');
        $section->addText('INTEREST: _______________________________________________________');
        $section->addTextBreak(1);

        // Remarks
        $section->addText('CLASS TEACHER\'S REMARKS: ______________________________________    HEADMASTER/MISTRESS REMARKS: __________________________');
        $section->addPageBreak(); // Page break for next student
    }

    // Save Word file
    $fileName = "Student_Academic_Report.docx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment;filename="'.$fileName.'"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save('php://output');
    exit;

} else {
    die("Invalid request method. Please submit the form.");
}
