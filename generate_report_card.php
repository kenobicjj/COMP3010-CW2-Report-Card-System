<?php
require_once 'db.php';
require_once 'tcpdf/tcpdf.php'; // Use TCPDF library

if (!isset($_GET['student_id'])) {
    die('Student ID is required.');
}

$student_id = intval($_GET['student_id']);

// Fetch student details
$stmt = $conn->prepare('SELECT * FROM Students WHERE student_id = ?');
$stmt->bind_param('i', $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die('Student not found.');
}

// Fetch coursework and grades
$stmt = $conn->prepare(
    'SELECT rc.component_name, rc.weight, rc.max_marks, cg.marks_obtained, cg.feedback
     FROM ComponentGrades cg
     JOIN RubricComponents rc ON cg.component_id = rc.component_id
     JOIN Submissions s ON cg.submission_id = s.submission_id
     WHERE s.student_id = ?'
);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$grades = $stmt->get_result();

// Initialize TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Your Institution');
$pdf->SetTitle('Report Card');
$pdf->SetHeaderData('', 0, 'Report Card', "Generated on " . date('Y-m-d'));
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetMargins(15, 27, 15);
$pdf->SetAutoPageBreak(TRUE, 25);
$pdf->AddPage();

// Add student details
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, 'Name: ' . $student['name'], '', 0, 'L', true, 0, false, false, 0);
$pdf->Write(0, 'Email: ' . $student['email'], '', 0, 'L', true, 0, false, false, 0);
$pdf->Ln(5);

// Add table header
$pdf->SetFont('helvetica', 'B', 12);
$html = '<table border="1" cellpadding="4">
            <thead>
                <tr>
                    <th>Component</th>
                    <th>Weight (%)</th>
                    <th>Max Marks</th>
                    <th>Marks Obtained</th>
                    <th>Feedback</th>
                </tr>
            </thead>
            <tbody>';

// Add grades
while ($row = $grades->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['component_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['weight']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['max_marks']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['marks_obtained']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['feedback']) . '</td>';
    $html .= '</tr>';
}

// Calculate weighted total
$total_weighted_marks = 0;
$total_weight = 0;
$grades->data_seek(0); // Reset result set pointer
while ($row = $grades->fetch_assoc()) {
    $weighted_marks = ($row['marks_obtained'] / $row['max_marks']) * $row['weight'];
    $total_weighted_marks += $weighted_marks;
    $total_weight += $row['weight'];
}

$html .= '<tfoot><tr>';
$html .= '<td colspan="3"><strong>Total Weighted Marks:</strong></td>';
$html .= '<td colspan="2">' . number_format($total_weighted_marks, 2) . ' / ' . number_format($total_weight, 2) . '</td>';
$html .= '</tr></tfoot>';

// Output the table
$pdf->writeHTML($html, true, false, true, false, '');

// Output the PDF
$pdf->Output('report_card_' . $student['student_id'] . '.pdf', 'I');
exit;
?>