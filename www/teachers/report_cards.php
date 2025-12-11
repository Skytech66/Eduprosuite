<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

// Load PhpWord (no vendor folder required)
require_once __DIR__ . '/phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// Database connection settings
$host = "aws-1-eu-north-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.mqtuzltstbshtjigzujz";
$password = "Ernestbizz..123";

// Connect to DB
try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Convert number to ordinal
function ordinal($number) {
    $number = (int)$number;
    if (!in_array($number % 100, [11, 12, 13])) {
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

    // Fetch marks
    $sql = "SELECT admission_number, student, subject, class_score, exam_score, remarks, position 
            FROM marks 
            WHERE class = :class AND examname = :exam 
            ORDER BY admission_number ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['class' => $class, 'exam' => $exam]);

    $students = [];
    while ($row = $stmt->fetch()) {
        $adm = $row["admission_number"];

        if (!isset($students[$adm])) {
            $students[$adm] = [
                'name' => $row['student'],
                'marks' => []
            ];
        }

        $students[$adm]['marks'][] = [
            'subject' => $row["subject"],
            'class_score' => (int)$row["class_score"],
            'exam_score' => (int)$row["exam_score"],
            'total' => (int)$row["class_score"] + (int)$row["exam_score"],
            'remarks' => $row["remarks"],
            'position' => $row["position"]
        ];
    }

    // Fetch school config
    $config = $conn->query("SELECT school_name, po_box, address, term_ends, term_begins 
                            FROM config_report LIMIT 1")
                   ->fetch() ?: [];

    // Create Word doc
    $phpWord = new PhpWord();
    $section = $phpWord->addSection(['marginTop' => 600, 'marginBottom' => 600]);

    foreach ($students as $adm => $data) {

        // School header
        if (file_exists('gat.png')) {
            $section->addImage('gat.png', ['width' => 80, 'height' => 80, 'alignment' => 'left']);
        }

        $section->addText(strtoupper($config['school_name'] ?? 'STARS ON EARTH ACADEMY'),
            ['size' => 28, 'bold' => true], ['alignment' => 'center']);

        $section->addText('LOCATION: ABOKOBI-AKPORMAN, ACCRA', ['size' => 14], ['alignment' => 'center']);
        $section->addText('TEL: +233246484366 / +233244457834', ['size' => 14], ['alignment' => 'center']);
        $section->addText('EMAIL: starsonearth@gmail.com', ['size' => 14], ['alignment' => 'center']);
        $section->addTextBreak(1);

        $section->addText('Terminal Report (Upper Primary)', ['italic' => true, 'size' => 11], ['alignment' => 'center']);
        $section->addTextBreak(1);

        // Student info
        $section->addText('STUDENT: ' . strtoupper($data['name']), ['bold' => true, 'size' => 16]);
        $section->addText('CLASS: ' . $class);
        $section->addText('EXAM: ' . $exam);
        $section->addText('NEXT TERM BEGINS: ' . ($config['term_begins'] ?? 'January 8, 2025'));

        $overallPos = is_numeric($data['marks'][0]['position'] ?? null)
                        ? ordinal($data['marks'][0]['position'])
                        : 'N/A';
        $section->addText("POSITION: $overallPos");
        $section->addTextBreak(1);

        // Table
        $phpWord->addTableStyle('MarksTable', [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 40
        ]);

        $table = $section->addTable('MarksTable');

        $headers = ['SUBJECT', 'CLASS SCORE 50%', 'EXAM SCORE 50%', 'TOTAL 100%', 'REMARKS', 'INITIALS'];

        $table->addRow();
        foreach ($headers as $h) {
            $table->addCell(2000)->addText($h, ['bold' => true], ['alignment' => 'center']);
        }

        foreach ($data['marks'] as $m) {
            $table->addRow();
            $table->addCell(2000)->addText($m['subject'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText($m['class_score'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText($m['exam_score'], [], ['alignment' => 'center']);
            $table->addCell(1500)->addText($m['total'], ['bold' => true], ['alignment' => 'center']);
            $table->addCell(2500)->addText($m['remarks'], [], ['alignment' => 'center']);

            $pos = is_numeric($m['position']) ? ordinal($m['position']) : 'N/A';
            $table->addCell(1500)->addText($pos, [], ['alignment' => 'center']);
        }

        $section->addTextBreak(1);

        // Comments fields
        $section->addText("ATTENDANCE: .................. OUT OF: .................. PROMOTED TO: ..................");
        $section->addText("CONDUCT/TEMPERAMENT: ________________________________________________");
        $section->addText("ATTITUDE TOWARDS WORK: _____________________________________________");
        $section->addText("INTEREST: ___________________________________________________________");
        $section->addText("CLASS TEACHER’S REMARKS: _______________________   HEADMASTER/MISTRESS REMARKS: _______________________");

        // Page break
        if ($adm !== array_key_last($students)) {
            $section->addPageBreak();
        }
    }

    // Output file
    $filename = "Student_Academic_Report.docx";

    header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $writer = IOFactory::createWriter($phpWord, "Word2007");
    $writer->save("php://output");

    ob_end_flush();
    exit;
}

echo "Invalid request.";
?>
