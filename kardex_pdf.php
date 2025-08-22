<?php
require("fpdf.php");
$conexion = new mysqli("localhost", "root", "Usu@rio_admin123", "inventario");
if ($conexion->connect_error) { die("Error: " . $conexion->connect_error); }

$producto_id = $_GET['producto_id'];
$producto = $conexion->query("SELECT nombre FROM productos WHERE id=$producto_id")->fetch_assoc();
$movimientos = $conexion->query("SELECT * FROM movimientos WHERE producto_id=$producto_id ORDER BY fecha ASC");

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0,10,"Kardex - ".$producto['nombre'],0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(40,8,"Tipo",1);
$pdf->Cell(40,8,"Cantidad",1);
$pdf->Cell(60,8,"Fecha",1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);
while ($row = $movimientos->fetch_assoc()) {
    $pdf->Cell(40,8,ucfirst($row['tipo']),1);
    $pdf->Cell(40,8,$row['cantidad'],1);
    $pdf->Cell(60,8,$row['fecha'],1);
    $pdf->Ln();
}

$pdf->Output();
