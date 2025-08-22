<?php
session_start();
$conexion = new mysqli("localhost", "root", "Usu@rio_admin123", "contabilidad_paccioli");
if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

// Verificar login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Procesar nuevo producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_producto'])) {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $empresa_id = intval($_POST['empresa_id']);

    $conexion->query("INSERT INTO productos (nombre, descripcion, precio, stock, empresa_id) 
                      VALUES ('$nombre','$descripcion',$precio,$stock,$empresa_id)");
}

// Registrar movimiento de entrada o salida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_movimiento'])) {
    $producto_id = intval($_POST['producto_id']);
    $tipo = $_POST['tipo'];
    $cantidad = intval($_POST['cantidad']);

    if ($tipo === "salida") {
        // Validar stock suficiente
        $res = $conexion->query("SELECT stock FROM productos WHERE id=$producto_id");
        $stock_actual = $res->fetch_assoc()['stock'];
        if ($cantidad > $stock_actual) {
            echo "<script>alert('No hay suficiente stock disponible');</script>";
        } else {
            $conexion->query("UPDATE productos SET stock = stock - $cantidad WHERE id=$producto_id");
            $conexion->query("INSERT INTO movimientos (producto_id, tipo, cantidad) 
                              VALUES ($producto_id, 'salida', $cantidad)");
        }
    } else {
        $conexion->query("UPDATE productos SET stock = stock + $cantidad WHERE id=$producto_id");
        $conexion->query("INSERT INTO movimientos (producto_id, tipo, cantidad) 
                          VALUES ($producto_id, 'entrada', $cantidad)");
    }
}

// Obtener empresas
$empresas = $conexion->query("SELECT * FROM empresas");

// Obtener productos y movimientos
$productos = $conexion->query("SELECT p.*, e.nombre as empresa FROM productos p 
                               JOIN empresas e ON p.empresa_id=e.id");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding:20px;}
        h1,h2 {color:#333;}
        table { width:100%; border-collapse:collapse; margin-top:20px; background:white;}
        th,td { border:1px solid #ccc; padding:8px; text-align:left;}
        th { background:#ddd;}
        input, select { padding:5px; margin:5px 0; width:100%;}
        .formulario { background:white; padding:15px; margin-bottom:20px; border:1px solid #ccc;}
        button { padding:10px; background:#28a745; color:white; border:none; cursor:pointer;}
        button:hover { background:#218838;}
        .salir { display:inline-block; margin-top:10px; padding:10px; background:#dc3545; color:white; text-decoration:none;}
        .salir:hover {background:#c82333;}
    </style>
</head>
<body>
<h1>Inventario (Kardex básico)</h1>
<a href="menu.php" class="salir">⬅ Volver al menú</a>

<div class="formulario">
    <h2>Agregar producto</h2>
    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre del producto" required>
        <textarea name="descripcion" placeholder="Descripción"></textarea>
        <input type="number" name="precio" step="0.01" placeholder="Precio" required>
        <input type="number" name="stock" placeholder="Stock inicial" required>
        <select name="empresa_id" required>
            <option value="">Seleccionar empresa</option>
            <?php while($e = $empresas->fetch_assoc()): ?>
                <option value="<?= $e['id']?>"><?= htmlspecialchars($e['nombre'])?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="agregar_producto">Agregar</button>
    </form>
</div>

<h2>Productos</h2>
<table>
    <tr><th>ID</th><th>Producto</th><th>Empresa</th><th>Precio</th><th>Stock</th><th>Registrar movimiento</th></tr>
    <?php while($p = $productos->fetch_assoc()): ?>
        <tr>
            <td><?= $p['id']?></td>
            <td><?= htmlspecialchars($p['nombre'])?></td>
            <td><?= htmlspecialchars($p['empresa'])?></td>
            <td>$<?= $p['precio']?></td>
            <td><?= $p['stock']?></td>
            <td>
                <form method="post" style="display:flex;gap:5px;">
                    <input type="hidden" name="producto_id" value="<?= $p['id']?>">
                    <select name="tipo">
                        <option value="entrada">Entrada</option>
                        <option value="salida">Salida</option>
                    </select>
                    <input type="number" name="cantidad" min="1" required>
                    <button type="submit" name="registrar_movimiento">OK</button>
                </form>
            </td>
        </tr>
    <?php endwhile;?>
</table>
</body>
</html>
