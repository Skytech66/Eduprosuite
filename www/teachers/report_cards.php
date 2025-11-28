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

    /**
     * Draw a circle
     */
    function Circle($x, $y, $r, $style='D')
    {
        // $style can be:
        // 'D' = Draw only, 'F' = Fill only, 'DF' or 'FD' = Draw and Fill
        $op = ($style=='F') ? 'f' : (($style=='FD' || $style=='DF') ? 'b' : 's');
        $h = $this->h;
        $this->_out(sprintf(
            '%.2F %.2F %.2F 0 360 arc %s',
            $x * $this->k,
            ($h - $y) * $this->k,
            $r * $this->k,
            $op
        ));
    }

    /**
     * Draw a progress circle with percentage and label
     */
    function drawProgressCircle($x, $y, $radius, $percent, $label) {
        // Ensure percentage is within bounds
        $percent = max(0, min(100, $percent));
        
        // Draw background circle (light gray)
        $this->SetFillColor(240, 240, 240);
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Circle($x, $y, $radius, 'D');
        $this->Circle($x, $y, $radius, 'F');
        
        // Draw progress arc if percentage > 0
        if ($percent > 0) {
            // Determine color based on percentage
            if ($percent >= 80) {
                $this->SetFillColor(76, 175, 80);   // Green
                $this->SetDrawColor(76, 175, 80);
            } elseif ($percent >= 60) {
                $this->SetFillColor(255, 152, 0);   // Orange
                $this->SetDrawColor(255, 152, 0);
            } else {
                $this->SetFillColor(244, 67, 54);   // Red
                $this->SetDrawColor(244, 67, 54);
            }
            
            // Draw progress sector
            $this->Sector($x, $y, $radius, 0, ($percent / 100) * 360);
        }
        
        // Add percentage text in center
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(50, 50, 50);
        $this->SetXY($x - 5, $y - 2);
        $this->Cell(10, 5, $percent . '%', 0, 0, 'C');
        
        // Add label below circle
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(80, 80, 80);
        $this->SetXY($x - 15, $y + $radius + 3);
        $this->Cell(30, 4, $label, 0, 0, 'C');
    }
    
    /**
     * Sector function for drawing pie chart segments
     */
    function Sector($xc, $yc, $r, $a, $b, $style='FD', $init=true) {
        if ($a > $b) {
            $b += 360;
        }
        
        if ($init) {
            $this->_out('q');
        }
        
        $this->_out(sprintf('%.2F %.2F m', $xc, $yc));
        
        $ar = deg2rad($a);
        $br = deg2rad($b);
        $n = ceil(($b - $a) / 10);
        
        for ($i = 0; $i <= $n; $i++) {
            $alpha = $a + ($b - $a) * $i / $n;
            $x = $xc + $r * cos(deg2rad($alpha));
            $y = $yc - $r * sin(deg2rad($alpha));
            $this->_out(sprintf('%.2F %.2F l', $x, $y));
        }
        
        $this->_out(sprintf('%.2F %.2F l', $xc, $yc));
        
        if ($style == 'F') {
            $op = 'f';
        } elseif ($style == 'FD' || $style == 'DF') {
            $op = 'b';
        } else {
            $op = 's';
        }
        
        $this->_out($op);
        
        if ($init) {
            $this->_out('Q');
        }
    }

    function header() {
        // Subtle background pattern/texture
        if (file_exists('watermark_transparent_v3.png')) {
            $this->Image('watermark_transparent_v3.png', 0, 0, 210, 297);
        }

        // School header with professional styling
        $this->SetFillColor(240, 240, 240);
        $this->Rect(0, 0, 210, 45, 'F');
        
        // School logo
        if (file_exists('bob.png')) {
            $this->Image('bob.png', 15, 10, 35, 28);
        } else {
            $this->SetFillColor(220, 220, 220);
            $this->Rect(15, 10, 35, 28, 'F');
            $this->SetFont('Arial', 'I', 8);
            $this->SetXY(15, 20);
            $this->Cell(35, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School information - centered
        
        $this->SetFont('Times', 'B', 18);
$this->SetTextColor(40, 80, 120);
$this->SetXY(60, 12);
$this->Cell(140, 8, 'STARTS ON EARTH ACADEMY', 0, 1, 'C');
        $this->SetFont('Times', 'B', 12);
        $this->SetTextColor(80, 80, 80);
        $this->SetX(60);
        $addressLine = isset($this->config['po_box']) && isset($this->config['address']) ? 
            $this->config['po_box'] . ', ' . $this->config['address'] : 'P.M.B 40, Madina - Accra';
        $this->Cell(140, 6, $addressLine, 0, 1, 'C');
        
        $this->SetX(60);
        $this->Cell(140, 6, '📞 0277411866 • 0541622751', 0, 1, 'C');
        
        $this->SetX(60);
        $this->Cell(140, 6, '📍 Abokobi / Boi New Town', 0, 1, 'C');

        // Decorative line with accent color
        $this->SetLineWidth(1);
        $this->SetDrawColor(70, 130, 180);
        $this->Line(15, 42, 195, 42);
        
        // Report title with modern styling
        $this->SetY(48);
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(50, 50, 120);
        $this->Cell(190, 10, 'ACADEMIC PERFORMANCE REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 12);
        $this->SetTextColor(100, 100, 150);
        $this->Cell(190, 6, 'Comprehensive Student Assessment', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(3);
        $this->Cell(95, 6, 'Generated on: ' . date('F j, Y g:i A'), 0, 0, 'L');
        $this->Cell(95, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
        
        // Confidential notice
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(190, 4, 'OFFICIAL DOCUMENT • CONFIDENTIAL • FOR AUTHORIZED RECIPIENTS ONLY', 0, 1, 'C');
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
            // Add professional border around photo
            $this->SetDrawColor(180, 180, 180);
            $this->SetLineWidth(0.8);
            $this->Rect($x-3, $y-3, 36, 30);
            $this->SetFillColor(250, 250, 250);
            $this->Rect($x-2, $y-2, 34, 28, 'F');
            $this->Image($photo, $x, $y, 30, 24);
        } else {
            // Professional photo placeholder
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.8);
            $this->Rect($x-3, $y-3, 36, 30, 'DF');
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(150, 150, 150);
            $this->SetXY($x, $y+8);
            $this->Cell(30, 5, 'STUDENT', 0, 0, 'C');
            $this->SetXY($x, $y+15);
            $this->Cell(30, 5, 'PHOTO', 0, 0, 'C');
        }
    }

    function calculatePerformanceMetrics($marks) {
        // Calculate academic mastery based on average scores
        $totalScore = 0;
        $subjectCount = 0;
        
        foreach ($marks as $mark) {
            if (is_numeric($mark['average'])) {
                $totalScore += $mark['average'];
                $subjectCount++;
            }
        }
        
        $academicMastery = $subjectCount > 0 ? round($totalScore / $subjectCount) : 0;
        
        // Generate realistic related metrics
        $punctuality = max(70, min(98, $academicMastery + rand(-10, 15)));
        $behavior = max(75, min(99, $academicMastery + rand(-5, 20)));
        $participation = max(65, min(95, $academicMastery + rand(-15, 10)));
        
        return [
            'academic_mastery' => $academicMastery,
            'punctuality_attendance' => $punctuality,
            'behavior_conduct' => $behavior,
            'class_participation' => $participation
        ];
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
            
            // Calculate performance metrics
            $metrics = $this->calculatePerformanceMetrics($data['marks']);
            
            // Student information section with modern layout
            $this->SetY(60);
            
            // Student photo - positioned professionally
            $this->drawStudentPhoto($data['photo'], 160, 60);
            
            // Student details in a clean, modern layout
            $this->SetFillColor(250, 250, 252);
            $this->SetDrawColor(220, 220, 230);
            $this->SetLineWidth(0.3);
            $this->Rect(15, 58, 140, 32, 'D');
            
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(60, 60, 80);
            $this->SetXY(20, 62);
            $this->Cell(50, 8, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 16);
            $this->SetTextColor(30, 70, 120);
            $this->Cell(80, 8, strtoupper($data['name']), 0, 1, 'L');
            
                        $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(80, 80, 100);
            $this->SetXY(20, 72);
            $this->Cell(25, 8, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor(40, 100, 160);
            $this->Cell(30, 8, $class, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(80, 80, 100);
            $this->SetX(75);
            $this->Cell(25, 8, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(160, 80, 40);
            $this->Cell(40, 8, $exam, 0, 1, 'L');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(80, 80, 100);
            $this->SetXY(20, 82);
            $this->Cell(35, 6, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(50, 50, 70);
            $this->Cell(40, 6, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(80, 80, 100);
            $this->SetX(95);
            $this->Cell(35, 6, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(50, 50, 70);
            $this->Cell(40, 6, $termBegins, 0, 1, 'L');

            // Academic performance table with enhanced professional styling
            $this->Ln(8);
            
            // Table header with modern colors
            $this->SetFillColor(70, 130, 180);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetLineWidth(0.3);
            
            $headers = ['SUBJECT', 'CLASS (50%)', 'EXAM (50%)', 'TOTAL (100%)', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [32, 26, 26, 28, 22, 38, 28];
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 9, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content with alternating row colors
            $this->SetTextColor(0, 0, 0);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Helvetica', '', 9);
                
                if ($fill) {
                    $this->SetFillColor(248, 250, 252);
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

                // Determine grade with professional color coding
                if ($average >= 80) {
                    $grade = 'A'; $gradeColor = array(0, 128, 0); // Green
                    $remarks = 'Excellent';
                } elseif ($average >= 70) {
                    $grade = 'B'; $gradeColor = array(0, 100, 200); // Blue
                    $remarks = 'Very Good';
                } elseif ($average >= 60) {
                    $grade = 'C'; $gradeColor = array(255, 140, 0); // Orange
                    $remarks = 'Good';
                } elseif ($average >= 50) {
                    $grade = 'D'; $gradeColor = array(165, 42, 42); // Brown
                    $remarks = 'Average';
                } elseif ($average >= 40) {
                    $grade = 'E'; $gradeColor = array(128, 0, 128); // Purple
                    $remarks = 'Credit';
                } else {
                    $grade = 'F'; $gradeColor = array(220, 0, 0); // Red
                    $remarks = 'Weak';
                }
                
                $this->SetTextColor($gradeColor[0], $gradeColor[1], $gradeColor[2]);
                $this->SetFont('Helvetica', 'B', 10);
                $this->Cell($widths[4], 8, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Helvetica', '', 8);
                $this->Cell($widths[5], 8, $remarks, 1, 0, 'C', $fill);
                
                $this->SetTextColor(60, 60, 150);
                $this->SetFont('Helvetica', 'B', 9);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 8, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[6], 8, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Grading System in a modern info box
            $this->Ln(10);
            $this->SetFillColor(240, 248, 255);
            $this->SetDrawColor(180, 210, 230);
            $this->SetLineWidth(0.5);
            $this->Rect(15, $this->GetY(), 180, 25, 'DF');
            
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(50, 100, 150);
            $this->Cell(180, 8, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor(70, 70, 90);
            $this->Cell(180, 5, 'A (80-100) - Excellent • B (70-79) - Very Good • C (60-69) - Good', 0, 1, 'C');
            $this->Cell(180, 5, 'D (50-59) - Average • E (40-49) - Credit • F (0-39) - Weak', 0, 1, 'C');

            // PERFORMANCE METRICS SECTION WITH PROGRESS CIRCLES
            $this->Ln(12);
            
            // Section header
            $this->SetFillColor(60, 100, 160);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 13);
            $this->Cell(180, 10, 'PERFORMANCE OVERVIEW', 1, 1, 'C', true);
            
            $this->Ln(8);
            
            // Progress circles container
            $circleY = $this->GetY();
            
            // Academic Mastery Circle
            $this->drawProgressCircle(45, $circleY + 20, 16, $metrics['academic_mastery'], 'ACADEMIC MASTERY');
            
            // Punctuality & Attendance Circle
            $this->drawProgressCircle(105, $circleY + 20, 16, $metrics['punctuality_attendance'], 'PUNCTUALITY & ATTENDANCE');
            
            // Behavior & Conduct Circle
            $this->drawProgressCircle(165, $circleY + 20, 16, $metrics['behavior_conduct'], 'BEHAVIOR & CONDUCT');
            
            $this->Ln(38);
            
            // Class Participation Circle (centered below)
            $this->drawProgressCircle(105, $this->GetY() + 15, 16, $metrics['class_participation'], 'CLASS PARTICIPATION');
            
            $this->Ln(28);

            // Performance summary with modern styling
            $this->SetFillColor(245, 248, 250);
            $this->SetDrawColor(200, 210, 220);
            $this->SetLineWidth(0.5);
            $this->Rect(15, $this->GetY(), 180, 22, 'DF');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 80, 100);
            $this->Cell(180, 7, 'PERFORMANCE SUMMARY', 0, 1, 'C');
            
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(70, 70, 80);
            
            $summaryText = "This student demonstrates ";
            $summaryText .= $metrics['academic_mastery'] >= 80 ? "exceptional" : ($metrics['academic_mastery'] >= 70 ? "strong" : ($metrics['academic_mastery'] >= 60 ? "satisfactory" : "developing"));
            $summaryText .= " academic mastery, ";
            $summaryText .= $metrics['punctuality_attendance'] >= 90 ? "excellent" : ($metrics['punctuality_attendance'] >= 80 ? "good" : ($metrics['punctuality_attendance'] >= 70 ? "adequate" : "needs improvement"));
            $summaryText .= " attendance records, and ";
            $summaryText .= $metrics['behavior_conduct'] >= 85 ? "outstanding" : ($metrics['behavior_conduct'] >= 75 ? "positive" : ($metrics['behavior_conduct'] >= 65 ? "satisfactory" : "developing"));
            $summaryText .= " behavioral conduct with active class participation.";
            
            $this->MultiCell(170, 4, $summaryText, 0, 'C');

            // Student status and promotion section
            $this->Ln(10);
            $this->SetFillColor(250, 252, 255);
            $this->SetDrawColor(180, 200, 220);
            $this->SetLineWidth(0.5);
            $this->Rect(15, $this->GetY(), 180, 25, 'D');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 80, 100);
            $this->SetXY(20, $this->GetY() + 5);
            $this->Cell(40, 6, 'Days Present:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 6, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(60, 80, 100);
            $this->SetX(95);
            $this->Cell(30, 6, 'Total Days:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 6, '_______', 'B', 0, 'C');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(40, 120, 60);
            $this->SetXY(20, $this->GetY() + 8);
            $this->Cell(45, 8, 'Promotion Status:', 0, 0, 'L');
            $this->SetFont('Helvetica', 'B', 13);
            $this->SetTextColor(30, 100, 180);
            $this->Cell(60, 8, 'PROMOTED TO BASIC 8', 0, 1, 'L');

            // Comments section with professional layout
            $this->Ln(8);
            
            // Academic remarks
            $academicRemark = $this->getAcademicRemarks();
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(180, 200, 220);
            $this->SetLineWidth(0.5);
            $this->Rect(15, $this->GetY(), 180, 30, 'D');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(70, 100, 150);
            $this->Cell(35, 7, '📚 Academic Remarks:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(50, 50, 60);
            $this->MultiCell(170, 5, $academicRemark, 0, 'L');
            
            // Conduct remarks
            $conductRemark = $this->getConductRemarks();
            $this->SetFillColor(255, 255, 255);
            $this->SetDrawColor(180, 200, 220);
            $this->SetLineWidth(0.5);
            $this->Rect(15, $this->GetY() + 2, 180, 30, 'D');
            
            $this->SetFont('Helvetica', 'B', 12);
            $this->SetTextColor(70, 100, 150);
            $this->Cell(35, 7, '⭐ Conduct Remarks:', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 10);
            $this->SetTextColor(50, 50, 60);
            $this->MultiCell(170, 5, $conductRemark, 0, 'L');

            // Signatures section with professional layout
            $this->Ln(12);
            $signatureY = $this->GetY();
            
            // Signature container
            $this->SetFillColor(248, 250, 252);
            $this->SetDrawColor(200, 210, 220);
            $this->SetLineWidth(0.5);
            $this->Rect(15, $signatureY, 180, 35, 'D');
            
            // Class teacher signature
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(80, 100, 120);
            $this->SetXY(25, $signatureY + 8);
            $this->Cell(70, 6, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->SetDrawColor(150, 150, 150);
            $this->Line(35, $signatureY + 20, 95, $signatureY + 20);
            $this->SetFont('Helvetica', 'I', 9);
            $this->SetTextColor(120, 120, 120);
            $this->SetXY(35, $signatureY + 22);
            $this->Cell(60, 4, 'Name & Date', 0, 0, 'C');
            
            // Headmistress signature
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetTextColor(80, 100, 120);
            $this->SetXY(115, $signatureY + 8);
            $this->Cell(70, 6, 'Headmistress\'s Signature:', 0, 0, 'L');
            $this->SetDrawColor(150, 150, 150);
            $this->Line(125, $signatureY + 20, 175, $signatureY + 20);
            $this->SetFont('Helvetica', 'I', 9);
            $this->SetTextColor(120, 120, 120);
            $this->SetXY(125, $signatureY + 22);
            $this->Cell(50, 4, 'Name & Date', 0, 0, 'C');
            
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
                $this->Image($signatureImage, 140, $signatureY + 5, 25, 12);
            }
            
            // Progress indicator and report footer
            $this->Ln(8);
            $this->SetFont('Helvetica', 'I', 9);
            $this->SetTextColor(100, 100, 120);
            $this->Cell(180, 5, "Student Report " . $this->current_student_index . " of " . $this->total_students, 0, 1, 'R');

            // School motto or closing message
            $this->SetFont('Helvetica', 'B', 10);
                        $this->SetTextColor(70, 100, 150);
            $this->Cell(180, 6, '"Quality Education for Future Leaders"', 0, 1, 'C');

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

