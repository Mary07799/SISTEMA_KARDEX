<?php
$conexion = new mysqli("localhost", "root", "Usu@rio_admin123", "inventario");
if ($conexion->connect_error) {
    die("Error: " . $conexion->connect_error);
}

$empresa_id = $_GET['id'] ?? 0;
if ($empresa_id == 0) { die("Empresa no válida"); }

// Datos de la empresa
$empresa = $conexion->query("SELECT nombre FROM empresas WHERE id=$empresa_id")->fetch_assoc();

// Agregar producto
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["agregar"])) {
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $cantidad = $_POST["cantidad"];
    $precio = $_POST["precio"];

    $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, cantidad, precio, empresa_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidi", $nombre, $descripcion, $cantidad, $precio, $empresa_id);
    $stmt->execute();
    $stmt->close();
}

// Eliminar producto
if (isset($_GET["eliminar"])) {
    $id = $_GET["eliminar"];
    $conexion->query("DELETE FROM productos WHERE id=$id");
}

// Listar productos de la empresa
$productos = $conexion->query("SELECT * FROM productos WHERE empresa_id=$empresa_id");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - <?php echo $empresa['nombre']; ?></title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #eee; }
        form { margin-top: 20px; background: #fff; padding: 15px; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { background: #007bff; color: white; padding: 10px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        a { color: red; text-decoration: none; }
    </style>
</head>
<body>
<h1>Inventario - <?php echo $empresa['nombre']; ?></h1>

<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre del producto" required>
    <input type="text" name="descripcion" placeholder="Descripción">
    <input type="number" name="cantidad" placeholder="Cantidad" required>
    <input type="number" step="0.01" name="precio" placeholder="Precio" required>
    <button type="submit" name="agregar">Agregar Producto</button>
</form>

<table>
    <tr>
        <th>ID</th><th>Nombre</th><th>Descripción</th><th>Cantidad</th><th>Precio</th><th>Acciones</th>
    </tr>
    <?php while ($fila = $productos->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $fila['id']; ?></td>
        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
        <td><?php echo htmlspecialchars($fila['descripcion']); ?></td>
        <td><?php echo $fila['cantidad']; ?></td>
        <td><?php echo $fila['precio']; ?></td>
        <td>
            <a href="?id=<?php echo $empresa_id; ?>&eliminar=<?php echo $fila['id']; ?>" onclick="return confirm('¿Eliminar producto?')">Eliminar</a> |
            <a href="movimientos.php?producto_id=<?php echo $fila['id']; ?>">Movimientos</a>
        </td>
    </tr>
    <?php } ?>
</table>
</body>
</html>
