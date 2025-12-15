<?php
session_start();
include 'includes/db.php';

/* TEMP login for testing (remove later) */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}

require_once __DIR__ . '/includes/tcpdf/tcpdf.php';

/* Validate POST */
if (
    isset(
        $_POST['topic'],
        $_POST['content'],
        $_POST['output_type'],
        $_POST['category_id'],
        $_POST['difficulty_id']
    )
) {

    $user_id       = $_SESSION['user_id'];
    $topic         = mysqli_real_escape_string($conn, $_POST['topic']);
    $content       = mysqli_real_escape_string($conn, $_POST['content']);
    $output_type   = mysqli_real_escape_string($conn, $_POST['output_type']);
    $category_id   = intval($_POST['category_id']);
    $difficulty_id = intval($_POST['difficulty_id']);

    /* 1️⃣ Save output in DB (without pdf first) */
    $insert = mysqli_query(
        $conn,
        "INSERT INTO outputs 
        (user_id, topic, category_id, difficulty_id, output_type, content) 
        VALUES 
        ('$user_id','$topic','$category_id','$difficulty_id','$output_type','$content')"
    );

    if (!$insert) {
        die("DB Insert Failed");
    }

    $output_id = mysqli_insert_id($conn);

    /* 2️⃣ Generate PDF */
    $pdf = new TCPDF();
    $pdf->SetCreator('AI Learning Platform');
    $pdf->SetAuthor('AI Generator');
    $pdf->SetTitle($output_type);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    $html = "
        <h2>{$output_type}</h2>
        <h4>Topic: {$topic}</h4>
        <hr>
        <p>" . nl2br(htmlspecialchars($_POST['content'])) . "</p>
    ";

    $pdf->writeHTML($html);

    /* 3️⃣ Save PDF file */
    $pdf_name = "user{$user_id}_output{$output_id}.pdf";
    $pdf_path = __DIR__ . "/storage/saved_outputs/" . $pdf_name;

    $pdf->Output($pdf_path, 'F');

    /* 4️⃣ Update DB with pdf filename */
    mysqli_query(
        $conn,
        "UPDATE outputs SET pdf_file='$pdf_name' WHERE id='$output_id'"
    );

    /* 5️⃣ Redirect to preview page */
    header("Location: download.php?id=$output_id");
    exit;
} else {
    echo "Invalid request.";
}
