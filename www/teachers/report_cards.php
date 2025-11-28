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

    // Function to draw progress circle
    function drawProgressCircle($x, $y, $radius, $percentage, $label, $color) {
        // Circle background
        $this->SetFillColor(240, 240, 240);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Circle($x, $y, $radius, 'D');
        $this->Circle($x, $y, $radius, 'F');
        
        // Progress arc - simplified version without complex arc drawing
        $this->SetDrawColor($color[0], $color[1], $color[2]);
        $this->SetLineWidth(2);
        
        // Draw a simple circle for progress (simplified approach)
        $this->SetFillColor($color[0], $color[1], $color[2]);
        $this->SetTextColor(255, 255, 255);
        
        // Draw filled circle based on percentage (simplified visual)
        $fillRadius = $radius * 0.7;
        $this->Circle($x, $y, $fillRadius, 'F');
        
        // Percentage text
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetTextColor(50, 50, 50);
        $this->SetXY($x - 5, $y - 2);
        $this->Cell(10, 4, $percentage . '%', 0, 0, 'C');
        
        // Label
        $this->SetFont('Helvetica', '', 7);
        $this->SetTextColor(80, 80, 80);
        $this->SetXY($x - 15, $y + $radius + 2);
        $this->Cell(30, 3, $label, 0, 0, 'C');
    }

    function header() {
        // Professional header with clean design
        $this->SetFillColor(250, 250, 252);
        $this->Rect(0, 0, 210, 40, 'F');

        // School logo aligned closer to school name
        if (file_exists('gat.png')) {
            $this->Image('gat.png', 15, 8, 28, 25); 
        } else {
            $this->SetFillColor(240, 240, 240);
            $this->Rect(15, 8, 28, 25, 'F');
            $this->SetFont('Arial', 'I', 7);
            $this->SetTextColor(180, 180, 180);
            $this->SetXY(15, 16);
            $this->Cell(28, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School Information - properly aligned with logo
        $this->SetFont('Times', 'B', 16);
        $this->SetTextColor(30, 70, 120);
        $this->SetXY(50, 8);
        $this->Cell(140, 8, 'STARS ON EARTH ACADEMY', 0, 1, 'C');

        $this->SetFont('Times', '', 10);
        $this->SetTextColor(80, 80, 90);

        // School motto centered
        $this->SetX(50);
        $this->SetFont('Times', 'I', 11);
        $this->SetTextColor(100, 120, 150);
        $this->Cell(140, 6, '"Quality Education for Future Leaders"', 0, 1, 'C');

        // Contact information
        $this->SetFont('Times', '', 9);
        $this->SetTextColor(70, 70, 80);
        $this->SetX(50);
        $this->Cell(140, 5, 'LOCATION: ABOKOBI-AKPORMAN, ACCRA', 0, 1, 'C');
        $this->SetX(50);
        $this->Cell(140, 5, 'TEL: +233246484366 / +233244457834', 0, 1, 'C');
        $this->SetX(50);
        $this->Cell(140, 5, 'EMAIL: starsonearth@gmail.com', 0, 1, 'C');
        
        // Clean separator line
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(70, 130, 180);
        $this->Line(15, 35, 195, 35);
        
        // Report title with modern styling
        $this->SetY(40);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(40, 80, 140);
        $this->Cell(190, 10, 'ACADEMIC PERFORMANCE REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(120, 120, 140);
        $this->Cell(190, 5, 'Comprehensive Student Assessment', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(2);
        $this->Cell(95, 4, 'Generated: ' . date('M j, Y g:i A'), 0, 0, 'L');
        $this->Cell(95, 4, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
        
        // Removed "CONFIDENTIAL ACADEMIC DOCUMENT" line
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
            $this->SetY(55);
            
            // Student photo - moved to the left for better separation
            $this->drawStudentPhoto($data['photo'], 20, 55);
            
            // Student details - modern layout with clear hierarchy
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(230, 230, 240);
            $this->SetLineWidth(0.3);
            
            // Main student info box
            $this->Rect(60, 53, 130, 32, 'D');
            
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(50, 70, 100);
            $this->SetXY(65, 57);
            $this->Cell(40, 7, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 15);
            $this->SetTextColor(30, 60, 110);
            $this->Cell(80, 7, strtoupper($data['name']), 0, 1, 'L');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(70, 90, 110);
            $this->SetXY(65, 66);
            $this->Cell(20, 6, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(40, 100, 160);
            $this->Cell(25, 6, $class, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(70, 90, 110);
            $this->SetX(110);
            $this->Cell(20, 6, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(160, 80, 40);
            $this->Cell(35, 6, $exam, 0, 1, 'L');
            
            // Term dates - properly separated from photo
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(80, 100, 120);
            $this->SetXY(65, 75);
            $this->Cell(28, 5, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(50, 70, 90);
            $this->Cell(40, 5, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(80, 100, 120);
            $this->SetX(125);
            $this->Cell(28, 5, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(50, 70, 90);
            $this->Cell(35, 5, $termBegins, 0, 1, 'L');

            // Academic performance table - sleek and modern
            $this->Ln(10);
            
            // Table header with professional colors
            $this->SetFillColor(60, 100, 160);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetLineWidth(0.3);
            
            $headers = ['SUBJECT', 'CLASS SCORE', 'EXAM SCORE', 'TOTAL', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [35, 25, 25, 25, 20, 40, 25];
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content with clean alternating colors
            $this->SetTextColor(0, 0, 0);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Helvetica', '', 9);
                
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

                $this->Cell($widths[0], 7, $subject, 1, 0, 'C', $fill);
                $this->Cell($widths[1], 7, $classScore, 1, 0, 'C', $fill);
                $this->Cell($widths[2], 7, $examScore, 1, 0, 'C', $fill);
                $this->Cell($widths[3], 7, $average, 1, 0, 'C', $fill);

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
                $this->SetFont('Helvetica', 'B', 9);
                $this->Cell($widths[4], 7, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Helvetica', '', 8);
                $this->Cell($widths[5], 7, $remarks, 1, 0, 'C', $fill);
                
                $this->SetTextColor(60, 80, 150);
                $this->SetFont('Helvetica', 'B', 9);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 7, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[6], 7, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Grading System in clean info box
            $this->Ln(8);
            $this->SetFillColor(245, 248, 255);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, $this->GetY(), 180, 20, 'DF');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(50, 80, 130);
            $this->SetXY(15, $this->GetY() + 3);
            $this->Cell(180, 6, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(70, 90, 120);
            $this->SetXY(15, $this->GetY());
            $this->Cell(180, 4, 'A (80-100) - Excellent | B (70-79) - Very Good | C (60-69) - Good', 0, 1, 'C');
            $this->SetXY(15, $this->GetY());
            $this->Cell(180, 4, 'D (50-59) - Average | E (40-49) - Credit | F (0-39) - Weak', 0, 1, 'C');

            // Attendance and promotion section
            $this->Ln(6);
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(220, 220, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, $this->GetY(), 180, 20, 'D');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(60, 80, 100);
            $this->SetXY(25, $this->GetY() + 4);
            $this->Cell(35, 5, 'Days Present:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(20, 5, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(60, 80, 100);
            $this->SetX(95);
            $this->Cell(30, 5, 'Total Days:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(20, 5, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(40, 120, 60);
            $this->SetXY(25, $this->GetY() + 7);
            $this->Cell(42, 6, 'Promotion Status:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(30, 100, 180);
            $this->Cell(60, 6, 'PROMOTED TO NEXT CLASS', 0, 1, 'L');

            // Comments section - clean and readable
            $this->Ln(8);
            
            // Academic remarks
            $academicRemark = $this->getAcademicRemarks();
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 90, 140);
            $this->Cell(180, 6, 'ACADEMIC REMARKS:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(40, 50, 60);
            $this->MultiCell(180, 5, $academicRemark, 0, 'L');
            
            // Conduct remarks
            $this->Ln(3);
            $conductRemark = $this->getConductRemarks();
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 90, 140);
            $this->Cell(180, 6, 'CONDUCT REMARKS:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(40, 50, 60);
            $this->MultiCell(180, 5, $conductRemark, 0, 'L');

            // Signatures section with progress circles
            $this->Ln(8);
            
            // Signature labels
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(70, 90, 110);
            $this->Cell(85, 5, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->SetX(110);
            $this->Cell(85, 5, 'Headteacher\'s Signature:', 0, 1, 'L');
            
            // Signature lines
            $this->SetDrawColor(150, 150, 150);
            $this->SetLineWidth(0.3);
            $this->Line(20, $this->GetY(), 85, $this->GetY());
            $this->Line(110, $this->GetY(), 175, $this->GetY());
            
            $this->Ln(1);
            
            // Names under signature lines
            $this->SetFont('Helvetica', 'I', 8);
            $this->SetTextColor(120, 120, 130);
            $this->Cell(85, 4, 'Name & Date', 0, 0, 'C');
            $this->SetX(110);
            $this->Cell(85, 4, 'Name & Date', 0, 1, 'C');
            
            // Acknowledgment line
            $this->SetDrawColor(150, 150, 150);
            $this->SetLineWidth(0.3);
            $this->Line(20, $this->GetY(), 85, $this->GetY());
            
            $this->Ln(0);
            $this->SetFont('Helvetica', 'I', 8);
            $this->SetTextColor(120, 120, 130);
            $this->Cell(85, 4, 'Acknowledgment', 0, 1, 'C');

            // Progress Circles - horizontally aligned below signatures
            $this->Ln(5);
            
            // Generate random percentages for progress circles
            $academicMastery = rand(70, 95);
            $levelOfUnderstanding = rand(65, 90);
            $behavior = rand(75, 98);
            
            // Circle colors
            $academicColor = [0, 100, 200]; // Blue
            $understandingColor = [40, 180, 40]; // Green
            $behaviorColor = [255, 140, 0]; // Orange
            
            // Draw progress circles horizontally aligned
            $circleY = $this->GetY();
            $circleRadius = 8;
            
            // Academic Mastery Circle
            $this->drawProgressCircle(40, $circleY, $circleRadius, $academicMastery, 'Academic Mastery', $academicColor);
            
            // Level of Understanding Circle
            $this->drawProgressCircle(105, $circleY, $circleRadius, $levelOfUnderstanding, 'Understanding', $understandingColor);
            
            // Behavior Circle
            $this->drawProgressCircle(170, $circleY, $circleRadius, $behavior, 'Behavior', $behaviorColor);
            
            $this->Ln(15); // Space after circles

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
                $this->Image($signatureImage, 140, $this->GetY() - 15, 22, 10);
            }
            
            // Progress indicator
            $this->Ln(6);
            $this->SetFont('Helvetica', 'I', 8);
            $this->SetTextColor(100, 100, 120);
            $this->Cell(180, 4, "Student Report " . $this->current_student_index . " of " . $this->total_students, 0, 1, 'R');

            // School motto at bottom
            $this->SetFont('Helvetica', 'B', 9);
            $this->SetTextColor(70, 100, 150);
            $this->Cell(180, 5, '"Quality Education for Future Leaders"', 0, 1, 'C');

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
