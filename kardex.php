<?php
$conexion = new mysqli("localhost", "root", "Usu@rio_admin123", "inventario_paccioli");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consultar todos los movimientos con datos del producto
$sql = "
    SELECT 
        p.nombre AS producto,
        m.tipo,
        m.cantidad,
        m.fecha,
        (SELECT 
            SUM(CASE WHEN tipo='entrada' THEN cantidad ELSE -cantidad END)
         FROM movimientos
         WHERE producto_id = p.id AND fecha <= m.fecha) AS stock_actual
    FROM movimientos m
    INNER JOIN productos p ON m.producto_id = p.id
    ORDER BY p.nombre, m.fecha ASC
";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex de Inventario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #007BFF;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Kardex de Inventario</h1>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Tipo Movimiento</th>
                <th>Cantidad</th>
                <th>Fecha</th>
                <th>Stock Acumulado</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['producto']); ?></td>
                    <td><?= ucfirst($fila['tipo']); ?></td>
                    <td><?= $fila['cantidad']; ?></td>
                    <td><?= $fila['fecha']; ?></td>
                    <td><?= $fila['stock_actual']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
