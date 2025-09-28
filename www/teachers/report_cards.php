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
    $number = (int)$number; // Convert to integer
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
    function header() {
        // Add the watermark image
        $this->addWatermark();

        // Header text with no background color
        $this->SetFont('Arial', 'B', 26);
        $this->Cell(190, 8, '', 0, 0, 'C');
        $this->Ln();
        // Add the logo image at the centered position
        $logoWidth = 150; // Width of the logo
        $this->Image('bob.png', 40, 12, $logoWidth, 23); // Adjusted X position to 40 and Y position to 3

        // Add a small line break to move the address down
        $this->Ln(11); // Adjust this value to control the spacing

        // Add the address directly under the logo
        $this->SetFont('Times', 'B', 11);
        $this->Cell(190, 10, 'P.M.B 40, Madina', 0, 0, 'C'); // Address
        $this->Ln(); // Line break after the address

        $this->Cell(190, 10, 'TEL: 0277411866 / 0541622751', 0, 0, 'C');
        $this->Ln();

        $this->Cell(190, 10, 'LOCATION: Abokobi / Boi New Town', 0, 0, 'C');
        $this->Ln();

        // Draw the line under LOCATION
        $this->SetLineWidth(1); // Thicker line
        $this->Line(10, $this->GetY(), 200, $this->GetY()); // Line under location
        $this->Ln(); // Add a line break after the line
    }

    function addWatermark() {
        // Add the watermark image
        $this->Image('watermark_transparent_v3.png', 0, 0, 210, 297); // Full page size for A4
    }

    function footer() {
        // Footer content can be added here if needed
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function getRemarks() {
        // List of remarks
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

        // Randomly select a remark from the list
        return $remarks[array_rand($remarks)];
    }

    function headertable($conn) {
        $class = $_POST['askclass'];
        $exam = $_POST['exam'];

        // Define conduct remarks
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

        // Fetch all student data including photo and marks from the marks table
        $sql = "SELECT admission_number, photo, student, subject, class_score, exam_score, average, remarks, position 
                FROM marks 
                WHERE class = :class AND examname = :exam 
                ORDER BY admission_number ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':class', $class, PDO::PARAM_STR);
        $stmt->bindValue(':exam', $exam, PDO::PARAM_STR);
        $stmt->execute();

        // Initialize an array to hold student data
        $students = [];

        // Fetch all data into the students array
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $admno = $row["admission_number"];
            
            // Initialize student info if not already set
            if (!isset($students[$admno])) {
                $students[$admno]['name'] = $row["student"];
                $students[$admno]['photo'] = $row["photo"]; // Include the photo
                $students[$admno]['marks'] = [];
            }

            // Add subject marks
            $students[$admno]['marks'][] = [
                'subject' => $row["subject"],
                'class_score' => $row["class_score"],
                'exam_score' => $row["exam_score"],
                'average' => $row["average"],
                'remarks' => $row["remarks"],
                'position' => $row["position"]
            ];
        }

        // Generate reports for each student
        foreach ($students as $admno => $data) {
            $this->Ln(-10); // Move up by 10 units (adjust as needed)
            $this->SetFont('Arial', 'BU', 16);
            $this->Cell(190, 10, 'PUPIL\'S TERMINAL REPORT', 0, 0, 'C'); // Use standard apostrophe
            $this->Ln();
            
            // Display the photo
            if (!empty($data['photo']) && file_exists($data['photo'])) {
                $this->Image($data['photo'], 11, 15, 26, 20); // Display the photo
            } else {
                // If no photo is available, display a grey placeholder
                $this->SetFillColor(200, 200, 200); // Set fill color to grey
                $this->Rect(11, 15, 26, 20, 'F'); // Draw a filled rectangle as a placeholder
            }

            // Student details section
            $this->SetFont('Times', '', 12);
            $this->Cell(35, 10, 'Name:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 12); // Set to bold
            $this->Cell(10, 10, $data['name'], 0, 0, 'L');
            $this->SetFont('Times', '', 12); // Reset to normal
            $this->Ln();

            // Now display the class in bold
            $this->SetFont('Times', '', 12);
            $this->Cell(35, 10, 'Class :', 0, 0, 'L');
            $this->SetFont('Times', 'B', 13); // Set to bold
            $this->Cell(70, 10, $class, 0, 0, 'L');
            $this->SetFont('Times', '', 12); // Reset to normal
            $this->Cell(30, 10, 'Exam :', 0, 0, 'L');
            $this->SetFont('Times', 'B', 12); // Set to bold
            $this->Cell(76, 10, $exam, 0, 0, 'L');
            $this->SetFont('Times', '', 12); // Reset to normal
            $this->Ln();

            $this->SetFont('Times', '', 12);
            $this->Cell(50, 10, 'Term Ending:', 0, 0, 'L');
            $this->SetFont('Times', 'B', 12); // Set to bold for the date
            $this->Cell(50, 10, '7th August, 2025', 0, 0, 'L'); // Bold date
            $this->SetFont('Times', '', 12); // Reset to normal
            $this->Cell(50, 10, 'Next term begins: ', 0, 0, 'L'); // Normal text
            $this->SetFont('Times', 'B', 12); // Set to bold for the date
            $this->Cell(50, 10, '2nd September,2025', 0, 0, 'L'); // Bold date
            $this->SetFont('Times', '', 12); // Reset to normal
            $this->Ln(); // Add an extra line

            // Table headers for subject and marks
            $this->SetFont('Times', 'B', 12);
            $this->Cell(27, 8, 'SUBJECT', 1, 0, 'C'); // Reduced width
            $this->Cell(30, 8, 'CLASS (50%)', 1, 0, 'C'); // Reduced width
            $this->Cell(30, 8, 'EXAM (50%)', 1, 0, 'C'); // Reduced width
            $this->Cell(30, 8, 'TOTAL (100%)', 1, 0, 'C'); // Reduced width
            $this->Cell(25, 8, 'GRADE', 1, 0, 'C'); // Reduced width
            $this->Cell(30, 8, 'REMARKS', 1, 0, 'C'); // Reduced width
            $this->Cell(25, 8, 'POSITION', 1, 0, 'C'); // Restored Position column
            $this->Ln();

            // Populate the table with subject data
            foreach ($data['marks'] as $row) {
                $this->SetFont('Arial', '', 10);
                $subject = $row["subject"]; // No decryption needed
                $classScore = $row["class_score"]; // No decryption needed
                $examScore = $row["exam_score"]; // No decryption needed
                $average = $row["average"]; // No decryption needed
                $originalPosition = $row["position"]; // Fetch the original position without decryption

                $this->Cell(27, 7, $subject, 1, 0, 'C'); // Reduced height
                $this->Cell(30, 7, $classScore, 1, 0, 'C'); // Reduced height
                $this->Cell(30, 7, $examScore, 1, 0, 'C'); // Reduced height
                $this->Cell(30, 7, $average, 1, 0, 'C'); // Reduced height

                // Display the grade and remarks
                if ($average >= 80) {
                    $grade = 'A';
                    $remarks = 'Excellent';
                } elseif ($average >= 70) {
                    $grade = 'B';
                    $remarks = 'Very Good';
                } elseif ($average >= 60) {
                    $grade = 'C';
                    $remarks = 'Good';
                } elseif ($average >= 50) {
                    $grade = 'D';
                    $remarks = 'Average';
                } elseif ($average >= 40) {
                    $grade = 'E';
                    $remarks = 'Credit';
                } else {
                    $grade = 'F';
                    $remarks = 'Weak';
                }
                // Set font to bold for Grade
                $this->SetFont('Arial', 'B', 10);
                $this->Cell(25, 7, $grade, 1, 0, 'C'); // Grade
                $this->SetFont('Arial', '', 10); // Reset font to normal for remarks
                $this->Cell(30, 7, $remarks, 1, 0, 'C'); // Remarks

                // Set font to bold for Position
                $this->SetFont('Arial', 'B', 10);
                if (is_numeric($originalPosition) && $originalPosition > 0) {
                    $this->Cell(25, 7, ordinal($originalPosition), 1, 0, 'C'); // Display the original position with ordinal
                } else {
                    $this->Cell(25, 7, 'N/A', 1, 0, 'C'); // Handle invalid position
                }
                $this->Ln();
            }

            // Grading System Section - Moved directly under the position
            $this->SetFont('Arial', 'BU', 14);
            $this->Cell(0, 10, 'GRADING SYSTEM', 0, 1, 'C');
            $this->SetFont('Times', 'B', 11);
            $this->Cell(0, 10, 'A - Excellent (80 - 100)               B - Very Good (70 - 79)               C - Good (60 - 69)', 0, 1, 'C');
            $this->Cell(0, 10, '     D - Average (50 - 59)                           E - Credit (40 - 44)                         F - Weak(39 and below)', 0, 1, 'C');
            $this->SetLineWidth(0.5); // Thicker line
            $this->Line(10, $this->GetY(), 200, $this->GetY()); // Add a line under grading system
            
            // Attendance, Out of, and Promoted to Section - Directly under the Total Score and Position
            $this->Ln(3); // Adjust as needed for spacing
            $this->SetFont('Times', 'B', 12);
            $this->Cell(35, 10, 'Attendance:', 0, 0, 'L');
            $this->Cell(35, 10, '______', 0, 0, 'L');
            $this->Cell(35, 10, 'Out of:', 0, 0, 'L');
            $this->Cell(35, 10, '______', 0, 0, 'L');
            $this->Cell(35, 10, 'Promoted to: Basic 8', 0, 0, 'L');
            $this->Cell(35, 10, '', 0, 1, 'L'); // Empty cell for spacing
            $this->Ln(1); // Adjust as needed for spacing

            // Remarks Section
            $remarks = $this->getRemarks();  // Use the new random remark selection
            $this->SetFont('Times', 'B', 12);
            $this->Cell(35, 4, 'Remarks:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->MultiCell(0, 4, $remarks, 0, 'L');
            $this->Ln();

            // Conduct Remark Section
            $conductRemark = $conductRemarks[array_rand($conductRemarks)]; // Random conduct remark
            $this->SetFont('Times', 'B', 12);
            $this->Cell(35, 4, 'Conduct:', 0, 0, 'L');
            $this->SetFont('Times', '', 12);
            $this->MultiCell(0, 4, $conductRemark, 0, 'L');
            $this->Ln(2);

            // Add the signatures 
            $this->SetFont('Times', 'B', 12);
            $this->Cell(80, 8, 'Class teacher\'s signature:', 0, 0, 'L'); 
            $this->Cell(80, 8, 'Headmistress\'s signature:', 0, 1, 'L'); 

            // Set specific Y position for the signatures
            $signatureY = $this->GetY() - 10;

            // Add signature placeholders
            $this->SetFont('Arial', 'I', 8);
            
            // Class teacher signature placeholder
            $this->SetXY(50, $signatureY);
            $this->Cell(40, 5, '', 0, 0, 'C');
            
            // Headmistress signature placeholder
            $this->SetXY(130, $signatureY);
            $this->Cell(40, 5, '', 0, 0, 'C');

            // Determine which image to insert based on the class
            $imagePath = '';
            switch(strtolower($class)) {
                case 'basic 3b':
                    $imagePath = 'lion.png';
                    break;
                case 'basic 3a':
                    $imagePath = 'free.png';
                    break;
                case 'basic 6':
                    $imagePath = 'ern.png';
                    break;
                default:
                    $imagePath = 'new.jpg';
            }

                        // Insert the appropriate image for headmistress signature based on class
            $signatureImage = '';
            switch(strtolower(trim($class))) {
                case 'basic 3b':
                    $signatureImage = 'new.jpg';
                    break;
                case 'basic 3a':
                    $signatureImage = 'new.jpg';
                    break;
                case 'basic 6':
                    $signatureImage = 'ern.png';
                    break;
                default:
                    $signatureImage = 'new.jpg';
            }

           // Verify image exists before inserting
if (file_exists($signatureImage)) {
    $this->Image($signatureImage, 140, $signatureY - 5, 25, 15); // Position next to signature
} else {
    // Fallback to default image if specified image doesn't exist
    if (file_exists('new.jpg')) {
        // Moved further right (changed X from 130 to 160)
        $this->Image('new.jpg', 140, $signatureY - 2, 17, 15);
    } else {
        // If no image is available, just show the signature line
        $this->SetXY(130, $signatureY);
        $this->Cell(40, 5, '', 0, 0, 'C');
    }
}

            // Requirements table
            $this->Ln(10);
            $this->SetFont('Arial', 'B', 10);
            $this->Cell(95, 7, 'REQUIREMENT FOR NEXT TERM', 1, 0, 'C');
            $this->Cell(95, 7, 'MANAGEMENT', 1, 1, 'C');
            
            $this->SetFont('Times', '', 10);
            $this->MultiCell(95, 5, "SCHOOL FEES: GHC 250\n A4 sheet 1\nDETOL: 1 (CAMEL)\nTOILET ROLL 3, TOILET SOAP 2\nFEEDING FEE: GHC 7.00", 1, 'L');
            
            $currentY = $this->GetY();
            $this->SetXY(105, $currentY - 25);
            $this->MultiCell(95, 6, "WITH OUR SINCEREST THANKSGIVING TO PARENTS AND STAKEHOLDERS OF THE SCHOOL, WE LOOK FORWARD TO WORKING WITH YOU NEXT TERM. MAY GOD BLESS YOU.", 1, 'L');
            
            $this->Ln(5); // Add space after management text
            
            // Add a new page for next student report
            $this->AddPage();
        }
    }
}

// Check if form is submitted and generate PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new PDF instance
    $pdf = new mypdf();
    $pdf->AliasNbPages();
    $pdf->AddPage('P', 'A4', 0);
    $pdf->headertable($conn);
    
    // Output the PDF inline in the browser
    $pdf->Output('student_report.pdf', 'I'); // 'I' shows in browser
    
    // Flush the output buffer
    ob_end_flush();
} else {
    die("Invalid request method.");
}
?>
