<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent "headers already sent" errors
ob_start();

// Supabase connection (must use pooler)
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

// Function to convert a number to its ordinal representation
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
        $this->Rect(0, 0, 210, 40, 'F');
        
        // School logo
        if (file_exists('bob.png')) {
            $this->Image('bob.png', 15, 8, 30, 25);
        } else {
            $this->SetFillColor(220, 220, 220);
            $this->Rect(15, 8, 30, 25, 'F');
            $this->SetFont('Arial', 'I', 8);
            $this->SetXY(15, 16);
            $this->Cell(30, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School information - right aligned
        $this->SetFont('Times', 'B', 16);
        $this->SetXY(50, 8);
        $this->Cell(140, 8, isset($this->config['school_name']) ? strtoupper($this->config['school_name']) : 'SCHOOL NAME', 0, 1, 'C');
        
        $this->SetFont('Times', '', 10);
        $this->SetX(50);
        $addressLine = isset($this->config['po_box']) && isset($this->config['address']) ? 
            $this->config['po_box'] . ', ' . $this->config['address'] : 'P.M.B 40, Madina';
        $this->Cell(140, 6, $addressLine, 0, 1, 'C');
        
        $this->SetX(50);
        $this->Cell(140, 6, 'TEL: 0277411866 / 0541622751', 0, 1, 'C');
        
        $this->SetX(50);
        $this->Cell(140, 6, 'LOCATION: Abokobi / Boi New Town', 0, 1, 'C');

        // Decorative line
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(100, 100, 150);
        $this->Line(10, 38, 200, 38);
        
        // Report title
        $this->SetY(42);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(50, 50, 120);
        $this->Cell(190, 10, 'STUDENT ACADEMIC REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 11);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(190, 6, 'Comprehensive Performance Assessment', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-18);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(2);
        $this->Cell(95, 6, 'Generated on: ' . date('F j, Y'), 0, 0, 'L');
        $this->Cell(95, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
        
        // Confidential notice
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(190, 4, 'CONFIDENTIAL: For authorized personnel only', 0, 1, 'C');
    }

    function getRemarks() {
        $remarks = [
            "Making steady progress keep it up.",
            "A consistent effort will lead to improvement.",
            "Shows potential, needs to stay focused.",
            "Can achieve more with greater concentration.",
            "A little more effort will bring better results.",
            "Shows interest but needs to work more independently.",
            "Needs to participate more actively in class.",
            "Good attitude toward learning keep improving.",
            "Capable of doing better with more dedication.",
            "Beginning to take studies more seriously.",
            "Needs to revise lessons more regularly.",
            "Can perform better with greater consistency.",
            "A quiet student, encouraged to engage more.",
            "Demonstrates average understanding, more practice needed.",
            "Should aim to submit all work on time.",
            "Needs to improve attention during lessons.",
            "Able to grasp concepts but needs reinforcement.",
            "Has potential, needs to be more confident.",
            "Tries hard but needs better study habits.",
            "Needs to avoid rushing through work.",
            "A positive attitude, but focus needs improvement.",
            "Has improved slightly, more effort needed.",
            "Capable of achieving higher results.",
            "Needs to seek help when struggling.",
            "Should stay on task more consistently.",
            "A good foundation needs to build on it.",
            "Shows improvement but must keep it up.",
            "Can benefit from regular revision.",
            "Can achieve higher potentials.",
            "Shows average results can improve with guidance.",
            "Can do better if distractions are minimized.",
            "Improvement seen encouraged to continue.",
            "Progressing slowly but steadily.",
            "Should challenge self with more effort.",
            "Needs to ask more questions when unsure.",
            "A cooperative student needs to show more initiative.",
            "Needs to take learning more seriously.",
            "Has the ability but needs to apply it more.",
            "Should strive to exceed basic expectations.",
            "Progresses at an average pace can do more.",
            "Will benefit from a more focused approach.",
            "Good behavior needs academic push.",
            "Needs to improve work completion rate.",
            "Can shine with more confidence.",
            "Should improve organization of work.",
            "Shows average results across subjects.",
            "Should build stronger study routines.",
            "Can reach greater heights with extra effort.",
            "Encouraged to keep working hard and not settle."
        ];
        return $remarks[array_rand($remarks)];
    }

    function drawStudentPhoto($photo, $x, $y) {
        if (!empty($photo) && file_exists($photo)) {
            // Add border around photo
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 32, 26);
            $this->Image($photo, $x, $y, 28, 22);
        } else {
            // Professional placeholder
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(220, 220, 220);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 32, 26, 'DF');
            $this->SetFont('Arial', 'I', 7);
            $this->SetTextColor(150, 150, 150);
            $this->SetXY($x, $y+8);
            $this->Cell(28, 5, 'PHOTO', 0, 0, 'C');
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

        $conductRemarks = [
            "Consistently demonstrates outstanding behavior and a positive attitude.",
            "Exemplifies respect, responsibility, and integrity in all actions.",
            "Engages actively and sets a positive example for peers.",
            "Shows great potential—would benefit from improved focus during class.",
            "Adheres to classroom expectations and contributes positively to the learning environment.",
            "Demonstrates empathy, kindness, and strong interpersonal skills.",
            "Encouraged to show greater respect and attentiveness during lessons.",
            "Exhibits natural leadership and inspires others through actions.",
            "Remarkable progress in behavior—keep up the great effort!",
            "Takes initiative and displays a strong sense of responsibility.",
            "Works well independently and in group settings.",
            "Demonstrates resilience and perseverance in challenging tasks.",
            "Is respectful to peers and teachers at all times.",
            "A reliable and dependable student.",
            "Cheerful and brings a positive energy to the class.",
            "Actively listens and contributes meaningfully to discussions.",
            "Regularly helps and encourages classmates.",
            "Handles responsibilities with maturity and care.",
            "Stays calm under pressure and manages conflict well.",
            "Needs gentle reminders to stay on task but shows willingness to improve.",
            "Maintains a positive attitude towards learning and growth.",
            "Is developing good self-control and patience.",
            "Willing to accept feedback and strives to do better.",
            "Consistently completes tasks with care and attention.",
            "Needs to work on being more cooperative during group activities.",
            "Kind-hearted and always ready to support others.",
            "Takes pride in personal and academic growth.",
            "Enthusiastic and motivated to learn new things.",
            "Sometimes distracted—encouraged to stay focused during lessons.",
            "A great example of punctuality and preparedness.",
            "Respectfully communicates with peers and adults.",
            "Demonstrates honesty and trustworthiness.",
            "Increasingly confident in expressing thoughts and ideas.",
            "Appreciates structure and responds well to routines.",
            "Can improve by being more mindful of class rules.",
            "Always willing to take part in class activities.",
            "Demonstrates a strong sense of fairness and justice.",
            "Well-mannered and considerate of others' feelings.",
            "Responds positively to encouragement and support.",
            "Making steady improvement in behavior and attitude.",
            "Demonstrates a calm and thoughtful presence.",
            "Follows instructions carefully and consistently.",
            "Is beginning to show initiative in taking responsibility.",
            "Needs to focus on being more respectful during class discussions.",
            "Displays maturity in handling challenges.",
            "Always completes tasks on time and with effort.",
            "Cooperates well and contributes meaningfully to team efforts.",
            "Learns from mistakes and shows a growth mindset.",
            "Needs reminders but shows willingness to correct behavior.",
            "Polite, respectful, and a joy to have in class.",
            "An excellent role model for classmates."
        ];

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
        $termEnds = isset($this->config['term_ends']) ? $this->config['term_ends'] : '7th August, 2025';
        $termBegins = isset($this->config['term_begins']) ? $this->config['term_begins'] : '2nd September, 2025';

        foreach ($students as $admno => $data) {
            $this->current_student_index++;
            
            // Calculate performance metrics
            $metrics = $this->calculatePerformanceMetrics($data['marks']);
            
            // Student information section with modern layout
            $this->SetY(55);
            
            // Student photo
            $this->drawStudentPhoto($data['photo'], 160, 55);
            
            // Student details in a clean layout
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(40, 8, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(80, 8, $data['name'], 0, 1, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(40, 8, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13);
            $this->SetTextColor(40, 80, 120);
            $this->Cell(40, 8, $class, 0, 0, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(30, 8, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(40, 80, 120);
            $this->Cell(40, 8, $exam, 0, 1, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(45, 8, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(45, 8, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(45, 8, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(45, 8, $termBegins, 0, 1, 'L');

            // Academic performance table with enhanced styling
            $this->Ln(5);
            
            // Table header with professional colors
            $this->SetFillColor(60, 100, 150);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Times', 'B', 11);
            $this->SetLineWidth(0.3);
            
            $headers = ['SUBJECT', 'CLASS (50%)', 'EXAM (50%)', 'TOTAL (100%)', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [30, 25, 25, 25, 20, 35, 25];
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content
            $this->SetTextColor(0, 0, 0);
            $this->SetFillColor(245, 248, 250);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Arial', '', 9);
                
                if ($fill) {
                    $this->SetFillColor(245, 248, 250);
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

                // Determine grade with color coding
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
                $this->SetFont('Arial', 'B', 9);
                $this->Cell($widths[4], 7, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Arial', '', 8);
                $this->Cell($widths[5], 7, $remarks, 1, 0, 'C', $fill);
                
                $this->SetTextColor(60, 60, 150);
                $this->SetFont('Arial', 'B', 9);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 7, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[6], 7, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Grading System in a clean box
            $this->Ln(8);
            $this->SetFillColor(240, 245, 250);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.5);
            $this->Rect(10, $this->GetY(), 190, 22, 'DF');
            
            $this->SetFont('Arial', 'BU', 12);
            $this->SetTextColor(50, 80, 120);
            $this->Cell(190, 8, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Times', 'B', 10);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(190, 5, 'A - Excellent (80 - 100)        B - Very Good (70 - 79)        C - Good (60 - 69)', 0, 1, 'C');
            $this->Cell(190, 5, 'D - Average (50 - 59)           E - Credit (40 - 49)           F - Weak (0 - 39)', 0, 1, 'C');

            // PERFORMANCE METRICS SECTION WITH PROGRESS CIRCLES
            $this->Ln(10);
            
            // Section header
            $this->SetFillColor(60, 100, 150);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(190, 8, 'PERFORMANCE OVERVIEW', 1, 1, 'C', true);
            
            $this->Ln(8);
            
            // Progress circles container
            $circleY = $this->GetY();
            
            // Academic Mastery Circle
            $this->drawProgressCircle(40, $circleY + 20, 15, $metrics['academic_mastery'], 'ACADEMIC MASTERY');
            
            // Punctuality & Attendance Circle
            $this->drawProgressCircle(105, $circleY + 20, 15, $metrics['punctuality_attendance'], 'PUNCTUALITY & ATTENDANCE');
            
            // Behavior & Conduct Circle
            $this->drawProgressCircle(170, $circleY + 20, 15, $metrics['behavior_conduct'], 'BEHAVIOR & CONDUCT');
            
            $this->Ln(35);
            
            // Class Participation Circle (centered below)
            $this->drawProgressCircle(105, $this->GetY() + 15, 15, $metrics['class_participation'], 'CLASS PARTICIPATION');
            
            $this->Ln(25);

            // Performance summary
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(220, 220, 220);
            $this->SetLineWidth(0.3);
            $this->Rect(10, $this->GetY(), 190, 20, 'D');
            
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(190, 6, 'PERFORMANCE SUMMARY', 0, 1, 'C');
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(80, 80, 80);
            
            $summaryText = "This student demonstrates ";
            $summaryText .= $metrics['academic_mastery'] >= 80 ? "exceptional" : ($metrics['academic_mastery'] >= 70 ? "strong" : ($metrics['academic_mastery'] >= 60 ? "satisfactory" : "developing"));
            $summaryText .= " academic mastery, ";
            $summaryText .= $metrics['punctuality_attendance'] >= 90 ? "excellent" : ($metrics['punctuality_attendance'] >= 80 ? "good" : ($metrics['punctuality_attendance'] >= 70 ? "adequate" : "needs improvement"));
            $summaryText .= " attendance, and ";
            $summaryText .= $metrics['behavior_conduct'] >= 85 ? "outstanding" : ($metrics['behavior_conduct'] >= 75 ? "positive" : ($metrics['behavior_conduct'] >= 65 ? "satisfactory" : "developing"));
            $summaryText .= " behavioral conduct.";
            
            $this->MultiCell(180, 4, $summaryText, 0, 'C');

            // Student status section
            $this->Ln(8);
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(60, 60, 60);
            
            $this->Cell(40, 8, 'Attendance:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 8, '______', 'B', 0, 'C');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(30, 8, 'Out of:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 8, '______', 'B', 0, 'C');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(40, 100, 40);
            $this->Cell(45, 8, 'Promoted to:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(30, 80, 160);
            $this->Cell(25, 8, 'Basic 8', 0, 1, 'L');

            // Comments section with improved layout
            $this->Ln(5);
            $remarks = $this->getRemarks();
            $conductRemark = $conductRemarks[array_rand($conductRemarks)];
            
            // Academic remarks
            $this->SetFillColor(250, 250, 252);
            $this->SetDrawColor(220, 220, 220);
            $this->Rect(10, $this->GetY(), 190, 22, 'D');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(35, 6, 'Academic Remarks:', 0, 1, 'L');
            $this->SetFont('Times', '', 10);
            $this->SetTextColor(50, 50, 50);
            $this->MultiCell(180, 5, $remarks, 0, 'L');
            
            // Conduct remarks
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(35, 6, 'Conduct Remarks:', 0, 1, 'L');
            $this->SetFont('Times', '', 10);
            $this->SetTextColor(50, 50, 50);
            $this->MultiCell(180, 5, $conductRemark, 0, 'L');

            // Signatures section with professional layout
            $this->Ln(8);
            $signatureY = $this->GetY();
            
            // Class teacher signature
            $this->SetFont('Times', 'B', 10);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(80, 6, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->SetDrawColor(150, 150, 150);
            $this->Line(45, $signatureY + 8, 85, $signatureY + 8);
            
            // Headmistress signature
            $this->SetX(110);
            $this->Cell(80, 6, 'Headmistress\'s Signature:', 0, 1, 'L');
            $this->Line(125, $signatureY + 8, 165, $signatureY + 8);
            
            // Add signature image if available
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
                $this->Image($signatureImage, 130, $signatureY - 2, 25, 12);
            }
            
            // Progress indicator
            $this->Ln(5);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(190, 4, "Report " . $this->current_student_index . " of " . $this->total_students, 0, 1, 'R');

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
    $pdf->Output('student_academic_report.pdf', 'I');
    
    // Flush the output buffer
    ob_end_flush();
} else {
    die("Invalid request method.");
}
?><?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent "headers already sent" errors
ob_start();

// Supabase connection (must use pooler)
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

// Function to convert a number to its ordinal representation
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
        $this->Rect(0, 0, 210, 40, 'F');
        
        // School logo
        if (file_exists('bob.png')) {
            $this->Image('bob.png', 15, 8, 30, 25);
        } else {
            $this->SetFillColor(220, 220, 220);
            $this->Rect(15, 8, 30, 25, 'F');
            $this->SetFont('Arial', 'I', 8);
            $this->SetXY(15, 16);
            $this->Cell(30, 5, 'SCHOOL LOGO', 0, 0, 'C');
        }

        // School information - right aligned
        $this->SetFont('Times', 'B', 16);
        $this->SetXY(50, 8);
        $this->Cell(140, 8, isset($this->config['school_name']) ? strtoupper($this->config['school_name']) : 'SCHOOL NAME', 0, 1, 'C');
        
        $this->SetFont('Times', '', 10);
        $this->SetX(50);
        $addressLine = isset($this->config['po_box']) && isset($this->config['address']) ? 
            $this->config['po_box'] . ', ' . $this->config['address'] : 'P.M.B 40, Madina';
        $this->Cell(140, 6, $addressLine, 0, 1, 'C');
        
        $this->SetX(50);
        $this->Cell(140, 6, 'TEL: 0277411866 / 0541622751', 0, 1, 'C');
        
        $this->SetX(50);
        $this->Cell(140, 6, 'LOCATION: Abokobi / Boi New Town', 0, 1, 'C');

        // Decorative line
        $this->SetLineWidth(0.8);
        $this->SetDrawColor(100, 100, 150);
        $this->Line(10, 38, 200, 38);
        
        // Report title
        $this->SetY(42);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(50, 50, 120);
        $this->Cell(190, 10, 'STUDENT ACADEMIC REPORT', 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 11);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(190, 6, 'Comprehensive Performance Assessment', 0, 1, 'C');
    }

    function footer() {
        $this->SetY(-18);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 100, 100);
        
        // Footer line
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(2);
        $this->Cell(95, 6, 'Generated on: ' . date('F j, Y'), 0, 0, 'L');
        $this->Cell(95, 6, 'Page ' . $this->PageNo() . ' of {nb}', 0, 1, 'R');
        
        // Confidential notice
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(190, 4, 'CONFIDENTIAL: For authorized personnel only', 0, 1, 'C');
    }

    function getRemarks() {
        $remarks = [
            "Making steady progress keep it up.",
            "A consistent effort will lead to improvement.",
            "Shows potential, needs to stay focused.",
            "Can achieve more with greater concentration.",
            "A little more effort will bring better results.",
            "Shows interest but needs to work more independently.",
            "Needs to participate more actively in class.",
            "Good attitude toward learning keep improving.",
            "Capable of doing better with more dedication.",
            "Beginning to take studies more seriously.",
            "Needs to revise lessons more regularly.",
            "Can perform better with greater consistency.",
            "A quiet student, encouraged to engage more.",
            "Demonstrates average understanding, more practice needed.",
            "Should aim to submit all work on time.",
            "Needs to improve attention during lessons.",
            "Able to grasp concepts but needs reinforcement.",
            "Has potential, needs to be more confident.",
            "Tries hard but needs better study habits.",
            "Needs to avoid rushing through work.",
            "A positive attitude, but focus needs improvement.",
            "Has improved slightly, more effort needed.",
            "Capable of achieving higher results.",
            "Needs to seek help when struggling.",
            "Should stay on task more consistently.",
            "A good foundation needs to build on it.",
            "Shows improvement but must keep it up.",
            "Can benefit from regular revision.",
            "Can achieve higher potentials.",
            "Shows average results can improve with guidance.",
            "Can do better if distractions are minimized.",
            "Improvement seen encouraged to continue.",
            "Progressing slowly but steadily.",
            "Should challenge self with more effort.",
            "Needs to ask more questions when unsure.",
            "A cooperative student needs to show more initiative.",
            "Needs to take learning more seriously.",
            "Has the ability but needs to apply it more.",
            "Should strive to exceed basic expectations.",
            "Progresses at an average pace can do more.",
            "Will benefit from a more focused approach.",
            "Good behavior needs academic push.",
            "Needs to improve work completion rate.",
            "Can shine with more confidence.",
            "Should improve organization of work.",
            "Shows average results across subjects.",
            "Should build stronger study routines.",
            "Can reach greater heights with extra effort.",
            "Encouraged to keep working hard and not settle."
        ];
        return $remarks[array_rand($remarks)];
    }

    function drawStudentPhoto($photo, $x, $y) {
        if (!empty($photo) && file_exists($photo)) {
            // Add border around photo
            $this->SetDrawColor(200, 200, 200);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 32, 26);
            $this->Image($photo, $x, $y, 28, 22);
        } else {
            // Professional placeholder
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(220, 220, 220);
            $this->SetLineWidth(0.5);
            $this->Rect($x-2, $y-2, 32, 26, 'DF');
            $this->SetFont('Arial', 'I', 7);
            $this->SetTextColor(150, 150, 150);
            $this->SetXY($x, $y+8);
            $this->Cell(28, 5, 'PHOTO', 0, 0, 'C');
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

        $conductRemarks = [
            "Consistently demonstrates outstanding behavior and a positive attitude.",
            "Exemplifies respect, responsibility, and integrity in all actions.",
            "Engages actively and sets a positive example for peers.",
            "Shows great potential—would benefit from improved focus during class.",
            "Adheres to classroom expectations and contributes positively to the learning environment.",
            "Demonstrates empathy, kindness, and strong interpersonal skills.",
            "Encouraged to show greater respect and attentiveness during lessons.",
            "Exhibits natural leadership and inspires others through actions.",
            "Remarkable progress in behavior—keep up the great effort!",
            "Takes initiative and displays a strong sense of responsibility.",
            "Works well independently and in group settings.",
            "Demonstrates resilience and perseverance in challenging tasks.",
            "Is respectful to peers and teachers at all times.",
            "A reliable and dependable student.",
            "Cheerful and brings a positive energy to the class.",
            "Actively listens and contributes meaningfully to discussions.",
            "Regularly helps and encourages classmates.",
            "Handles responsibilities with maturity and care.",
            "Stays calm under pressure and manages conflict well.",
            "Needs gentle reminders to stay on task but shows willingness to improve.",
            "Maintains a positive attitude towards learning and growth.",
            "Is developing good self-control and patience.",
            "Willing to accept feedback and strives to do better.",
            "Consistently completes tasks with care and attention.",
            "Needs to work on being more cooperative during group activities.",
            "Kind-hearted and always ready to support others.",
            "Takes pride in personal and academic growth.",
            "Enthusiastic and motivated to learn new things.",
            "Sometimes distracted—encouraged to stay focused during lessons.",
            "A great example of punctuality and preparedness.",
            "Respectfully communicates with peers and adults.",
            "Demonstrates honesty and trustworthiness.",
            "Increasingly confident in expressing thoughts and ideas.",
            "Appreciates structure and responds well to routines.",
            "Can improve by being more mindful of class rules.",
            "Always willing to take part in class activities.",
            "Demonstrates a strong sense of fairness and justice.",
            "Well-mannered and considerate of others' feelings.",
            "Responds positively to encouragement and support.",
            "Making steady improvement in behavior and attitude.",
            "Demonstrates a calm and thoughtful presence.",
            "Follows instructions carefully and consistently.",
            "Is beginning to show initiative in taking responsibility.",
            "Needs to focus on being more respectful during class discussions.",
            "Displays maturity in handling challenges.",
            "Always completes tasks on time and with effort.",
            "Cooperates well and contributes meaningfully to team efforts.",
            "Learns from mistakes and shows a growth mindset.",
            "Needs reminders but shows willingness to correct behavior.",
            "Polite, respectful, and a joy to have in class.",
            "An excellent role model for classmates."
        ];

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
        $termEnds = isset($this->config['term_ends']) ? $this->config['term_ends'] : '7th August, 2025';
        $termBegins = isset($this->config['term_begins']) ? $this->config['term_begins'] : '2nd September, 2025';

        foreach ($students as $admno => $data) {
            $this->current_student_index++;
            
            // Calculate performance metrics
            $metrics = $this->calculatePerformanceMetrics($data['marks']);
            
            // Student information section with modern layout
            $this->SetY(55);
            
            // Student photo
            $this->drawStudentPhoto($data['photo'], 160, 55);
            
            // Student details in a clean layout
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(40, 8, 'STUDENT:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(80, 8, $data['name'], 0, 1, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(40, 8, 'CLASS:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13);
            $this->SetTextColor(40, 80, 120);
            $this->Cell(40, 8, $class, 0, 0, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(30, 8, 'EXAM:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(40, 80, 120);
            $this->Cell(40, 8, $exam, 0, 1, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(45, 8, 'TERM ENDS:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(45, 8, $termEnds, 0, 0, 'L');
            
            $this->SetFont('Times', 'B', 12);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(45, 8, 'NEXT TERM:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(45, 8, $termBegins, 0, 1, 'L');

            // Academic performance table with enhanced styling
            $this->Ln(5);
            
            // Table header with professional colors
            $this->SetFillColor(60, 100, 150);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Times', 'B', 11);
            $this->SetLineWidth(0.3);
            
            $headers = ['SUBJECT', 'CLASS (50%)', 'EXAM (50%)', 'TOTAL (100%)', 'GRADE', 'REMARKS', 'POSITION'];
            $widths = [30, 25, 25, 25, 20, 35, 25];
            
            for ($i = 0; $i < count($headers); $i++) {
                $this->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C', true);
            }
            $this->Ln();
            
            // Table content
            $this->SetTextColor(0, 0, 0);
            $this->SetFillColor(245, 248, 250);
            $fill = false;
            
            foreach ($data['marks'] as $row) {
                $this->SetFont('Arial', '', 9);
                
                if ($fill) {
                    $this->SetFillColor(245, 248, 250);
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

                // Determine grade with color coding
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
                $this->SetFont('Arial', 'B', 9);
                $this->Cell($widths[4], 7, $grade, 1, 0, 'C', $fill);
                
                $this->SetTextColor(0, 0, 0);
                $this->SetFont('Arial', '', 8);
                $this->Cell($widths[5], 7, $remarks, 1, 0, 'C', $fill);
                
                $this->SetTextColor(60, 60, 150);
                $this->SetFont('Arial', 'B', 9);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($widths[6], 7, ordinal($originalPosition), 1, 0, 'C', $fill);
                } else {
                    $this->Cell($widths[6], 7, 'N/A', 1, 0, 'C', $fill);
                }
                $this->Ln();
                
                $fill = !$fill;
                $this->SetTextColor(0, 0, 0);
            }

            // Grading System in a clean box
            $this->Ln(8);
            $this->SetFillColor(240, 245, 250);
            $this->SetDrawColor(200, 210, 230);
            $this->SetLineWidth(0.5);
            $this->Rect(10, $this->GetY(), 190, 22, 'DF');
            
            $this->SetFont('Arial', 'BU', 12);
            $this->SetTextColor(50, 80, 120);
            $this->Cell(190, 8, 'GRADING SYSTEM', 0, 1, 'C');
            
            $this->SetFont('Times', 'B', 10);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(190, 5, 'A - Excellent (80 - 100)        B - Very Good (70 - 79)        C - Good (60 - 69)', 0, 1, 'C');
            $this->Cell(190, 5, 'D - Average (50 - 59)           E - Credit (40 - 49)           F - Weak (0 - 39)', 0, 1, 'C');

            // PERFORMANCE METRICS SECTION WITH PROGRESS CIRCLES
            $this->Ln(10);
            
            // Section header
            $this->SetFillColor(60, 100, 150);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(190, 8, 'PERFORMANCE OVERVIEW', 1, 1, 'C', true);
            
            $this->Ln(8);
            
            // Progress circles container
            $circleY = $this->GetY();
            
            // Academic Mastery Circle
            $this->drawProgressCircle(40, $circleY + 20, 15, $metrics['academic_mastery'], 'ACADEMIC MASTERY');
            
            // Punctuality & Attendance Circle
            $this->drawProgressCircle(105, $circleY + 20, 15, $metrics['punctuality_attendance'], 'PUNCTUALITY & ATTENDANCE');
            
            // Behavior & Conduct Circle
            $this->drawProgressCircle(170, $circleY + 20, 15, $metrics['behavior_conduct'], 'BEHAVIOR & CONDUCT');
            
            $this->Ln(35);
            
            // Class Participation Circle (centered below)
            $this->drawProgressCircle(105, $this->GetY() + 15, 15, $metrics['class_participation'], 'CLASS PARTICIPATION');
            
            $this->Ln(25);

            // Performance summary
            $this->SetFillColor(245, 245, 245);
            $this->SetDrawColor(220, 220, 220);
            $this->SetLineWidth(0.3);
            $this->Rect(10, $this->GetY(), 190, 20, 'D');
            
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(190, 6, 'PERFORMANCE SUMMARY', 0, 1, 'C');
            
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(80, 80, 80);
            
            $summaryText = "This student demonstrates ";
            $summaryText .= $metrics['academic_mastery'] >= 80 ? "exceptional" : ($metrics['academic_mastery'] >= 70 ? "strong" : ($metrics['academic_mastery'] >= 60 ? "satisfactory" : "developing"));
            $summaryText .= " academic mastery, ";
            $summaryText .= $metrics['punctuality_attendance'] >= 90 ? "excellent" : ($metrics['punctuality_attendance'] >= 80 ? "good" : ($metrics['punctuality_attendance'] >= 70 ? "adequate" : "needs improvement"));
            $summaryText .= " attendance, and ";
            $summaryText .= $metrics['behavior_conduct'] >= 85 ? "outstanding" : ($metrics['behavior_conduct'] >= 75 ? "positive" : ($metrics['behavior_conduct'] >= 65 ? "satisfactory" : "developing"));
            $summaryText .= " behavioral conduct.";
            
            $this->MultiCell(180, 4, $summaryText, 0, 'C');

            // Student status section
            $this->Ln(8);
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(60, 60, 60);
            
            $this->Cell(40, 8, 'Attendance:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 8, '______', 'B', 0, 'C');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(60, 60, 60);
            $this->Cell(30, 8, 'Out of:', 0, 0, 'L');
            $this->SetFont('Times', '', 11);
            $this->SetTextColor(30, 30, 30);
            $this->Cell(25, 8, '______', 'B', 0, 'C');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(40, 100, 40);
            $this->Cell(45, 8, 'Promoted to:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(30, 80, 160);
            $this->Cell(25, 8, 'Basic 8', 0, 1, 'L');

            // Comments section with improved layout
            $this->Ln(5);
            $remarks = $this->getRemarks();
            $conductRemark = $conductRemarks[array_rand($conductRemarks)];
            
            // Academic remarks
            $this->SetFillColor(250, 250, 252);
            $this->SetDrawColor(220, 220, 220);
            $this->Rect(10, $this->GetY(), 190, 22, 'D');
            
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(35, 6, 'Academic Remarks:', 0, 1, 'L');
            $this->SetFont('Times', '', 10);
            $this->SetTextColor(50, 50, 50);
            $this->MultiCell(180, 5, $remarks, 0, 'L');
            
            // Conduct remarks
            $this->SetFont('Times', 'B', 11);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(35, 6, 'Conduct Remarks:', 0, 1, 'L');
            $this->SetFont('Times', '', 10);
            $this->SetTextColor(50, 50, 50);
            $this->MultiCell(180, 5, $conductRemark, 0, 'L');

            // Signatures section with professional layout
            $this->Ln(8);
            $signatureY = $this->GetY();
            
            // Class teacher signature
            $this->SetFont('Times', 'B', 10);
            $this->SetTextColor(80, 80, 80);
            $this->Cell(80, 6, 'Class Teacher\'s Signature:', 0, 0, 'L');
            $this->SetDrawColor(150, 150, 150);
            $this->Line(45, $signatureY + 8, 85, $signatureY + 8);
            
            // Headmistress signature
            $this->SetX(110);
            $this->Cell(80, 6, 'Headmistress\'s Signature:', 0, 1, 'L');
            $this->Line(125, $signatureY + 8, 165, $signatureY + 8);
            
            // Add signature image if available
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
                $this->Image($signatureImage, 130, $signatureY - 2, 25, 12);
            }
            
            // Progress indicator
            $this->Ln(5);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(100, 100, 100);
            $this->Cell(190, 4, "Report " . $this->current_student_index . " of " . $this->total_students, 0, 1, 'R');

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
    $pdf->Output('student_academic_report.pdf', 'I');
    
    // Flush the output buffer
    ob_end_flush();
} else {
    die("Invalid request method.");
}
?>
