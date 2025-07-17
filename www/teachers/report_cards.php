<?php
ob_start();
require "config.php"; // Include the database configuration

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getRandomConductRemark($conductRemarks) {
    if (empty($conductRemarks)) {
        return 'No conduct remarks available.';
    }
    return $conductRemarks[array_rand($conductRemarks)];
}

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
    // School colors
    private $primaryColor = [0, 70, 140]; // Dark blue
    private $secondaryColor = [220, 230, 241]; // Light blue
    private $accentColor = [255, 195, 0]; // Gold

    function header() {
        // Modern header with school branding
        $this->SetY(10);
        $this->SetFont('Helvetica', 'B', 24);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Cell(0, 10, 'ADINKRA INTERNATIONAL SCHOOL', 0, 1, 'C');

        // Add the school crest image
        $this->Image('adinkra.png', 30, 20, 20, 20); // Adjust the position and 
        // Subheader with motto
        $this->SetFont('Helvetica', 'I', 12);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, 'Excellence Through Knowledge', 0, 1, 'C');

        // Contact information
        $this->SetFont('Helvetica', '', 10);
        $this->SetTextColor(70, 70, 70);
        $this->Cell(0, 6, 'P.M.B 40, legon | TEL: +233 7741 1866 / +233 5416 2275', 0, 1, 'C');
        $this->Cell(0, 6, 'LOCATION: East-legon / Accra', 0, 1, 'C');

        // Decorative line
        $this->SetDrawColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
        $this->SetLineWidth(0.8);
        $this->Line(10, $this->GetY() + 5, 200, $this->GetY() + 5);
        $this->Ln(6);
    }

    function footer() {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function getRemarks() {
        $remarks = [
            "Making steady progress keep it up.",
            "A consistent effort will lead to improvement.",
            "Shows potential, needs to stay focused.",
            "Good performance, keep working hard.",
            "Excellent work, maintain the standard.",
            "Satisfactory performance, can do better.",
            "Needs to put in more effort to improve.",
            "Showing improvement, keep it up.",
            "Working hard, results are showing.",
            "Needs to be more attentive in class.",
            "Performance is improving steadily.",
            "Should participate more in class activities.",
            "Demonstrates good understanding of concepts.",
            "Should work on time management.",
            "Encouraged to keep working hard and not settle."
        ];
        return $remarks[array_rand($remarks)];
    }

    // Ellipse drawing method supporting arcs for partial ellipses
    function Ellipse($x, $y, $rx, $ry, $startAngle = 0, $endAngle = 360, $style = '') {
        $k = $this->k;
        $hp = $this->h;

        // Convert angles from degrees to radians
        $startAngle = deg2rad($startAngle);
        $endAngle = deg2rad($endAngle);

        $segments = 40; // Increase segments for smoother ellipse
        $angleStep = ($endAngle - $startAngle) / $segments;

        // Calculate points around the ellipse arc
        $points = [];
        for ($i = 0; $i <= $segments; $i++) {
            $angle = $startAngle + $i * $angleStep;
            $points[] = [
                $x + $rx * cos($angle),
                $y + $ry * sin($angle)
            ];
        }

        // Begin path
        $this->_out(sprintf('%.2F %.2F m', $points[0][0] * $k, ($hp - $points[0][1]) * $k));

        // Draw lines between points
        for ($i = 1; $i <= $segments; $i++) {
            $this->_out(sprintf('%.2F %.2F l', $points[$i][0] * $k, ($hp - $points[$i][1]) * $k));
        }

        // Draw path: stroke or fill (default) or both
        if ($style == 'F') {
            $this->_out('f'); // Fill
        } else if ($style == 'FD' || $style == 'DF') {
            $this->_out('B'); // Fill and stroke
        } else {
            $this->_out('S'); // Stroke only
        }
    }

    function drawCircularProgress($x, $y, $radius, $value, $label) {
        // Draw background circle
        $this->SetDrawColor(220, 220, 220);
        $this->SetLineWidth(2);
        $this->Ellipse($x, $y, $radius, $radius, 0, 360);

        // Draw progress arc
        $this->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->SetLineWidth(3);
        $endAngle = 360 * ($value / 100);
        $this->Ellipse($x, $y, $radius, $radius, 0, $endAngle);

        // Draw percentage text
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        $this->SetXY($x - 5, $y - 3);
        $this->Cell(10, 6, $value . '%', 0, 0, 'C');

        // Draw label
        $this->SetFont('Helvetica', '', 9);
        $this->SetXY($x - $radius, $y + $radius + 2);
        $this->Cell($radius * 2, 5, $label, 0, 0, 'C');
    }

    function headertable() {
        global $conn; // Use the global MySQL connection
        $class = $_POST['askclass'];
        $exam = $_POST['exam'];

        $conductRemarks = [
            "Consistently demonstrates outstanding behavior and a positive attitude.",
            "Shows respect for teachers and peers at all times.",
            "A role model for other students in terms of conduct.",
            "Always follows classroom rules and procedures.",
            "Demonstrates excellent self-discipline and responsibility.",
            "Polite and courteous in all interactions.",
            "Works well with others in group activities.",
            "Needs occasional reminders about classroom behavior.",
            "Generally well-behaved but can be distracted sometimes.",
            "Should work on following instructions more carefully.",
            "Has shown improvement in behavior this term.",
            "Sometimes needs guidance to make good choices.",
            "Responds well to positive reinforcement.",
            "Should work on staying focused during lessons.",
            "Polite, respectful, and a joy to have in class."
        ];

        // Updated SQL query to select from 'students' and use 'admission_number'
        $sql = "SELECT name, admission_number, photo FROM students WHERE class LIKE ? ORDER BY admission_number ASC";
        $stmt = $conn->prepare($sql);
        $likeClass = "%$class%";
        $stmt->bind_param('s', $likeClass);
        $stmt->execute();
        $ret = $stmt->get_result();

        if (!$ret) {
            die("Query failed: " . $conn->error);
        }

        $subjectOrder = [
            'English', 'Science', 'Owop', 'R.M.E', 'History', 
            'Computing', 'Creative', 'Twi', 'French'
        ];

        $totalScores = [];
        while ($row1 = $ret->fetch_assoc()) {
            $admission_number = $row1["admission_number"];
            $studentName = $row1["name"];
            $photoPath = $row1["photo"];

            $sqlm = "SELECT average FROM marks WHERE admission_number = ? AND examname = ?";
            $stmtm = $conn->prepare($sqlm);
            $stmtm->bind_param('ss', $admission_number, $exam);
            $stmtm->execute();
            $retm = $stmtm->get_result();

            $totalScore = 0;
            $subjectCount = 0;

            while ($row = $retm->fetch_assoc()) {
                $average = $row["average"];
                $totalScore += $average;
                $subjectCount++;
            }

            $totalScores[$admission_number] = [
                'total' => $totalScore,
                'student' => $studentName,
                'subjectCount' => $subjectCount,
                'photo' => $photoPath
            ];
            $stmtm->close();
        }

        arsort($totalScores);
        $ret->data_seek(0); // Reset the result set

        foreach ($totalScores as $admission_number => $data) {
            // Student Report Header
            $this->SetFont('Helvetica', 'B', 16);
            $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
            $this->Cell(0, 10, 'STUDENT PROGRESS REPORT', 0, 1, 'C');
            $this->SetFont('Helvetica', '', 12);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 6, 'Terminal Assessment - ' . $exam, 0, 1, 'C');
            $this->Ln(1);

            // Student Photo and Info Section
            $this->SetFillColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
            $this->RoundedRect(10, $this->GetY(), 190, 30, 5, 'F');

            if (!empty($data['photo']) && file_exists($data['photo'])) {
                $this->Image($data['photo'], 15, $this->GetY() + 5, 20, 20);
            } else {
                $this->SetFillColor(200, 200, 200);
                $this->Rect(15, $this->GetY() + 5, 20, 20, 'F');
            }

            $this->SetXY(40, $this->GetY() + 5);
            $this->SetFont('Helvetica', 'B', 14);
            $this->Cell(50, 7, 'Name:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 14);
            $this->Cell(100, 7, $data['student'], 0, 1, 'L');

            $this->SetX(40);
            $this->SetFont('Helvetica', 'B', 12);
            $this->Cell(50, 7, 'Class:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->Cell(50, 7, $class, 0, 0, 'L');

            $this->SetFont('Helvetica', 'B', 12);
            $this->Cell(30, 7, 'Term Ends:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->Cell(50, 7, '17th April, 2025', 0, 1, 'L');

            $this->SetX(40);
            $this->SetFont('Helvetica', 'B', 12);
            $this->Cell(50, 7, 'Admission No:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->Cell(50, 7, $admission_number, 0, 0, 'L');

            $this->SetFont('Helvetica', 'B', 12);
            $this->Cell(30, 7, 'Resumes:', 0, 0, 'L');
            $this->SetFont('Helvetica', '', 12);
            $this->Cell(50, 7, '6th May, 2025', 0, 1, 'L');

            $this->Ln(7);

            // Academic Performance Table
            $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Helvetica', 'B', 11);

            $header = ['SUBJECT', 'CLASS(50%)', 'EXAM(50%)', 'TOTAL(100%)', 'GRADE', 'REMARKS', 'POSITION'];
            $w = [30, 25, 25, 25, 20, 40, 25];

            for ($i=0; $i<count($header); $i++)
                $this->Cell($w[$i], 8, $header[$i], 1, 0, 'C', true);
            $this->Ln();

            $this->SetTextColor(0, 0, 0);
            $this->SetFont('Helvetica', '', 10);

            $sqlm = "SELECT subject, midterm, endterm, average, remarks, position FROM marks WHERE admission_number = ? AND examname = ?";
            $stmtm2 = $conn->prepare($sqlm);
            $stmtm2->bind_param('ss', $admission_number, $exam);
            $stmtm2->execute();
            $retm2 = $stmtm2->get_result();

            $subjects = [];
            while ($row = $retm2->fetch_assoc()) {
                $subjects[] = $row;
            }
            $stmtm2->close();

            usort($subjects, function($a, $b) use ($subjectOrder) {
                $posA = array_search($a['subject'], $subjectOrder);
                $posB = array_search($b['subject'], $subjectOrder);
                return $posA - $posB;
            });

            foreach ($subjects as $row) {
                $subject = $row["subject"];
                $midterm = $row["midterm"];
                $endterm = $row["endterm"];
                $average = $row["average"];
                $originalPosition = $row["position"];

                $this->Cell($w[0], 7, $subject, 'LRB', 0, 'C');
                $this->Cell($w[1], 7, $midterm, 'RB', 0, 'C');
                $this->Cell($w[2], 7, $endterm, 'RB', 0, 'C');
                $this->Cell($w[3], 7, $average, 'RB', 0, 'C');

                if ($average >= 80) {
                    $grade = 'A'; $remarks = 'Superb';
                } elseif ($average >= 70) {
                    $grade = 'B'; $remarks = 'Outstanding';
                } elseif ($average >= 60) {
                    $grade = 'C'; $remarks = 'Commendable';
                } elseif ($average >= 50) {
                    $grade = 'D'; $remarks = 'Capable';
                } elseif ($average >= 40) {
                    $grade = 'E'; $remarks = 'Developing';
                } else {
                    $grade = 'F'; $remarks = 'Emerging';
                }

                $this->SetFont('Helvetica', 'B', 10);
                $this->Cell($w[4], 7, $grade, 'RB', 0, 'C');
                $this->SetFont('Helvetica', '', 10);
                $this->Cell($w[5], 7, $remarks, 'RB', 0, 'C');
                $this->SetFont('Helvetica', 'B', 10);

                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell($w[6], 7, ordinal($originalPosition), 'RB', 0, 'C');
                } else {
                    $this->Cell($w[6], 7, 'N/A', 'RB', 0, 'C');
                }
                $this->Ln();
            }

             // Grading Key
            $this->Ln(5);
            $this->SetFillColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
            $this->RoundedRect(10, $this->GetY(), 190, 15, 3, 'F');
            
            $this->SetFont('Helvetica', 'B', 11);
            $this->SetXY(10, $this->GetY() + 3);
            $this->Cell(190, 2, 'GRADING KEY', 0, 1, 'C');
            
            $this->SetFont('Helvetica', '', 10);
            $this->SetXY(10, $this->GetY() + 2);
            $this->Cell(38, 5, 'A (80-100) = Superb', 0, 0, 'C');
            $this->Cell(38, 5, 'B (70-79) = Commendable', 0, 0, 'C');
            $this->Cell(38, 5, 'C (60-69) = Capable', 0, 0, 'C');
            $this->Cell(38, 5, 'D (50-59) = Developing', 0, 0, 'C');
            $this->Cell(38, 5, 'E (40-49) = Emerging', 0, 1, 'C');
            $this->Ln(3);

            // Skills Assessment Section
            $this->SetFont('Helvetica', 'B', 11);
            $this->Cell(0, 7, 'SKILLS ASSESSMENT', 0, 1, 'L');
            $this->Ln(0);
            
            // Updated skills array and order as per request:
            $skills = [
                'Time Management' => rand(60, 100),
                'Critical Thinking' => rand(60, 100),
                'Creativity' => rand(60, 100),
                'Collaboration' => rand(60, 100),
                'Behaviour' => rand(60, 100),

                
            ];
            
           // Draw circular progress bars
$radius = 12;
$startX = 30;
$yPos = $this->GetY();
$spacing = 38;
$skillsPerRow = 5;
$skillCount = 0;

foreach ($skills as $skill => $value) {
    $this->drawCircularProgress($startX, $yPos + $radius + 10, $radius, $value, $skill);
    $startX += $spacing;
    $skillCount++;

    if ($skillCount % $skillsPerRow == 0) {
        $startX = 30;
        $yPos += $radius * 2 + 1;
        $this->SetY($yPos + $radius + 14);
    }
}

// Set Y position to be just below the progress circles
$this->SetY($yPos + $radius * 1 + 2);

// Attendance and Promotion Section

$this->SetFont('Helvetica', 'B', 11);
$this->SetXY(15, $this->GetY() + 1);
$this->Cell(40, 5, 'Attendance:', 0, 0, 'L');
$this->SetFont('Helvetica', '', 11);
$this->Cell(20, 5, '______', 0, 0, 'L');

$this->SetFont('Helvetica', 'B', 11);
$this->Cell(40, 5, 'Out of:', 0, 0, 'L');
$this->SetFont('Helvetica', '', 11);
$this->Cell(20, 5, '______', 0, 0, 'L');

$this->SetFont('Helvetica', 'B', 11);
$this->Cell(40, 5, 'Promoted to:', 0, 0, 'L');
$this->SetFont('Helvetica', '', 11);
$this->Cell(20, 5, '______', 0, 1, 'L');
$this->Ln(1);

// Comments Section
$this->SetFont('Helvetica', 'B', 12);
$this->Cell(40, 7, 'Teacher Remarks:', 0, 0, 'L');
$this->SetFont('Helvetica', '', 11);
$this->MultiCell(0, 7, $this->getRemarks(), 0, 'L');

$this->SetFont('Helvetica', 'B', 12);
$this->Cell(40, 7, 'Conduct:', 0, 0, 'L');
$this->SetFont('Helvetica', '', 11);
$this->MultiCell(0, 7, getRandomConductRemark($conductRemarks), 0, 'L');

// Signature Line
$this->SetFont('Helvetica', 'B', 11);
$this->Cell(0, 5, 'Class Teacher: ____________________                Headteacher signature ___________________', 0, 1, 'L');

            // Add new page for next student
            $this->AddPage();
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
        $xc = $x+$r ;
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
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}

$pdf = new mypdf();
$pdf->AliasNbPages();
$pdf->AddPage('P', 'A4', 0);
$pdf->headertable();
$pdf->Output();
ob_end_flush();
?>
