<?php
require_once("../../includes/db.php");
require_once('../../vendor/autoload.php');

// Check if required parameters are provided
if (
    !isset($_GET['student_id']) || empty($_GET['student_id']) ||
    !isset($_GET['admit_id']) || empty($_GET['admit_id'])
) {
    die("Student ID and Admit Card ID are required");
}

$student_id = $_GET['student_id'];
$admit_id = $_GET['admit_id'];

//Fetch school information
$school_name = 'ABCD KNOWLEDGE HIGH SCHOOL';
$school_address = '123 Education Street, Knowledge City';

$stmt = $pdo->prepare("SELECT * FROM school_information");
$stmt->execute();
$school_info = $stmt->fetch(PDO::FETCH_ASSOC);

if ($school_info) {
    $school_name = $school_info['name'];
    $school_address = $school_info['address'];
}

// Fetch student details with class information
$stmt = $pdo->prepare("
    SELECT s.*, c.id as class_id, c.class_name, sec.section_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    WHERE s.student_id = ?
");
$stmt->execute([$student_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found");
}

// Fetch specific admit card details and verify it's for the student's class
$stmt = $pdo->prepare("
    SELECT ea.*, e.exam_name, e.exam_date
    FROM exam_admit_releases ea
    JOIN exams e ON ea.exam_id = e.id
    WHERE ea.id = ? AND ea.class_id = ?
");
$stmt->execute([$admit_id, $student['class_id']]);
$admit_card = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admit_card) {
    die("Admit card not found or not valid for this student's class");
}

// Check permissions
$stmt = $pdo->prepare("
    SELECT 
    sp.override_admit_check, 
    sp.allow_admit_card, 
    SUM(f.unpaid_amount) AS unpaid_amount
    FROM student_unpaid_fees f
    LEFT JOIN student_permissions sp ON f.student_id = sp.student_id
    WHERE f.student_id = ?
    GROUP BY sp.override_admit_check, sp.allow_admit_card;
");
$stmt->execute([$student_id]);
$permissions = $stmt->fetch(PDO::FETCH_ASSOC);

// Default behavior - check fees if not overridden
if (!$permissions || !$permissions['override_admit_check']) {
    if (($permissions['unpaid_amount'] ?? 0) > 0) {
        die("Admit card download is not permitted - pending fees must be cleared first");
    }
}
// If overridden, check explicit permission
else {
    if (!$permissions['allow_admit_card']) {
        die("Admit card download has been disabled for this student");
    }
}

// Fetch exam routine for this specific admit card
$stmt = $pdo->prepare("
    SELECT er.*, sub.subject_name 
    FROM exam_routines er
    JOIN subjects sub ON er.subject_id = sub.id
    WHERE er.exam_id = ? AND er.class_id = ?
    ORDER BY er.exam_date, er.start_time
");
$stmt->execute([$admit_card['exam_id'], $student['class_id']]);
$exam_routine = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('School Management System');
$pdf->SetAuthor('School Admin');
$pdf->SetTitle('Admit Card - ' . $student['name'] . ' - ' . $admit_card['exam_name']);
$pdf->SetSubject('Student Admit Card');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Define dimensions for half-page admit card (A4 is 210x297mm)
$card_width = 190; // width of content area
$card_height = 127; // height for each admit card (allows two per page with spacing)
$margin_top = 10; // top margin for first card
$gap_between_cards = 18; // space between two cards

// First Admit Card (Top Half)
createAdmitCard($pdf, $student, $admit_card, $exam_routine, $margin_top, $card_width, $card_height, $school_name, $school_address);

// Second Admit Card (Bottom Half)
// createAdmitCard($pdf, $student, $admit_card, $exam_routine, $margin_top + $card_height + $gap_between_cards, $card_width, $card_height);

// Output the PDF
$pdf->Output('AdmitCard_' . $student['student_id'] . '_' . $admit_card['exam_name'] . '.pdf', 'D');

/**
 * Function to create a single admit card
 */
function createAdmitCard($pdf, $student, $admit_card, $exam_routine, $start_y, $width, $height, $school_name, $school_address)
{

    // Add watermark pattern within the admit card boundaries
    $pdf->SetAlpha(0.1); // Very subtle transparency
    $pdf->SetFont('helvetica', '', 5); // Medium size
    $pdf->SetTextColor(0, 90, 150); // Light gray

    $watermarkText = $school_name;
    $textWidth = $pdf->GetStringWidth($watermarkText);
    $spacingX = $textWidth + 1; // Horizontal spacing between watermarks
    $spacingY = 2; // Vertical spacing between watermarks

    // Calculate starting positions to center the pattern
    $startX = 11;
    $startY = $start_y + 1;

    // Create grid pattern within card boundaries
    for ($y = $startY; $y < ($start_y + $height - 10); $y += $spacingY) {
        for ($x = $startX; $x < ($startX + $width - 10); $x += $spacingX) {
            $pdf->Text($x, $y, $watermarkText);
        }
    }

    $pdf->SetAlpha(1); // Reset transparency

    // -- Watermark pattern ends here --

    // School information - compact
    $pdf->SetY($start_y + 3);
    $pdf->SetFont('times', 'B', 25);
    $pdf->SetTextColor(0, 3, 70);
    $pdf->Cell(0, 6, $school_name, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 46, 19);
    $pdf->Cell(0, 4, $school_address, 0, 1, 'C');
    $pdf->Ln(2);

    // Admit Card Title - compact
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(60, 0, 0);
    $pdf->Cell(0, 6, 'ADMIT CARD', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 4, strtoupper($admit_card['exam_name']) . ' - ' . date('Y', strtotime($admit_card['release_date'])), 0, 1, 'C');
    $pdf->Ln(2);

    // Student photo - smaller
    $photo_width = 20;
    $photo_x = 160;
    $photo_y = $start_y + 26;

    if (!empty($student['student_image']) && file_exists('../../uploads/students/' . $student['student_image'])) {
        $pdf->Image('../../uploads/students/' . $student['student_image'], $photo_x, $photo_y, $photo_width, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
    } else {
        $pdf->SetXY($photo_x, $photo_y);
        $pdf->Cell($photo_width, 20, 'Photo', array('width' => 0.5), 0, 'C');
    }

    // Student information table - compact

    $student_name = strtoupper($student['name']);
    $father_name = strtoupper($student['father_name']);

    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 7, 145);
    $pdf->SetXY(15, $start_y + 32);

    $tbl = <<<EOD
    <table cellspacing="0" style="width: 70%;" cellpadding="4" border="0">
        <tr>
            <td width="20%"><strong>Name:</strong></td>
            <td width="30%">{$student_name}</td>
            <td width="20%"><strong>Father's Name:</strong></td>
            <td width="30%">{$father_name}</td>
        </tr>
        <tr>
            <td width="20%"><strong>Class:</strong></td>
            <td width="30%">{$student['class_name']} ({$student['section_name']})</td>
            <td width="20%"><strong>Roll No:</strong></td>
            <td width="30%">{$student['roll_no']}</td>
        </tr>
        <tr>
            <td width="20%"><strong>Address:</strong></td>
            <td width="80%">{$student['address']}</td>
        </tr>
    </table>
    EOD;

    $pdf->writeHTML($tbl, true, false, false, false, '');

    // Exam Routine Section - compact
    $pdf->SetXY(15, $start_y + 55);

    if (!empty($exam_routine)) {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        $table_height = 4 + (8 - count($exam_routine)); // 4 is perfect height for 8 subjects

        if (count($exam_routine) == 0) {
            $table_height = 4;
        }

        $routine_tbl = '<table style="text-align: center; width: 180mm; line-height: '.$table_height.'mm;" cellspacing="0" cellpadding="3" border="0.1">
            <tr style="background-color:#000346; color:#ffffff;">
                <th width="20%">DATE</th>
                <th width="20%">DAY</th>
                <th width="20%">SUBJECTS</th>
                <th width="20%">TIME</th>
                <th width="20%">SUPERVISOR\'s SIGN</th>
            </tr>';

        foreach ($exam_routine as $routine) {
            $start_time = date("h:i A", strtotime($routine['start_time']));
            $end_time = date("h:i A", strtotime($routine['end_time']));

            $routine_tbl .= '<tr style="font-size: 8pt;">
                <td border="0">' . date('d M Y', strtotime($routine['exam_date'])) . '</td>
                <td>' . date('l', strtotime($routine['exam_date'])) . '</td>
                <td>' . $routine['subject_name'] . '</td>
                <td>' . $start_time . '-' . $end_time . '</td>
                <td></td>
            </tr>';
        }

        $routine_tbl .= '</table>';
        $pdf->writeHTML($routine_tbl, true, false, false, false, '');
    }

    // Principle Signature Image
    $photo_width = 20;
    $photo_x = 143;
    $photo_y = $start_y + $height - 17;

    if (file_exists('../../uploads/school/principle_sign.png')) {
        $pdf->Image('../../uploads/school/principle_sign.png', $photo_x, $photo_y, $photo_width, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
    } else {
        $pdf->SetXY($photo_x, $photo_y);
        $pdf->Cell($photo_width, 20, 'Photo', array('width' => 0.5), 0, 'C');
    }

    // Signatures - compact
    $pdf->SetXY(10, $start_y + $height - 8);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->Cell(95, 4, 'Exam Controller', 0, 0, 'C');
    $pdf->Cell(95, 4, 'Principal', 0, 1, 'C');

    // Border around the admit card - with color
    $border_width = 0.5; // Border line width
    $gap = 1; // Gap between the two border lines
    $border_color = array(0, 3, 100); // RGB color for border (blue in this example)

    // Set border color and width
    $pdf->SetDrawColor($border_color[0], $border_color[1], $border_color[2]);
    $pdf->SetLineWidth($border_width);

    // Outer border
    $pdf->Rect(10, $start_y, $width, $height, 'D');

    // Inner border (offset by the gap amount)
    $pdf->Rect(
        10 + $gap,
        $start_y + $gap,
        $width - ($gap * 2),
        $height - ($gap * 2),
        'D'
    );

    // Reset draw color to black for other elements
    $pdf->SetDrawColor(0, 0, 0);
}
