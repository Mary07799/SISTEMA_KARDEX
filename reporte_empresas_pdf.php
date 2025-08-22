<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require('fpdf.php');

$mysqli = new mysqli("localhost", "root", "Usu@rio_admin123", "c_paccioli");
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$sql = "SELECT e.nombre, e.descripcion, u.nombre AS estudiante FROM empresas e JOIN usuarios u ON e.usuario_id = u.id ORDER BY e.fecha_creacion DESC";
$result = $mysqli->query($sql);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Empresas Ficticias', 0, 1, 'C');
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Nombre Empresa', 1);
$pdf->Cell(60, 10, 'Descripción', 1);
$pdf->Cell(60, 10, 'Estudiante', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 11);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 10, utf8_decode($row['nombre']), 1);
    $pdf->Cell(60, 10, utf8_decode(substr($row['descripcion'],0,40)), 1);
    $pdf->Cell(60, 10, utf8_decode($row['estudiante']), 1);
    $pdf->Ln();
}

$pdf->Output();
exit();
