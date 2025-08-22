<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "Usu@rio_admin123", "c_paccioli");
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=empresas.xls");

echo "Nombre Empresa\tDescripción\tEstudiante\n";

$sql = "SELECT e.nombre, e.descripcion, u.nombre AS estudiante FROM empresas e JOIN usuarios u ON e.usuario_id = u.id ORDER BY e.fecha_creacion DESC";
$result = $mysqli->query($sql);

while ($row = $result->fetch_assoc()) {
    echo $row['nombre'] . "\t" . $row['descripcion'] . "\t" . $row['estudiante'] . "\n";
}
exit();
