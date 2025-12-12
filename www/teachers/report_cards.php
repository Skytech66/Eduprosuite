<?php
require_once 'vendor/autoload.php'; // Make sure this path points to your PHPWord autoload file

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

// Create a new Word document
$phpWord = new PhpWord();
$section = $phpWord->addSection();

// Add some content
$section->addText("Report Card");
$section->addText("Student Name: John Doe");
$section->addText("Grade: A");
$section->addText("Comments: Excellent performance!");

// Save the document
$filename = 'ReportCard.docx';
$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($filename);

echo "Word document '$filename' has been generated successfully!";
?>
