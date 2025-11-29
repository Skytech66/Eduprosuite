<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

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

require "fpdf.php";

class mypdf extends FPDF {
    var $config = array();
    var $current_student_index = 0;
    var $total_students = 0;

    // Add Circle and Ellipse methods to FPDF
    function Circle($x, $y, $r, $style='D') {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    function Ellipse($x, $y, $rx, $ry, $style='D') {
        if($style=='F')
            $op = 'f';
        elseif($style=='FD' || $style=='DF')
            $op = 'B';
        else
            $op = 'S';

        $lx = 4/3*(M_SQRT2-1)*$rx;
        $ly = 4/3*(M_SQRT2-1)*$ry;

        $k = $this->k;
        $h = $this->h;

        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k, ($h-$y)*$k,
            ($x+$rx)*$k, ($h-($y-$ly))*$k,
            ($x+$lx)*$k, ($h-($y-$ry))*$k,
            $x*$k, ($h-($y-$ry))*$k
        ));

        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k, ($h-($y-$ry))*$k,
            ($x-$rx)*$k, ($h-($y-$ly))*$k,
            ($x-$rx)*$k, ($h-$y)*$k
        ));
        
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k, ($h-($y+$ly))*$k,
            ($x-$lx)*$k, ($h-($y+$ry))*$k,
            $x*$k, ($h-($y+$ry))*$k
        ));
        
        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$lx)*$k, ($h-($y+$ry))*$k,
            ($x+$rx)*$k, ($h-($y+$ly))*$k,
            ($x+$rx)*$k, ($h-$y)*$k
        ));

        $this->_out($op);
    }

    function setConfig($config) {
        $this->config = $config;
    }

    function setTotalStudents($total) {
        $this->total_students = $total;
    }

    function header() {
        // Professional header with clean design
        $this->SetFillColor(250, 250, 252);
        $this->Rect(0, 0, 210, 45, 'F');

        // School logo aligned closer to school name
        if (file_exists('gat.png')) {
            $this->Image('gat.png', 15, 10, 25, 25); // Adjusted size and position
        } else {
            $this->SetFillColor(240, 240, 240);
            $this->Rect(15, 10, 25, 25, 'F');
            $this->SetFont('Arial', 'I', 6);
            $this->SetTextColor(180, 180, 180);
            $this->SetXY(15, 18);
            $this->Cell(25, 4, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School Information - properly aligned with logo closer
        $this->SetFont('Times', 'B', 16);
        $this->SetTextColor(30, 70, 120);
        $this->SetXY(45, 10); // Moved closer to logo
        $this->Cell(145, 8, 'STARS ON EARTH ACADEMY', 0, 1, 'C');

        $this->SetFont('Times', '', 10);
        $this->SetTextColor(80, 80, 90);

        // School motto centered
        $this->SetX(45);
        $this->SetFont('Times', 'I', 11);
        $this->SetTextColor(100, 120, 150);
        $this->Cell(145, 6, '"Quality Education for Future Leaders"', 0, 1, 'C');

        // Contact information - improved readability
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(70, 70, 80);
        $this->SetX(45);
        $this->Cell(145, 5, 'LOCATION: ABOKOBI-AKPORMAN, ACCRA', 0, 1, 'C');
        $this->SetX(45);
        $this->Cell(145, 5, 'TEL: +233246484366 / +233244457834', 0, 1, 'C');
        $this->SetX(45);
        $this->Cell(145, 5, 'EMAIL: starsonearth@gmail.com', 0, 1, 'C');
        
        // Clean separator line
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(70, 130, 180);
        $this->Line(15, 40, 195, 40);
        
        // Report title with modern styling
        $this->SetY(45);
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(40, 80, 140);
        $this->Cell(190, 8, 'ACADEMIC PERFORMANCE REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(120, 120, 140);
        $this->Cell(190, 5, 'Comprehensive Student Assessment', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(2);
        $this->Cell(95, 4, 'Generated: ' . date('M j, Y g:i A'), 0, 0, 'L');
        $this->Cell(95, 4, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
    }

    function getAcademicRemarks() {
        $remarks = [
            "Exceptional academic performance with consistent excellence across all subjects.",
            "Demonstrates outstanding understanding and application of concepts.",
            "Shows remarkable progress and dedication to academic excellence.",
            "Consistently produces high-quality work with great attention to detail.",
            "Strong analytical skills and excellent problem-solving abilities.",
            "Shows great potential and consistently exceeds expectations.",
            "Very good understanding of concepts with consistent performance.",
            "Makes valuable contributions to class discussions and activities.",
            "Shows steady improvement and strong commitment to learning.",
            "Good grasp of subject matter with reliable performance.",
        ];
        return $remarks[array_rand($remarks)];
    }

    function getConductRemarks() {
        $conductRemarks = [
            "Exemplary behavior and outstanding character. A role model for peers.",
            "Consistently demonstrates respect, responsibility, and integrity.",
            "Excellent classroom citizen with positive attitude and strong work ethic.",
            "Shows exceptional leadership qualities and helps others willingly.",
            "Very respectful and cooperative with teachers and classmates.",
            "Displays excellent self-discipline and organizational skills.",
            "Positive contributor to class environment with good behavior.",
            "Reliable and trustworthy with strong sense of responsibility.",
            "Works well independently and collaborates effectively in groups.",
            "Shows good manners and treats others with kindness and respect.",
        ];
        return $conductRemarks[array_rand($conductRemarks)];
    }

    function drawStudentPhoto($photo, $x, $y) {
        if (!empty($photo) && file_exists($photo)) {
            // Professional photo with border
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 34, 28);
            $this->SetFillColor(250, 250, 250);
            $this->Rect($x-1, $y-1, 32, 26, 'F');
            $this->Image($photo, $x, $y, 30, 24);
        } else {
            // Clean photo placeholder
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(220, 220, 220);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 34, 28, 'DF');
            $this->SetFont('Arial', 'I', 7);
            $this->SetTextColor(160, 160, 160);
            $this->SetXY($x, $y+8);
            $this->Cell(30, 4, 'STUDENT', 0, 0, 'C');
            $this->SetXY($x, $y+14);
            $this->Cell(30, 4, 'PHOTO', 0, 0, 'C');
        }
    }

    function headertable($conn) {
        $class = $_POST['askclass'];
        $exam = $_POST['exam'];

        // Fetch all student data
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
                $students[$admno]['photo'] = $row["photo"];
                $students[$admno]['marks'] = [];
            }
            $students[$admno]['marks'][] = [
                'subject' => $row["subject"],
                'class_score' => $row["class_score"],
                'exam_score' => $row["exam_score"],
                'average' => $row["average"],
                'remarks' => $row["remarks"],
                'position' => $row["position"]
            ];
        }

        $this->setTotalStudents(count($students));
        $termEnds = isset($this->config['term_ends']) ? $this->config['term_ends'] : 'December 15, 2024';
        $termBegins = isset($this->config['term_begins']) ? $this->config['term_begins'] : 'January 8, 2025';

        foreach ($students as $admno => $data) {
            $this->current_student_index++;
            
            // Student information section - clean and professional
            $this->SetY(60);
            
            // Student photo - moved to the left for better separation
            $this->drawStudentPhoto($data['photo'], 15, 60);
            
            // Student details - modern layout with clear hierarchy
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(230, 230, 240);
            $this->SetLineWidth(0.3);
            
            // Main student info box
            $this->Rect(55, 58, 135, 32, 'D');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(50, 70, 100);
            $this->SetXY(60, 62);
            $this->Cell(35, 6, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(30, 60, 110);
            $this->Cell(75, 6, strtoupper($data['name']), 0, 1, 'L');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(70, 90, 110);
            $this->SetXY(60, 70);
            $this->Cell(18, 5, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(40, 100, 160);
            $this->Cell(25, 5, $class, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(70, 90, 110);
            $this->SetX(100);
            $this->Cell(18, 5, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(160, 80, 40);
            $this->Cell(35, 5, $exam, 0, 1, 'L');
            
            // Term dates - properly separated from photo
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(80, 100, 120);
            $this->SetXY(60, 78);
            $this->Cell(25, 4, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(50, 70, 90);
            $this->Cell(40, 4, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(80, 100, 120);
            $this->SetX(120);
            $this->Cell(25, 4, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(50, 70, 90);
            $this->Cell(30, 4, $termBegins, 0, 1, 'L');

            // Academic performance table - sleek and modern
            $this->Ln(12);
            
            // Table header with professional colors
            $this->SetFillColor(60, 100, 160);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetLineWidth(0.3);
            
            $headers = ['SUBJECT', 'CLASS SCORE', 'EXAM SCORE', 'TOTAL', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [35, 22, 22, 22, 18, 38, 22];
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content with clean alternating colors
            $this->SetTextColor(0, 0, 0);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Helvetica', '', 8);
                
                if ($fill) {
                    $this->SetFillColor(248, 250, 255);
                } else {
                    $this->SetFillColor(255, 255, 255);
                }
                
                $subject = $row["subject"];
                $classScore = $row["class_score"];
                $examScore = $row["exam_score"];
                $average = $row["average"];
                $originalPosition = $row["position"];

                $this->Cell($widths[0], 6, $subject, 1, 0, 'C', $fill);
                $this->Cell($widths[1], 6, $classScore, 1, 0, 'C', $fill);
                $this->Cell($widths[2], 6, $examScore, 1, 0, 'C', $fill);
                $this->Cell($widths[3], 6, $average, 1, 0, 'C', $fill);

                // Grade with professional color coding
                if ($average >= 70 && $average <= 100) {
                    $grade = 'A'; 
                    $gradeColor = array(0, 128, 0); // Green
                    $remarks = 'Advanced';
                } elseif ($average >= 55 && $average < 70) {
                    $grade = 'B'; 
                    $gradeColor = array(0, 100, 200); // Blue
                    $remarks = 'Proficient';
                } elseif ($average >= 40 && $average < 55) {
                    $grade = 'C'; 
                    $gradeColor = array(255, 140, 0); // Orange
                    $remarks = 'Developing';
                } else {
                    $grade = 'D'; 
                    $gradeColor = array(220, 0, 0); // Red
                    $remarks = 'Beginning';
                }
                
                $this->SetTextColor($gradeColor[0], $gradeColor[1], $gradeColor[2]);
                $this->SetFont('Helvetica', 'B', 8);
                $this->Cell($widths[4], 6, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Helvetica', '', 7);
                $this->Cell($widths[5], 6, $remarks, 1, 0, 'C', $fill);
                
                $this->SetTextColor(60, 80, 150);
                $this->SetFont('Helvetica', 'B', 8);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 6, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[6], 6, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Performance summary section
            $this->Ln(8);
            
            // Left column - Grading System
            $this->SetFillColor(245, 248, 255);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, $this->GetY(), 87, 25, 'DF');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(50, 80, 130);
            $this->SetXY(15, $this->GetY() + 3);
            $this->Cell(87, 5, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(70, 90, 120);
            $this->SetXY(20, $this->GetY());
            $this->Cell(77, 4, 'A (80-100) - Excellent', 0, 1, 'L');
            $this->SetX(20);
            $this->Cell(77, 4, 'B (70-79) - Very Good', 0, 1, 'L');
            $this->SetX(20);
            $this->Cell(77, 4, 'C (60-69) - Good', 0, 1, 'L');
            $this->SetX(20);
            $this->Cell(77, 4, 'D (50-59) - Average', 0, 1, 'L');
            $this->SetX(20);
            $this->Cell(77, 4, 'E (40-49) - Credit', 0, 1, 'L');
            $this->SetX(20);
            $this->Cell(77, 4, 'F (0-39) - Weak', 0, 1, 'L');

            // Right column - Teacher's Comments
            $this->SetFillColor(255, 253, 245);
            $this->SetDrawColor(230, 220, 200);
            $this->Rect(108, $this->GetY() - 25, 87, 25, 'DF');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(130, 80, 50);
            $this->SetXY(108, $this->GetY() - 22);
            $this->Cell(87, 5, 'TEACHER\'S COMMENTS', 0, 1, 'C');
            
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(90, 70, 50);
            $this->SetXY(113, $this->GetY() - 18);
            $this->MultiCell(77, 4, $this->getAcademicRemarks(), 0, 'L');

            // Attendance and promotion section
            $this->Ln(8);
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(220, 220, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, $this->GetY(), 180, 18, 'D');
            
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(60, 80, 100);
            $this->SetXY(25, $this->GetY() + 4);
            $this->Cell(30, 5, 'Days Present:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(20, 5, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(60, 80, 100);
            $this->SetX(85);
            $this->Cell(25, 5, 'Total Days:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(20, 5, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(60, 80, 100);
            $this->SetX(145);
            $this->Cell(25, 5, 'Promoted:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(15, 5, '_______', 'B', 1, 'C');

            // Signatures section
            $this->Ln(6);
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(80, 80, 100);
            
            // Class Teacher signature
            $this->SetX(25);
            $this->Cell(50, 4, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->Cell(40, 4, '____________________', 0, 0, 'C');
            
            // Head Teacher signature
            $this->SetX(125);
            $this->Cell(40, 4, 'Head Teacher\'s Signature:', 0, 0, 'L');
            $this->Cell(40, 4, '____________________', 0, 1, 'C');

            // Add page break for next student
            if ($this->current_student_index < $this->total_students) {
                $this->AddPage();
            }
        }
    }
}

// Create PDF instance
$pdf = new mypdf('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Set configuration
$config = array(
    'term_ends' => 'December 15, 2024',
    'term_begins' => 'January 8, 2025'
);
$pdf->setConfig($config);

// Generate the report
$pdf->headertable($conn);

// Output the PDF
$pdf->Output('I', 'Student_Report_Cards.pdf');

ob_end_flush();
?>
