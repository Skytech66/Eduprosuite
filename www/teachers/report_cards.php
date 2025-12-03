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

    // Add circle + ellipse support for FPDF
    function Circle($x, $y, $r, $style='D')
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    function Ellipse($x, $y, $rx, $ry, $style='D')
    {
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';

        $k = $this->k;
        $hp = $this->h;

        $lx = 4/3 * (sqrt(2) - 1) * $rx;
        $ly = 4/3 * (sqrt(2) - 1) * $ry;

        $this->_out(sprintf('%.2F %.2F m', ($x+$rx)*$k, ($hp-$y)*$k ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k, ($hp-($y-$ly))*$k,
            ($x+$lx)*$k, ($hp-($y-$ry))*$k,
            $x*$k, ($hp-($y-$ry))*$k ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k, ($hp-($y-$ry))*$k,
            ($x-$rx)*$k, ($hp-($y-$ly))*$k,
            ($x-$rx)*$k, ($hp-$y)*$k ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k, ($hp-($y+$ly))*$k,
            ($x-$lx)*$k, ($hp-($y+$ry))*$k,
            $x*$k, ($hp-($y+$ry))*$k ));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x+$lx)*$k, ($hp-($y+$ry))*$k,
            ($x+$rx)*$k, ($hp-($y+$ly))*$k,
            ($x+$rx)*$k, ($hp-$y)*$k,
            $op ));
    }

    function setConfig($config) {
        $this->config = $config;
    }

    function setTotalStudents($total) {
        $this->total_students = $total;
    }

    function header() {
        // Black and white header with clean design
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 210, 45, 'F');

        // School logo - MOVED VERY CLOSE TO SCHOOL NAME
        if (file_exists('gat.png')) {
            $this->Image('gat.png', 10, 8, 35, 35);
        } else {
            $this->SetFillColor(240, 240, 240);
            $this->Rect(10, 8, 35, 35, 'F');
            $this->SetFont('Times', 'I', 12);
            $this->SetTextColor(100, 100, 100);
            $this->SetXY(10, 20);
            $this->Cell(35, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School Information - ADJUSTED TO BE CLOSE TO LOGO
        $this->SetFont('Times', 'B', 28);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY(50, 8);
        $this->Cell(145, 8, 'STARS ON EARTH ACADEMY', 0, 1, 'C');

        // Contact information - FONT SIZE 14
        $this->SetFont('Times', 'B', 14);
        $this->SetTextColor(50, 50, 50);
        $this->SetX(50);
        $this->Cell(145, 6, 'LOCATION: ABOKOBI-AKPORMAN, ACCRA', 0, 1, 'C');
        $this->SetX(50);
        $this->Cell(145, 6, 'TEL: +233246484366 / +233244457834', 0, 1, 'C');
        $this->SetX(50);
        $this->Cell(145, 6, 'EMAIL: starsonearth@gmail.com', 0, 1, 'C');
        
        // Clean separator line
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(0, 0, 0);
        $this->Line(15, 40, 195, 40);
        
        // Changed "TERMINAL REPORT (UPPER PRIMARY)" to font size 11 and italic
        $this->SetY(45);
        $this->SetFont('Times', 'I', 11);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(190, 12, 'Terminal Report (Upper Primary)', 0, 1, 'C');
        
        $this->SetFont('Times', 'I', 12);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(190, 6, '', 0, 1, 'C');
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
                $students[$admno]['marks'] = [];
            }
            
            // Calculate total by adding class_score and exam_score, and remove decimals
            $classScore = $row["class_score"];
            $examScore = $row["exam_score"];
            
            // Convert to integers to remove decimals
            $classScoreInt = (int)$classScore;
            $examScoreInt = (int)$examScore;
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

        $this->setTotalStudents(count($students));
        $termEnds = isset($this->config['term_ends']) ? $this->config['term_ends'] : 'December 15, 2024';
        $termBegins = isset($this->config['term_begins']) ? $this->config['term_begins'] : 'January 8, 2025';

        foreach ($students as $admno => $data) {
            $this->current_student_index++;
            
            // Student information section
            $this->SetY(58);
            
            // Student details - REMOVED PHOTO SECTION
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.3);
            
            // Main student info box
            $this->Rect(15, 56, 180, 36, 'D');
            
            // Student name and exam (to the right of name)
            $this->SetFont('Times', 'B', 14);
            $this->SetTextColor(0, 0, 0);
            $this->SetXY(20, 60);
            $this->Cell(40, 8, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 16);
            $this->Cell(80, 8, strtoupper($data['name']), 0, 0, 'L');
            
            // Exam to the right of student name
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(50, 50, 50);
            $this->Cell(18, 8, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, 8, $exam, 0, 1, 'L');
            
            // Class and Number on Roll
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(50, 50, 50);
            $this->SetXY(20, 70);
            $this->Cell(22, 7, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(25, 7, $class, 0, 0, 'L');
            
            // Number on Roll beside class
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(50, 50, 50);
            $this->SetX(70);
            $this->Cell(30, 7, 'NO. ON ROLL:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(15, 7, $this->total_students, 0, 1, 'L');
            
            // Next term begins and Position
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(50, 50, 50);
            $this->SetXY(20, 80);
            $this->Cell(35, 6, 'NEXT TERM BEGINS:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(40, 6, $termBegins, 0, 0, 'L');
            
            // Position to the right
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(50, 50, 50);
            $this->SetX(100);
            $this->Cell(22, 6, 'POSITION:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13);
            $this->SetTextColor(0, 0, 0);
            
            // Calculate and display overall position
            $overallPosition = 'N/A';
            if (isset($data['marks'][0]['position']) && is_numeric($data['marks'][0]['position'])) {
                $overallPosition = ordinal($data['marks'][0]['position']);
            }
            $this->Cell(25, 6, $overallPosition, 0, 1, 'L');

            // Academic performance table
            $this->Ln(12);
            
            // Table header - NORMAL TABLE
            $this->SetFillColor(240, 240, 240);
            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Times', 'B', 11);
            $this->SetLineWidth(0.3);
            
            // Updated columns as requested - SUBJECT and REMARKS wider, others thinner
            $headers = ['SUBJECT', 'CLASS SCORE 50%', 'EXAM SCORE 50%', 'TOTAL 100%', 'REMARKS', 'INITIALS'];
            $widths = [60, 20, 20, 20, 50, 20]; // SUBJECT and REMARKS wider
            
            // Draw table header with borders
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content
            $this->SetTextColor(0, 0, 0);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Times', '', 11);
                
                if ($fill) {
                    $this->SetFillColor(248, 248, 248);
                } else {
                    $this->SetFillColor(255, 255, 255);
                }
                
                $subject = $row["subject"];
                $classScore = $row["class_score"];
                $examScore = $row["exam_score"];
                $total = $row["total"];
                $originalPosition = $row["position"];

                $this->Cell($widths[0], 8, $subject, 1, 0, 'C', $fill);
                $this->Cell($widths[1], 8, $classScore, 1, 0, 'C', $fill);
                $this->Cell($widths[2], 8, $examScore, 1, 0, 'C', $fill);
                
                // BOLD THE TOTAL 100% TEXT
                $this->SetFont('Times', 'B', 11);
                $this->Cell($widths[3], 8, $total, 1, 0, 'C', $fill);
                
                // REMARKS (changed from Grade)
                $this->SetFont('Times', '', 11);
                $remarks = isset($row["remarks"]) ? $row["remarks"] : '';
                $this->Cell($widths[4], 8, $remarks, 1, 0, 'C', $fill);
                
                // INITIALS (changed from Position)
                $this->SetFont('Times', 'B', 10);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[5], 8, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[5], 8, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
            }

            // Attendance and promotion section - ALL ON SAME LINE
            $this->Ln(8);
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.3);
            
            // Attendance and promotion on one line
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, 6, 'ATTENDANCE:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->Cell(50, 6, '..........................', 0, 0, 'L');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, 6, 'OUT OF:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->Cell(30, 6, '..........................', 0, 0, 'L');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, 6, 'PROMOTED TO:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->Cell(40, 6, '..........................', 0, 1, 'L');
            
            $this->Ln(4);
            
            // Conduct/Temperament
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(50, 6, 'CONDUCT/TEMPERAMENT:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->Cell(130, 6, '_________________________________________________', 0, 1, 'L');
            
            $this->Ln(4);
            
            // Attitude towards work
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(50, 6, 'ATTITUDE TOWARDS WORK:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->Cell(130, 6, '_________________________________________________', 0, 1, 'L');
            
            $this->Ln(4);
            
            // Interest
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(30, 6, 'INTEREST:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->Cell(150, 6, '_________________________________________________________', 0, 1, 'L');
            
            $this->Ln(8);
            
            // Class Teacher's Remarks
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(90, 6, 'CLASS TEACHER\'S REMARKS:', 0, 0, 'L');
            
            // Headmaster/Mistress's Remarks
            $this->SetX(110);
            $this->Cell(90, 6, 'HEADMASTER/MISTRESS REMARKS:', 0, 1, 'L');
            
            $this->Ln(2);
            
            // Underline for Class Teacher's Remarks
            $this->SetDrawColor(150, 150, 150);
            $this->SetLineWidth(0.5);
            $this->Line(20, $this->GetY(), 85, $this->GetY());
            
            // Space between the two remarks lines
            $this->Ln(8);
            
            // Underline for Headmaster/Mistress's Remarks
            $this->Line(110, $this->GetY(), 175, $this->GetY());
            
            $this->Ln(10);

            // Add page break for next student (except for the last one)
            if ($this->current_student_index < $this->total_students) {
                $this->AddPage();
            }
        }
    }
}

// Check if form is submitted and generate PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch config data from the database
    $configSql = "SELECT school_name, po_box, address, term_ends, term_begins FROM config_report LIMIT 1";
    $configStmt = $conn->query($configSql);
    $config = $configStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        $config = array();
    }
    
    // Create new PDF instance
    $pdf = new mypdf();
    $pdf->setConfig($config);
    $pdf->AliasNbPages();
    $pdf->AddPage('P', 'A4', 0);
    $pdf->headertable($conn);
    
    // Output the PDF inline in the browser
    $pdf->Output('Student_Academic_Report.pdf', 'I');
    
    // Flush the output buffer
    ob_end_flush();
} else {
    die("Invalid request method. Please submit the form.");
}
?>
