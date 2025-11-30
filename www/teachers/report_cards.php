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
        // Professional header with clean design
        $this->SetFillColor(250, 250, 252);
        $this->Rect(0, 0, 210, 45, 'F');

        // School logo - INCREASED SIZE
        if (file_exists('gat.png')) {
            $this->Image('gat.png', 15, 8, 35, 30); 
        } else {
            $this->SetFillColor(240, 240, 240);
            $this->Rect(15, 8, 35, 30, 'F');
            $this->SetFont('Arial', 'I', 12);
            $this->SetTextColor(180, 180, 180);
            $this->SetXY(15, 20);
            $this->Cell(35, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School Information - LARGER FONT SIZES
        $this->SetFont('Times', 'B', 19);
        $this->SetTextColor(30, 70, 120);
        $this->SetXY(55, 10);
        $this->Cell(140, 8, 'STARS ON EARTH ACADEMY', 0, 1, 'C');

        // REMOVED SLOGAN "Quality Education for Future Leaders"

        // Contact information - LARGER FONT
        $this->SetFont('Times', 'B', 12);
        $this->SetTextColor(70, 70, 80);
        $this->SetX(55);
        $this->Cell(140, 6, 'LOCATION: ABOKOBI-AKPORMAN, ACCRA', 0, 1, 'C');
        $this->SetX(55);
        $this->Cell(140, 6, 'TEL: +233246484366 / +233244457834', 0, 1, 'C');
        $this->SetX(55);
        $this->Cell(140, 6, 'EMAIL: starsonearth@gmail.com', 0, 1, 'C');
        
        // Clean separator line
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(70, 130, 180);
        $this->Line(15, 40, 195, 40);
        
        // Report title with LARGER FONT
        $this->SetY(45);
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(40, 80, 140);
        $this->Cell(190, 12, 'ACADEMIC PERFORMANCE REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 12);
        $this->SetTextColor(120, 120, 140);
        $this->Cell(190, 6, '', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(2);
        $this->Cell(95, 5, 'Generated: ' . date('M j, Y g:i A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
        
        // REMOVED "CONFIDENTIAL ACADEMIC DOCUMENT" and SLOGAN
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
            "Developing well and shows positive attitude towards learning.",
            "Making satisfactory progress with room for continued growth.",
            "Shows interest in learning and participates actively in class.",
            "Working towards achieving full potential with guidance.",
            "Needs to develop more consistent study habits for better results.",
            "Would benefit from additional practice and reinforcement.",
            "Requires more focus and dedication to improve performance.",
            "Needs to work on completing assignments more consistently."
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
            "Generally well-behaved with positive approach to learning.",
            "Responds well to guidance and shows willingness to improve.",
            "Developing good social skills and classroom etiquette.",
            "Needs occasional reminders to maintain focus and attention.",
            "Would benefit from improved self-control in classroom settings.",
            "Requires consistent monitoring to ensure task completion.",
            "Needs to work on following classroom rules more consistently.",
            "Would benefit from developing better conflict resolution skills."
        ];
        return $conductRemarks[array_rand($conductRemarks)];
    }

    function drawStudentPhoto($photo, $x, $y) {
        if (!empty($photo) && file_exists($photo)) {
            // Professional photo with border - INCREASED SIZE
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 38, 32);
            $this->SetFillColor(250, 250, 250);
            $this->Rect($x-1, $y-1, 36, 30, 'F');
            $this->Image($photo, $x, $y, 34, 28);
        } else {
            // Clean photo placeholder - INCREASED SIZE
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(220, 220, 220);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 38, 32, 'DF');
            $this->SetFont('Arial', 'I', 12);
            $this->SetTextColor(160, 160, 160);
            $this->SetXY($x, $y+10);
            $this->Cell(34, 5, 'STUDENT', 0, 0, 'C');
            $this->SetXY($x, $y+18);
            $this->Cell(34, 5, 'PHOTO', 0, 0, 'C');
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
            
            // Student information section - LARGER FONTS
            $this->SetY(58);
            
            // Student photo - moved to the left for better separation
            $this->drawStudentPhoto($data['photo'], 20, 58);
            
            // Student details - LARGER FONTS
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(230, 230, 240);
            $this->SetLineWidth(0.3);
            
            // Main student info box
            $this->Rect(65, 56, 125, 36, 'D');
            
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(50, 70, 100);
            $this->SetXY(70, 60);
            $this->Cell(40, 8, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 16);
            $this->SetTextColor(30, 60, 110);
            $this->Cell(80, 8, strtoupper($data['name']), 0, 1, 'L');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(70, 90, 110);
            $this->SetXY(70, 70);
            $this->Cell(22, 7, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(40, 100, 160);
            $this->Cell(25, 7, $class, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(70, 90, 110);
            $this->SetX(115);
            $this->Cell(22, 7, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(160, 80, 40);
            $this->Cell(35, 7, $exam, 0, 1, 'L');
            
            // Term dates - LARGER FONTS
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(80, 100, 120);
            $this->SetXY(70, 80);
            $this->Cell(30, 6, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(50, 70, 90);
            $this->Cell(40, 6, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(80, 100, 120);
            $this->SetX(130);
            $this->Cell(30, 6, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(50, 70, 90);
            $this->Cell(35, 6, $termBegins, 0, 1, 'L');

            // Academic performance table - LARGER FONTS
            $this->Ln(12);
            
            // Table header with LARGER FONTS - ADJUSTED WIDTHS to fit properly
            $this->SetFillColor(60, 100, 160);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 11); // Slightly smaller font to fit
            $this->SetLineWidth(0.3);
            
            // Adjusted column widths to fit better on the page
            $headers = ['SUBJECT', 'CLASS SCORE', 'EXAM SCORE', 'TOTAL', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [35, 25, 25, 22, 20, 38, 25]; // Adjusted widths
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content with LARGER FONTS
            $this->SetTextColor(0, 0, 0);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Helvetica', '', 11); // Slightly smaller font to fit
                
                if ($fill) {
                    $this->SetFillColor(248, 250, 255);
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
                $this->Cell($widths[3], 8, $total, 1, 0, 'C', $fill);

                // Grade with professional color coding - using total instead of average
                if ($total >= 70 && $total <= 100) {
                    $grade = 'A'; 
                    $gradeColor = array(0, 128, 0); // Green
                    $remarks = 'Advanced';
                } elseif ($total >= 55 && $total < 70) {
                    $grade = 'B'; 
                    $gradeColor = array(0, 100, 200); // Blue
                    $remarks = 'Proficient';
                } elseif ($total >= 40 && $total < 55) {
                    $grade = 'C'; 
                    $gradeColor = array(255, 140, 0); // Orange
                    $remarks = 'Developing';
                } else {
                    $grade = 'D'; 
                    $gradeColor = array(220, 0, 0); // Red
                    $remarks = 'Beginning';
                }
                
                $this->SetTextColor($gradeColor[0], $gradeColor[1], $gradeColor[2]);
                $this->SetFont('Helvetica', 'B', 11);
                $this->Cell($widths[4], 8, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Helvetica', '', 11);
                $this->Cell($widths[5], 8, $remarks, 1, 0, 'C', $fill);
                
                $this->SetTextColor(60, 80, 150);
                $this->SetFont('Helvetica', 'B', 10);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 8, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[6], 8, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Grading System - LARGER FONTS
            $this->Ln(10);
            $this->SetFillColor(245, 248, 255);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, $this->GetY(), 180, 22, 'DF');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(50, 80, 130);
            $this->SetXY(15, $this->GetY() + 4);
            $this->Cell(180, 6, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(70, 90, 120);
            $this->SetXY(15, $this->GetY());
            $this->Cell(180, 5, 'A (70-100) -  Advanced| B (55-69) - Proficient | C (40-54) - Developing', 0, 1, 'C');
            $this->SetXY(15, $this->GetY());
            $this->Cell(180, 5, 'D (0-39) - Beginning ', 0, 1, 'C');

            // Attendance and promotion section - LARGER FONTS
            $this->Ln(8);
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(220, 220, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, $this->GetY(), 180, 22, 'D');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 80, 100);
            $this->SetXY(25, $this->GetY() + 5);
            $this->Cell(38, 6, 'Days Present:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 6, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 80, 100);
            $this->SetX(100);
            $this->Cell(35, 6, 'Total Days:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 6, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(40, 120, 60);
            $this->SetXY(25, $this->GetY() + 8);
            $this->Cell(45, 7, 'Promotion Status:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(30, 100, 180);
            $this->Cell(60, 7, 'Repeated / Promoted', 0, 1, 'L');

            // Comments section - LARGER FONTS
            $this->Ln(4);
            
            // Academic remarks
            $academicRemark = $this->getAcademicRemarks();
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 90, 140);
            $this->Cell(180, 7, 'ACADEMIC REMARKS:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(40, 50, 60);
            $this->MultiCell(180, 6, $academicRemark, 0, 'L');
            
            // Conduct remarks
            $this->Ln(3);
            $conductRemark = $this->getConductRemarks();
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 90, 140);
            $this->Cell(180, 7, 'CONDUCT REMARKS:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(40, 50, 60);
            $this->MultiCell(180, 6, $conductRemark, 0, 'L');

            // Signatures section - SIMPLIFIED (removed Name&Date and Acknowledgment)
            $this->Ln(4);
            
            // Signature labels - LARGER FONT
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(70, 90, 110);
            $this->Cell(85, 6, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->SetX(110);
            $this->Cell(85, 6, 'Headteacher\'s Signature:', 0, 1, 'L');
            
            // Signature lines - THICKER LINES
            $this->SetDrawColor(150, 150, 150);
            $this->SetLineWidth(0.5);
            $this->Line(20, $this->GetY(), 85, $this->GetY());
            $this->Line(110, $this->GetY(), 175, $this->GetY());
            
            // REMOVED Name & Date and Acknowledgment lines

            // REMOVED PROGRESS CIRCLES COMPLETELY

            // Add official stamp/signature image if available
            $signatureImage = '';
            switch(strtolower(trim($class))) {
                case 'basic 3b':
                case 'basic 3a':
                    $signatureImage = 'new.jpg';
                    break;
                case 'basic 6':
                    $signatureImage = 'ern.png';
                    break;
                default:
                    $signatureImage = 'new.jpg';
            }

            if (file_exists($signatureImage)) {
                $this->Image($signatureImage, 140, $this->GetY() - 18, 25, 12);
            }
            
            // Progress indicator - LARGER FONT
            $this->Ln(4);
            $this->SetFont('Helvetica', 'I', 10);
            $this->SetTextColor(100, 100, 120);
            $this->Cell(180, 5, "Student Report " . $this->current_student_index . " of " . $this->total_students, 0, 1, 'R');

            // REMOVED School motto "Quality Education for Future Leaders"

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
