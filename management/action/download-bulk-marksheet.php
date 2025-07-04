<?php
// Check if user is logged in
require_once("../../includes/config.php");
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
require_once('../../vendor/autoload.php');

// Check if user has permission to perform this action
if (!hasPermission(PERM_MANAGE_RESULTS)) {
    die("You do not have permission to perform this action.");
}

if (!isset($_GET['result_ids'])) {
    die("Result IDs are required");
}

$result_ids = explode(',', $_GET['result_ids']);

// School information
$school_name = $schoolInfo['name'];
$school_address = $schoolInfo['address'];
$school_contacts = 'Phone: ' . $schoolInfo['phone'] . ' | Email: ' . $schoolInfo['email'];

// Create PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('School Management System');
$pdf->SetAuthor('School Admin');
$pdf->SetTitle('Bulk Marksheets');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Function to generate a single marksheet page
function generateMarksheetPage($pdf, $pdo, $result_id)
{

    global $school_name;
    global $school_address;
    global $school_contacts;

    // Fetch result details with student and exam information
    $stmt = $pdo->prepare("
        SELECT r.*, 
               s.*, 
               e.exam_name, 
               e.exam_date,
               c.class_name,
               sec.section_name,
               (SELECT COUNT(*) FROM results WHERE exam_id = r.exam_id AND class_id = r.class_id) as total_students
        FROM results r
        JOIN students s ON r.student_id = s.student_id
        JOIN exams e ON r.exam_id = e.id
        JOIN classes c ON r.class_id = c.id
        JOIN sections sec ON s.section_id = sec.id
        WHERE r.id = ? AND (s.status = 'Active' OR s.status = 'Alumni')
    ");
    $stmt->execute([$result_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        return false; // Skip if result not found
    }

    // Fetch subject marks
    $stmt = $pdo->prepare("
        SELECT sm.*, sub.subject_name, sub.subject_type 
        FROM subject_marks sm
        JOIN subjects sub ON sm.subject_id = sub.id
        WHERE sm.result_id = ?
        ORDER BY sub.subject_type
    ");
    $stmt->execute([$result_id]);
    $subject_marks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add a new page for each student
    $pdf->AddPage();

    // Set background color
    $pdf->SetAlpha(0.1);
    $pdf->SetFillColor(0, 0, 255);
    $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'F');
    $pdf->SetAlpha(1);

    // Add watermark
    $watermarkPath = '../../uploads/school/logo-square.png';
    if (file_exists($watermarkPath)) {
        list($width, $height) = getimagesize($watermarkPath);
        $maxWidth = 100;
        $ratio = $width / $height;
        $watermarkWidth = $maxWidth;
        $watermarkHeight = $maxWidth / $ratio;
        $x = ($pdf->getPageWidth() - $watermarkWidth) / 2;
        $y = ($pdf->getPageHeight() - $watermarkHeight) / 2;
        $pdf->SetAlpha(0.1);
        $pdf->Image($watermarkPath, $x, $y, $watermarkWidth, $watermarkHeight, '', '', '', false, 300, '', false, false, 0, false, false, false);
        $pdf->SetAlpha(1);
    }

    // Set margins
    $pdf->SetMargins(15, 10, 15);
    $pdf->SetAutoPageBreak(TRUE, -30);

    // School information
    $pdf->SetFont('times', 'B', 20);
    $pdf->SetTextColor(0, 6, 77);
    $pdf->Ln(3);
    $pdf->Cell(0, 10, $school_name, 0, 1, 'C');
    $pdf->Ln(3);
    $pdf->SetTextColor(0, 50, 15);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, $school_address, 0, 1, 'C');
    $pdf->Cell(0, 5, $school_contacts, 0, 1, 'C');
    $pdf->Ln(3);

    // Marksheet Title
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(66, 0, 10);
    $pdf->Cell(0, 10, 'MARKSHEET - ' . strtoupper($result['exam_name']), 0, 1, 'C');
    $pdf->Ln(5);

    // School Logo
    $logoPath = $watermarkPath;
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 20, 30, 20, 20, '', '', '', false, 300, '', false, false, 0, false, false, false);
    }

    // Student Information
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 112);
    $tbl = <<<EOD
    <table cellspacing="0" cellpadding="4" width="75%">
        <tr>
            <td width="20%"><strong>Student ID:</strong></td>
            <td width="30%" style="color: #000000;">{$result['student_id']}</td>
            <td width="20%"><strong>Roll No:</strong></td>
            <td width="30%" style="color: #000000;">{$result['roll_no']}</td>
        </tr>
        <tr>
            <td><strong>Student Name:</strong></td>
            <td style="color: #000000;">{$result['name']}</td>
            <td><strong>Father's Name:</strong></td>
            <td style="color: #000000;">{$result['father_name']}</td>
        </tr>
        <tr>
            <td><strong>Class:</strong></td>
            <td style="color: #000000;">{$result['class_name']} - {$result['section_name']}</td>
            <td><strong>Exam:</strong></td>
            <td style="color: #000000;">{$result['exam_name']} ({$result['exam_date']})</td>
        </tr>
    </table>
    EOD;
    $pdf->writeHTML($tbl, true, false, false, false, '');
    $pdf->Ln(3);

    // Student Photo
    $studentImagePath = '../../uploads/students/' . $result['student_image'];
    if (!empty($result['student_image']) && file_exists($studentImagePath)) {
        $pdf->Image($studentImagePath, 170, 50, 20, 25, '', '', '', false, 300, '', false, false, 0, false, false, false);
    } else {
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY(160, 60);
        $pdf->Cell(30, 5, 'Photo Not Available', 0, 0, 'C');
    }

    // Subject Marks Table
    $table_height = 5 + (8 - count($subject_marks)) - 0.1; // 5 is perfect height for 8 subjects

    if (count($subject_marks) == 0) {
        $table_height = 5;
    }

    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $subject_tbl = '<table cellspacing="0" style="line-height: ' . $table_height . 'mm;" cellpadding="4" border="0.5">
        <tr style="background-color:#00064d; color:#FFFFFF;">
            <th width="25%" align="center"><strong>Subject</strong></th>
            <th width="15%" align="center"><strong>Type</strong></th>
            <th width="10%" align="center"><strong>Written</strong></th>
            <th width="10%" align="center"><strong>Oral</strong></th>
            <th width="10%" align="center"><strong>Total</strong></th>
            <th width="15%" align="center"><strong>Obtained</strong></th>
            <th width="15%" align="center"><strong>Grade</strong></th>
        </tr>';

    foreach ($subject_marks as $subject) {
        $subject_tbl .= '<tr>
            <td align="center;">' . safe_htmlspecialchars($subject['subject_name']) . '</td>
            <td align="center">' . safe_htmlspecialchars($subject['subject_type']) . '</td>
            <td align="center">' . (round($subject['theory_marks']) ?? '-') . '</td>
            <td align="center">' . (round($subject['practical_marks']) ?? '-') . '</td>
            <td align="center">' . round($subject['total_marks']) . '</td>
            <td align="center">' . round($subject['obtained_marks']) . '</td>
            <td align="center"><strong>' . $subject['grade'] . '</strong></td>
        </tr>';
    }

    $subject_tbl .= '</table>';
    $pdf->writeHTML($subject_tbl, true, false, false, false, '');
    $pdf->Ln(3);

    // Summary Information
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 112);
    $summary_tbl = '<table cellspacing="0" cellpadding="4">
        <tr>
            <td width="25%"><strong>Total Marks:</strong></td>
            <td width="25%" style="color:#000000;">' . round($result['total_marks']) . '</td>
            <td width="25%"><strong>Obtained Marks:</strong></td>
            <td width="25%" style="color:#000000;">' . round($result['obtained_marks']) . '</td>
        </tr>
        <tr>
            <td><strong>Percentage:</strong></td>
            <td style="color:#000000;">' . number_format($result['percentage'], 2) . '%</td>
            <td><strong>Overall Grade:</strong></td>
            <td style="color:#000000;"><strong>' . $result['grade'] . '</strong></td>
        </tr>
        <tr>
            <td><strong>Position in Class:</strong></td>
            <td style="color:#420303;"><b>' . getOrdinal(getStudentPositionInClass($pdo, $result['exam_id'], $result['class_id'], $result['student_id'])) . '</b></td>
            <td><strong>Result:</strong></td>
            ' . getPassFailStatus($result['grade']) . '
        </tr>
        <tr>
            <td><strong>Remarks:</strong></td>
            <td style="color:#000000;">' . $result['remarks'] . '</td>
            ' . ($result['is_promoted'] ? '<td><strong>Promotion Status:</strong></td><td style="color:#000000;">Promoted</td>' : '') . '
        </tr>
    </table>';
    $pdf->writeHTML($summary_tbl, true, false, false, false, '');
    $pdf->Ln(2);

    // Grading system table
    $stmt = $pdo->prepare("SELECT * FROM grading_system ORDER BY min_percentage DESC");
    $stmt->execute();
    $grading_system = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grading_tbl = '<table cellspacing="0" cellpadding="3" border="0.5" style="border-collapse:collapse; width:55%;">
        <tr style="background-color:#035863; color:#FFFFFF;">
            <th width="35%" align="center"><strong>Marks Range</strong></th>
            <th width="15%" align="center"><strong>Grade</strong></th>
            <th width="35%" align="center"><strong>Performance</strong></th>
        </tr>';

    foreach ($grading_system as $grade) {
        $range = floor($grade['min_percentage']) . ' - ' . floor($grade['max_percentage']);
        $grading_tbl .= '<tr>
            <td align="center">' . $range . '</td>
            <td align="center"><strong>' . $grade['grade'] . '</strong></td>
            <td align="center">' . $grade['remarks'] . '</td>
        </tr>';
    }

    $grading_tbl .= '</table>';
    $pdf->writeHTML($grading_tbl, true, false, false, false, '');
    $pdf->Ln(10);

    // QR code
    $style = array(
        'border' => 0,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false,
        'module_width' => 1,
        'module_height' => 1
    );

    $qrContent = "Student: {$result['name']}\n";
    $qrContent .= "ID: {$result['student_id']}\n";
    $qrContent .= "Class: {$result['class_name']}\n";
    $qrContent .= "Exam: {$result['exam_name']}\n";
    $qrContent .= "Percentage: " . number_format($result['percentage'], 2) . "%";

    $currentY = $pdf->GetY();
    $pdf->write2DBarcode($qrContent, 'QRCODE,L', 150, $currentY - 60, 30, 30, $style, 'N');

    // Principle Signature Image
    $photo_width = 20;
    $photo_x = 166;
    $photo_y = $currentY - 5;

    if (file_exists('../../uploads/school/principle_sign.png')) {
        $pdf->Image('../../uploads/school/principle_sign.png', $photo_x, $photo_y, $photo_width, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
    } else {
        $pdf->SetXY($photo_x, $photo_y);
        $pdf->Cell($photo_width, 20, 'Photo', array('width' => 0.5), 0, 'C');
    }

    // Signatures
    $pdf->Ln(10);
    // Total available width (190mm - page width minus left/right margins)
    $totalWidth = 190;
    $sectionWidth = $totalWidth / 4; // Each section gets equal width (47.5mm)
    $leftMargin = 10; // Left margin (matches the page border at 10mm)
    $rightMargin = 10; // Right margin (matches the page border at 190mm + 10mm = 200mm total)
    // Calculate starting X position to center the 4 sections within the available width
    $startX = $leftMargin;
    // Teacher's Sign (left-aligned with left margin)
    $pdf->SetX($startX);
    $pdf->Cell($sectionWidth, 5, 'Teacher\'s Sign', 0, 0, 'C');
    // Date
    $pdf->SetX($startX + $sectionWidth);
    $pdf->Cell($sectionWidth, 5, 'Date', 0, 0, 'C');
    // Guardian's Sign
    $pdf->SetX($startX + ($sectionWidth * 2));
    $pdf->Cell($sectionWidth, 5, 'Guardian\'s Sign', 0, 0, 'C');
    // Principal's Sign (right-aligned with right margin)
    $pdf->SetX($startX + ($sectionWidth * 3));
    $pdf->Cell($sectionWidth, 5, 'Principal\'s Sign', 0, 1, 'C');

    // Border around the page
    $pdf->Rect(10, 10, 190, 277, 'D');

    return true;
}

// Process each result ID
foreach ($result_ids as $result_id) {
    generateMarksheetPage($pdf, $pdo, $result_id);
}

// Output the PDF
$pdf->Output('Bulk_Marksheets.pdf', 'D');

// Close the database connection
$pdo = null;

// Function to get the ordinal suffix for a number
function getOrdinal($number)
{
    $suffix = ['th', 'st', 'nd', 'rd'];
    $mod100 = $number % 100;
    return $number . ($suffix[($mod100 - 20) % 10] ?? $suffix[$mod100] ?? $suffix[0]);
}

// Function to get pass or fail status by grade
function getPassFailStatus($grade)
{
    if ($grade == 'D' || $grade == 'F') {
        return '<td style="color:#FF0000;"><b>FAIL</b></td>';
    } else {
        return '<td style="color:#008000;"><b>PASS</b></td>';
    }
}
