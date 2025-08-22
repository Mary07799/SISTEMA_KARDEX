<?php
require_once 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_empresa = trim($_POST['nombre_empresa']);
    $descripcion = trim($_POST['descripcion']);

    if (!$nombre_empresa) {
        $error = "El nombre de la empresa es obligatorio.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO empresas (usuario_id, nombre_empresa, descripcion) VALUES (?, ?, ?)");
        if ($stmt->execute([$usuario_id, $nombre_empresa, $descripcion])) {
            header("Location: empresas.php");
            exit();
        } else {
            $error = "Error al agregar la empresa.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar Empresa</title>
    <style>
        body { background: #000; color: #fff; font-family: Arial, sans-serif; padding: 20px; }
        form {
            max-width: 400px; margin: 0 auto;
            background: #111; padding: 20px; border-radius: 8px;
            box-shadow: 0 0 20px #00e6e6;
        }
        input[type="text"], textarea {
            width: 100%; padding: 8px; margin: 10px 0;
            border-radius: 4px; border: none;
        }
        button {
            width: 100%; padding: 10px; background: #00e6e6;
            border: none; border-radius: 4px; color: #000;
            font-weight: bold; font-size: 16px;
            cursor: pointer; text-transform: uppercase;
        }
        button:hover { background: #00baba; }
        .error { color: #ff5555; margin-bottom: 10px; }
        a { display: block; margin-top: 15px; text-align: center; color: #00e6e6; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Agregar nueva empresa/práctica contable</h2>

    <?php if ($error): ?>
        <p class="error"><?=htmlspecialchars($error)?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label>Nombre de la empresa:<br>
            <input type="text" name="nombre_empresa" required />
        </label><br>
        <label>Descripción:<br>
            <textarea name="descripcion" rows="4"></textarea>
        </label><br>
        <button type="submit">Agregar</button>
    </form>
    <a href="empresas.php">Volver a mis prácticas</a>
</body>
</html>
