<?php
// 1. Load PHPWord (manual installation)
require_once __DIR__ . '/PHPWord-master/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// --------------------------------------------------
// SAMPLE DATA — you can replace with DB values
// --------------------------------------------------
$student_name = "John Doe";
$class = "JSS 2A";
$term = "Second Term";
$session = "2024 / 2025";

$subjects = [
    ["Mathematics", 78],
    ["English Language", 73],
    ["Basic Science", 84],
    ["Social Studies", 69],
    ["Computer Science", 92],
    ["Physical & Health Ed", 81]
];

// --------------------------------------------------
// CREATE DOCUMENT
// --------------------------------------------------
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// Header (School Name)
$titleStyle = [
    'bold' => true,
    'size' => 20,
    'underline' => 'single',
    'alignment' => 'center'
];
$section->addText("SCHOOL REPORT CARD", $titleStyle);

// Student info
$section->addTextBreak(1);
$section->addText("Student Name:  $student_name", ['size' => 14]);
$section->addText("Class:         $class", ['size' => 14]);
$section->addText("Term:          $term", ['size' => 14]);
$section->addText("Session:       $session", ['size' => 14]);

$section->addTextBreak(1);

// --------------------------------------------------
// SUBJECT SCORES TABLE
// --------------------------------------------------
$tableStyle = [
    'borderSize' => 6,
    'borderColor' => '000000',
    'cellMargin' => 120
];

$phpWord->addTableStyle("ReportTable", $tableStyle);

$table = $section->addTable("ReportTable");

// Header row
$table->addRow();
$table->addCell(6000)->addText("Subject", ['bold' => true]);
$table->addCell(2000)->addText("Score", ['bold' => true]);

// Subject rows
foreach ($subjects as $sub) {
    $table->addRow();
    $table->addCell(6000)->addText($sub[0]);
    $table->addCell(2000)->addText($sub[1]);
}

// --------------------------------------------------
// FOOTER SECTION
// --------------------------------------------------
$section->addTextBreak(2);
$section->addText("Teacher's Remark: ____________________________", ['size' => 12]);
$section->addTextBreak(1);
$section->addText("Principal's Signature: ________________________", ['size' => 12]);

// --------------------------------------------------
// SAVE FILE
// --------------------------------------------------
$filename = "report_card_" . str_replace(" ", "_", strtolower($student_name)) . ".docx";
$savePath = __DIR__ . "/" . $filename;

$writer = IOFactory::createWriter($phpWord, "Word2007");
$writer->save($savePath);

echo "Report card generated successfully!<br>";
echo "<a href='$filename' download>Click here to download report card</a>";

?>
