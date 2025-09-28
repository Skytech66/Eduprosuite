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
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

require('fpdf.php');

class ReportCard extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'TEST REPORT CARD', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Sample School, Madina - Tel: 0277411866', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo(),0,0,'C');
    }

    function ReportBody($conn) {
        // Fetch one test student's marks
        $sql = "SELECT student_name, class, subject, class_score, exam_score 
                FROM marks_test 
                WHERE student_name = 'John Doe'
                ORDER BY subject ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (!$rows) {
            $this->SetFont('Arial', 'B', 14);
            $this->Cell(0, 10, 'No test data found for John Doe.', 0, 1, 'C');
            return;
        }

        $studentName = $rows[0]['student_name'];
        $class = $rows[0]['class'];

        // Student Info
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,"Student Name: {$studentName}", 0, 1);
        $this->Cell(0,10,"Class: {$class}   |   Term: 3rd Term", 0, 1);
        $this->Cell(0,10,"Date: 28th September, 2025", 0, 1);
        $this->Ln(3);

        // Table header
        $this->SetFont('Arial','B',10);
        $this->Cell(40,10,'Subject',1);
        $this->Cell(30,10,'Class (50%)',1);
        $this->Cell(30,10,'Exam (50%)',1);
        $this->Cell(30,10,'Total',1);
        $this->Cell(20,10,'Grade',1);
        $this->Cell(40,10,'Remarks',1);
        $this->Ln();

        // Subject rows
        $this->SetFont('Arial','',10);
        foreach ($rows as $row) {
            $subject = $row['subject'];
            $classScore = $row['class_score'];
            $examScore = $row['exam_score'];
            $total = $classScore + $examScore;

            // Grading
            if ($total >= 80) {
                $grade = 'A'; $remark = 'Excellent';
            } elseif ($total >= 70) {
                $grade = 'B'; $remark = 'Very Good';
            } elseif ($total >= 60) {
                $grade = 'C'; $remark = 'Good';
            } elseif ($total >= 50) {
                $grade = 'D'; $remark = 'Average';
            } elseif ($total >= 40) {
                $grade = 'E'; $remark = 'Credit';
            } else {
                $grade = 'F'; $remark = 'Poor';
            }

            $this->Cell(40,10,$subject,1);
            $this->Cell(30,10,$classScore,1,0,'C');
            $this->Cell(30,10,$examScore,1,0,'C');
            $this->Cell(30,10,$total,1,0,'C');
            $this->Cell(20,10,$grade,1,0,'C');
            $this->Cell(40,10,$remark,1);
            $this->Ln();
        }

        // Footer area
        $this->Ln(5);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0,10, "Attendance: ______    Out of: ______    Promoted to: Basic 8", 0, 1);
        $this->Ln(5);
        $this->Cell(0,10, "Remarks: Keep up the good work!", 0, 1);
        $this->Ln(10);
        $this->Cell(90,10, "Class Teacher's Signature: ______________", 0, 0);
        $this->Cell(90,10, "Headmistress's Signature: ______________", 0, 1);
    }
}

// Generate PDF
$pdf = new ReportCard();
$pdf->AddPage();
$pdf->ReportBody($conn);
$pdf->Output('I', 'test_report_card.pdf');

// Flush output
ob_end_flush();
?>
