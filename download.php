<?php
session_start();
include 'includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    die('Invalid request.');
}

$output_id = intval($_GET['id']);

// Fetch the output from database
$result = mysqli_query($conn, "SELECT * FROM outputs WHERE id='$output_id' AND user_id='{$_SESSION['user_id']}'");
if (mysqli_num_rows($result) === 0) {
    die('Output not found.');
}

$row = mysqli_fetch_assoc($result);
$content = $row['content'];
$topic = $row['topic'];
$output_type = $row['output_type'];

// Include TCPDF
require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('AI Learning Platform');
$pdf->SetAuthor('AI Generator');
$pdf->SetTitle($output_type . ' - ' . $topic);
$pdf->SetSubject($output_type);

// Set margins
$pdf->SetMargins(20, 25, 20);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 25);

// Add a page
$pdf->AddPage();

// Add a colored title box
$pdf->SetFillColor(52, 152, 219); // Blue
$pdf->SetTextColor(255, 255, 255); // White
$pdf->SetFont('helvetica', 'B', 18);
$pdf->MultiCell(0, 12, $output_type . " - " . $topic, 0, 'C', 1, 1, '', '', true);

// Add some spacing
$pdf->Ln(10);

// Reset text color for content
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('helvetica', '', 12);

// Add content inside a bordered box with light background
$pdf->SetFillColor(236, 240, 241); // Light gray background
$pdf->SetDrawColor(189, 195, 199); // Border color
$pdf->MultiCell(0, 0, $content, 1, 'L', 1, 1, '', '', true);

// Add a line break
$pdf->Ln(10);

// Add footer note with date
$pdf->SetFont('helvetica', 'I', 10);
$pdf->SetTextColor(127, 140, 141); // Gray
$pdf->Cell(0, 0, 'Generated on: ' . date('F j, Y, g:i A'), 0, 1, 'R');

// Output PDF to browser
$pdf_filename = 'output_' . $output_id . '.pdf';
$pdf->Output($pdf_filename, 'I'); // 'I' = inline view
exit;
