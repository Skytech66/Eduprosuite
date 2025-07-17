<?php
// Include the database connection and DOMPDF library
require_once "db_connection.php";
require_once "vendor/autoload.php"; // Path to your autoload file for DOMPDF

use Dompdf\Dompdf;
use Dompdf\Options;

// Get the lesson note ID from the URL
$lesson_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($lesson_id) {
    try {
        // Fetch the lesson note data from the database
        $query = "SELECT * FROM lesson_notes WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->execute([':id' => $lesson_id]);
        $lesson_note = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lesson_note) {
            // Set up DOMPDF options
            $options = new Options();
            $options->set("isHtml5ParserEnabled", true);
            $options->set("isPhpEnabled", true);
            $dompdf = new Dompdf($options);

            // Create HTML content for the PDF
            $html = '
                <html>
                <head>
                    <style>
                        body {
                            font-family: "Helvetica", sans-serif;
                            font-size: 18px;
                            margin: 0;
                            padding: 0;
                            color: #333;
                        }
                        .container {
                            padding: 20px;
                            line-height: 1.5;
                            position: relative;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .header h1 {
                            font-size: 32px;
                            margin: 0;
                            text-transform: uppercase;
                            text-decoration: underline;
                        }
                        .header p {
                            margin: 2px 0;
                            font-size: 18px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                            background-color: #fff;
                        }
                        table th, table td {
                            padding: 12px;
                            text-align: left;
                            border: 1px solid #BDC3C7;
                        }
                        table th {
                            font-weight: bold;
                            font-size: 18px;
                        }
                        .section {
                            margin-bottom: 20px;
                            padding: 15px;
                            border-radius: 8px;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                        }
                        .section h3 {
                            font-size: 18px;
                            margin-bottom: 10px;
                            text-decoration: underline;
                            font-weight: bold;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 30px;
                            font-size: 18px;
                        }
                        /* Watermark Styles */
                        .watermark {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            font-size: 80px;
                            font-weight: bold;
                            color: rgba(0, 0, 0, 0.1); /* Semi-transparent */
                            text-align: center;
                            z-index: -1; /* Send behind content */
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <!-- Watermark -->
                        <div class="watermark">C.F.E.C</div>
                        
                        <!-- Header -->
                        <div class="header">
                            <h1>LESSON PLAN</h1>
                            <p></p>
                        </div>
                        
                        <!-- Section: Basic Information & Strand/Sub-Strand inside it -->
                        <div class="section">
                            <h3>Basic Information</h3>
                            <table>
                                <tr>
                                    <th>Subject</th>
                                    <td>' . htmlspecialchars($lesson_note['subject']) . '</td>
                                </tr>
                                <tr>
                                    <th>Class</th>
                                    <td>' . htmlspecialchars($lesson_note['class']) . '</td>
                                </tr>
                                <tr>
                                    <th>Class Size</th>
                                    <td>' . htmlspecialchars($lesson_note['class_size']) . '</td>
                                </tr>
                                <tr>
                                    <th>Week Ending</th>
                                    <td>' . htmlspecialchars($lesson_note['week_ending']) . '</td>
                                </tr>
                                <tr>
                                    <th>Strand</th>
                                    <td>' . htmlspecialchars($lesson_note['strand']) . '</td>
                                </tr>
                                <tr>
                                    <th>Sub-Strand</th>
                                    <td>' . htmlspecialchars($lesson_note['sub_strand']) . '</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Section: Indicators & Standards -->
                        <div class="section">
                            <h3>Indicators & Standards</h3>
                            <table>
                                <tr>
                                    <th>Indicator (Code)</th>
                                    <td>' . htmlspecialchars($lesson_note['indicator']) . '</td>
                                </tr>
                                <tr>
                                    <th>Content Standard</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['content_standard'])) . '</td>
                                </tr>
                                <tr>
                                    <th>Performance Indicator</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['performance_indicator'])) . '</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Section: Core Competencies -->
                        <div class="section">
                            <h3>Core Competencies</h3>
                            <table>
                                <tr>
                                    <th>Competencies</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['core_competencies'])) . '</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Section: Teaching and Learning Resources -->
                        <div class="section">
                            <h3>Teaching and Learning Resources (TLMs)</h3>
                            <table>
                                <tr>
                                    <th>Resources</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['tlm'])) . '</td>
                                </tr>
                                <tr>
                                    <th>Reference</th>
                                    <td>' . htmlspecialchars($lesson_note['reference']) . '</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Section: Phases -->
                        <div class="section">
                            <h3>Phases of Lesson</h3>
                            <table>
                                <tr>
                                    <th>Starter</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['starter'])) . '</td>
                                </tr>
                                <tr>
                                    <th>Main</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['main'])) . '</td>
                                </tr>
                                <tr>
                                    <th>Plenary</th>
                                    <td>' . nl2br(htmlspecialchars($lesson_note['plenary'])) . '</td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- Footer -->
                        <div class="footer">
                            <p></p>
                            <p>Vetted by.........................signature................................date....................</p>
                        </div>
                    </div>
                </body>
                </html>
            ';

            // Load the HTML content
            $dompdf->loadHtml($html);

            // Set paper size to A4, portrait mode
            $dompdf->setPaper('A4', 'portrait');

            // Render PDF
            $dompdf->render();

            // Output the PDF (force download)
            $dompdf->stream("lesson_note_{$lesson_id}.pdf", ["Attachment" => 1]);
        } else {
            echo "Lesson note not found!";
        }
    } catch (PDOException $e) {
        echo "Error fetching lesson note: " . $e->getMessage();
    }
} else {
    echo "Invalid lesson note ID.";
}
?>
