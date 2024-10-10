<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';
require('fpdf/fpdf.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}


// Create new PDF instance
class PDF extends FPDF {
    // Page header
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Task List', 0, 1, 'C');
    }

    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Initialize the PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Add table headers
$pdf->Cell(40, 10, 'Task Name', 1);
$pdf->Cell(40, 10, 'Deadline', 1);
$pdf->Cell(30, 10, 'Priority', 1);
$pdf->Cell(40, 10, 'Category', 1);
$pdf->Ln(); // Line break

// Fetch tasks from the database for the logged-in user
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM tasks WHERE user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(40, 10, $row['task_name'], 1);
        $pdf->Cell(40, 10, $row['deadline'], 1);
        $pdf->Cell(30, 10, $row['priority'], 1);
        $pdf->Cell(40, 10, $row['category'], 1);
        $pdf->Ln(); // Line break after each task
    }
} else {
    // If no tasks, print a message
    $pdf->Cell(0, 10, 'No tasks found.', 1, 1, 'C');
}

// Output the PDF
$pdf->Output();

$conn->close();