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

    function setConfig($config) {
        $this->config = $config;
    }

    function setTotalStudents($total) {
        $this->total_students = $total;
    }

    function header() {
        // Professional header with gradient background
        $this->SetFillColor(30, 70, 120);
        $this->Rect(0, 0, 210, 45, 'F');
        
        // Decorative accent
        $this->SetFillColor(255, 215, 0);
        $this->Rect(0, 40, 210, 5, 'F');

        // School logo - CENTERED AND LARGER
        if (file_exists('gat.png')) {
            $this->Image('gat.png', 85, 8, 40, 30); 
        } else {
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(255, 255, 255);
            $this->SetLineWidth(1);
            $this->Rect(85, 8, 40, 30, 'FD');
            $this->SetFont('Arial', 'I', 10);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(85, 20);
            $this->Cell(40, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School Information - WHITE TEXT ON BLUE BACKGROUND
        $this->SetFont('Times', 'B', 20);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(0, 12);
        $this->Cell(210, 8, 'STARS ON EARTH ACADEMY', 0, 1, 'C');

        // Contact information
        $this->SetFont('Times', 'B', 11);
        $this->SetTextColor(255, 255, 255);
        $this->SetX(0);
        $this->Cell(210, 6, 'ABOKOBI-AKPORMAN, ACCRA', 0, 1, 'C');
        $this->SetX(0);
        $this->Cell(210, 6, 'TEL: +233246484366 / +233244457834', 0, 1, 'C');
        $this->SetX(0);
        $this->Cell(210, 6, 'EMAIL: starsonearth@gmail.com', 0, 1, 'C');
        
        // Report title with golden color
        $this->SetY(48);
        $this->SetFont('Arial', 'B', 22);
        $this->SetTextColor(30, 70, 120);
        $this->Cell(210, 12, 'ACADEMIC PERFORMANCE REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 12);
        $this->SetTextColor(120, 120, 140);
        $this->Cell(210, 6, 'Comprehensive Student Assessment', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-18);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line with accent color
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(255, 215, 0);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(3);
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(95, 5, 'Generated: ' . date('M j, Y g:i A'), 0, 0, 'L');
        $this->Cell(95, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
        
        // Confidential notice
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(150, 0, 0);
        $this->Cell(210, 5, 'CONFIDENTIAL ACADEMIC DOCUMENT - DO NOT DUPLICATE', 0, 1, 'C');
    }

    function getAcademicRemarks() {
        $remarks = [
            "Exceptional academic performance with consistent excellence across all subjects. Demonstrates outstanding understanding and application of concepts with remarkable analytical skills.",
            "Outstanding achievement across all subjects. Shows exceptional dedication to academic excellence and consistently produces high-quality work with great attention to detail.",
            "Excellent performance demonstrating strong understanding of concepts. Shows remarkable progress and maintains consistent high standards in all academic work.",
            "Very good academic performance with strong analytical skills. Demonstrates excellent problem-solving abilities and makes valuable contributions to class discussions.",
            "Good overall performance showing steady improvement. Demonstrates strong commitment to learning and shows great potential for continued growth.",
            "Satisfactory performance with positive attitude towards learning. Shows good grasp of subject matter and participates actively in classroom activities.",
            "Developing well academically with consistent effort. Shows interest in learning and is working towards achieving full potential with continued guidance.",
            "Making satisfactory progress with room for continued growth. Needs to develop more consistent study habits and focus on completing assignments regularly."
        ];
        return $remarks[array_rand($remarks)];
    }

    function getConductRemarks() {
        $conductRemarks = [
            "Exemplary behavior and outstanding character. A role model for peers with exceptional leadership qualities and consistent demonstration of respect and responsibility.",
            "Excellent classroom citizen with positive attitude and strong work ethic. Shows exceptional self-discipline and organizational skills while helping others willingly.",
            "Consistently demonstrates respect, responsibility, and integrity. Very cooperative with teachers and classmates, displaying excellent social skills.",
            "Positive contributor to class environment with good behavior. Reliable and trustworthy with strong sense of responsibility towards academic work.",
            "Shows good manners and treats others with kindness and respect. Works well independently and collaborates effectively in group activities.",
            "Generally well-behaved with positive approach to learning. Responds well to guidance and shows willingness to improve social interactions.",
            "Developing good social skills and classroom etiquette. Needs occasional reminders to maintain focus and demonstrate consistent self-control.",
            "Shows improvement in behavior and classroom conduct. Would benefit from continued development of conflict resolution skills and consistent rule following."
        ];
        return $conductRemarks[array_rand($conductRemarks)];
    }

    function drawStudentPhoto($photo, $x, $y) {
        if (!empty($photo) && file_exists($photo)) {
            // Professional photo with golden border
            $this->SetDrawColor(255, 215, 0);
            $this->SetLineWidth(1);
            $this->Rect($x-3, $y-3, 41, 35);
            $this->SetFillColor(250, 250, 250);
            $this->Rect($x-2, $y-2, 39, 33, 'F');
            $this->Image($photo, $x, $y, 35, 29);
        } else {
            // Attractive photo placeholder
            $this->SetFillColor(245, 248, 255);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(1);
            $this->Rect($x-3, $y-3, 41, 35, 'DF');
            $this->SetFont('Arial', 'I', 10);
            $this->SetTextColor(160, 160, 180);
            $this->SetXY($x, $y+8);
            $this->Cell(35, 5, 'STUDENT', 0, 0, 'C');
            $this->SetXY($x, $y+16);
            $this->Cell(35, 5, 'PHOTO', 0, 0, 'C');
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
            
            // Format numbers to remove .00 and convert to integers
            $class_score = $row["class_score"];
            $exam_score = $row["exam_score"];
            $average = $row["average"];
            
            // Remove .00 and convert to integer if it's a whole number
            $class_score = (float)$class_score == (int)$class_score ? (int)$class_score : (float)$class_score;
            $exam_score = (float)$exam_score == (int)$exam_score ? (int)$exam_score : (float)$exam_score;
            $average = (float)$average == (int)$average ? (int)$average : (float)$average;
            
            $students[$admno]['marks'][] = [
                'subject' => $row["subject"],
                'class_score' => $class_score,
                'exam_score' => $exam_score,
                'average' => $average,
                'remarks' => $row["remarks"],
                'position' => $row["position"]
            ];
        }

        $this->setTotalStudents(count($students));
        $termEnds = isset($this->config['term_ends']) ? $this->config['term_ends'] : 'December 15, 2024';
        $termBegins = isset($this->config['term_begins']) ? $this->config['term_begins'] : 'January 8, 2025';

        foreach ($students as $admno => $data) {
            $this->current_student_index++;
            
            // FIX: Always add a new page for every student
            $this->AddPage();
            
            // Student information section with improved layout
            $this->SetY(65);
            
            // Student photo with better positioning
            $this->drawStudentPhoto($data['photo'], 20, 65);
            
            // Student details box with modern design
            $this->SetFillColor(245, 248, 255);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.5);
            $this->RoundedRect(65, 63, 125, 40, 3, 'DF');
            
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(30, 70, 120);
            $this->SetXY(70, 68);
            $this->Cell(40, 8, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 16);
            $this->SetTextColor(20, 50, 100);
            $this->Cell(75, 8, strtoupper($data['name']), 0, 1, 'L');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 90, 130);
            $this->SetXY(70, 78);
            $this->Cell(22, 7, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(30, 80, 150);
            $this->Cell(25, 7, $class, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 90, 130);
            $this->SetX(115);
            $this->Cell(22, 7, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(180, 80, 40);
            $this->Cell(35, 7, $exam, 0, 1, 'L');
            
            // Term dates
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(70, 100, 140);
            $this->SetXY(70, 88);
            $this->Cell(30, 6, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(40, 60, 90);
            $this->Cell(40, 6, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(70, 100, 140);
            $this->SetX(130);
            $this->Cell(30, 6, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(40, 60, 90);
            $this->Cell(35, 6, $termBegins, 0, 1, 'L');

            // Academic performance table
            $this->Ln(15);
            
            // Table header with modern design
            $this->SetFillColor(30, 70, 120);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetLineWidth(0.3);
            
            $headers = ['SUBJECT', 'CLASS WORK', 'EXAM', 'TOTAL', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [35, 25, 25, 25, 22, 40, 28];
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 10, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content
            $this->SetTextColor(0, 0, 0);
            $fill = false;
            $totalMarks = 0;
            $subjectCount = 0;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Helvetica', '', 12);
                
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

                $this->Cell($widths[0], 8, $subject, 1, 0, 'C', $fill);
                $this->Cell($widths[1], 8, $classScore, 1, 0, 'C', $fill);
                $this->Cell($widths[2], 8, $examScore, 1, 0, 'C', $fill);
                $this->Cell($widths[3], 8, $average, 1, 0, 'C', $fill);

                // Grade with professional color coding
                if ($average >= 80) {
                    $grade = 'A'; 
                    $gradeColor = array(0, 128, 0);
                    $remarks = 'Advanced';
                } elseif ($average >= 70) {
                    $grade = 'B'; 
                    $gradeColor = array(0, 100, 200);
                    $remarks = 'Proficient';
                } elseif ($average >= 60) {
                    $grade = 'C'; 
                    $gradeColor = array(255, 140, 0);
                    $remarks = 'Developing';
                } else {
                    $grade = 'D'; 
                    $gradeColor = array(220, 0, 0);
                    $remarks = 'Beginning';
                }
                
                $this->SetTextColor($gradeColor[0], $gradeColor[1], $gradeColor[2]);
                $this->SetFont('Helvetica', 'B', 12);
                $this->Cell($widths[4], 8, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Helvetica', '', 11);
                $this->Cell($widths[5], 8, $remarks, 1, 0, 'C', $fill);
                
                // Position column with better styling
                $this->SetTextColor(255, 255, 255);
                $this->SetFillColor(60, 100, 180);
                $this->SetFont('Helvetica', 'B', 11);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 8, ordinal($originalPosition), 1, 0, 'C', true);
                } else {
                    $this->Cell($widths[6], 8, 'N/A', 1, 0, 'C', true);
                }
                $this->Ln();
                
                $totalMarks += $average;
                $subjectCount++;
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Overall performance summary
            if ($subjectCount > 0) {
                $overallAverage = round($totalMarks / $subjectCount, 1);
                $this->Ln(5);
                $this->SetFont('Helvetica', 'B', 13);
                $this->SetTextColor(30, 70, 120);
                $this->Cell(120, 8, 'OVERALL ACADEMIC PERFORMANCE:', 0, 0, 'R');
                $this->SetFont('Helvetica', 'B', 14);
                $this->SetTextColor(20, 50, 100);
                $this->Cell(30, 8, $overallAverage . '%', 0, 0, 'C');
                $this->SetFont('Helvetica', 'B', 13);
                
                // Overall grade
                if ($overallAverage >= 80) {
                    $overallGrade = 'A (EXCELLENT)';
                    $gradeColor = array(0, 128, 0);
                } elseif ($overallAverage >= 70) {
                    $overallGrade = 'B (VERY GOOD)';
                    $gradeColor = array(0, 100, 200);
                } elseif ($overallAverage >= 60) {
                    $overallGrade = 'C (GOOD)';
                    $gradeColor = array(255, 140, 0);
                } else {
                    $overallGrade = 'D (NEEDS IMPROVEMENT)';
                    $gradeColor = array(220, 0, 0);
                }
                
                $this->SetTextColor($gradeColor[0], $gradeColor[1], $gradeColor[2]);
                $this->Cell(40, 8, $overallGrade, 0, 1, 'C');
            }

            // Grading System with modern design
            $this->Ln(8);
            $this->SetFillColor(240, 245, 255);
            $this->SetDrawColor(180, 200, 230);
            $this->SetLineWidth(0.5);
            $this->RoundedRect(15, $this->GetY(), 180, 25, 3, 'DF');
            
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(30, 70, 120);
            $this->SetXY(15, $this->GetY() + 5);
            $this->Cell(180, 6, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 90, 140);
            $this->SetXY(15, $this->GetY());
            $this->Cell(180, 6, 'A (80-100%) - Advanced | B (70-79%) - Proficient', 0, 1, 'C');
            $this->SetXY(15, $this->GetY());
            $this->Cell(180, 6, 'C (60-69%) - Developing | D (0-59%) - Beginning', 0, 1, 'C');

            // Attendance and promotion section
            $this->Ln(10);
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.5);
            $this->RoundedRect(15, $this->GetY(), 180, 25, 3, 'D');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 80, 120);
            $this->SetXY(25, $this->GetY() + 6);
            $this->Cell(38, 7, 'Days Present:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(30, 7, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 80, 120);
            $this->SetX(100);
            $this->Cell(35, 7, 'Total Days:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 7, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(40, 120, 60);
            $this->SetXY(25, $this->GetY() + 10);
            $this->Cell(45, 8, 'Promotion Status:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(30, 100, 180);
            $this->Cell(60, 8, 'Repeated / Promoted', 0, 1, 'L');

            // Comments section
            $this->Ln(12);
            
            // Academic remarks
            $academicRemark = $this->getAcademicRemarks();
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(30, 70, 120);
            $this->Cell(180, 8, 'ACADEMIC REMARKS:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(40, 50, 70);
            $this->MultiCell(180, 6, $academicRemark, 0, 'L');
            
            // Conduct remarks
            $this->Ln(5);
            $conductRemark = $this->getConductRemarks();
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(30, 70, 120);
            $this->Cell(180, 8, 'CONDUCT REMARKS:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(40, 50, 70);
            $this->MultiCell(180, 6, $conductRemark, 0, 'L');

            // Signatures section
            $this->Ln(10);
            
            // Signature labels
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(60, 80, 120);
            $this->Cell(85, 7, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->SetX(110);
            $this->Cell(85, 7, 'Headteacher\'s Signature:', 0, 1, 'L');
            
            // Signature lines
            $this->SetDrawColor(150, 150, 150);
            $this->SetLineWidth(0.5);
            $this->Line(20, $this->GetY(), 85, $this->GetY());
            $this->Line(110, $this->GetY(), 175, $this->GetY());
            $this->Ln(8);
            
            // Names and dates
            $this->SetFont('Helvetica', 'I', 10);
            $this->SetTextColor(100, 100, 120);
            $this->Cell(85, 5, 'Name & Date:', 0, 0, 'L');
            $this->SetX(110);
            $this->Cell(85, 5, 'Name & Date:', 0, 1, 'L');

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
                $this->Image($signatureImage, 140, $this->GetY() - 15, 25, 12);
            }
            
            // Progress indicator
            $this->Ln(5);
            $this->SetFont('Helvetica', 'I', 10);
            $this->SetTextColor(100, 100, 120);
            $this->Cell(180, 5, "Student Report " . $this->current_student_index . " of " . $this->total_students, 0, 1, 'R');
        }
    }

    // Helper function for rounded rectangles
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
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
    $pdf->headertable($conn);
    
    // Output the PDF inline in the browser
    $pdf->Output('Student_Academic_Report.pdf', 'I');
    
    // Flush the output buffer
    ob_end_flush();
} else {
    die("Invalid request method. Please submit the form.");
}
?>

